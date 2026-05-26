<?php

namespace Webkul\ErpConnector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Product\Repositories\ProductRepository;

class SyncProductFromErpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sku;

    /**
     * Create a new job instance.
     *
     * @param string $sku
     * @return void
     */
    public function __construct(string $sku)
    {
        $this->sku = $sku;
    }

    /**
     * Execute the job.
     *
     * @param \Webkul\Product\Repositories\ProductRepository $productRepository
     * @return void
     */
    public function handle(ProductRepository $productRepository)
    {
        Log::info("Starting ERP Sync for Product SKU: {$this->sku}");

        // Read ERP URL and token from admin UI settings (same source as ConnectionController)
        $erpConfig = app(\Webkul\ErpConnector\Helpers\Config::class);
        $erpUrl    = rtrim($erpConfig->getErpUrl(), '/');
        $erpToken  = $erpConfig->getErpToken();

        if (empty($erpUrl) || empty($erpToken)) {
            Log::error('SyncProductFromErpJob: ERP URL or Token not configured in admin settings.');
            return;
        }

        // Retrieve JWT from Keycloak
        $jwt = app(\Webkul\ErpConnector\Services\KeycloakTokenService::class)->getToken();
        \Log::info('SyncProductFromErpJob: Sending request to ERP', [
            'erpUrl'   => $erpUrl,
            'erpToken' => substr($erpToken, 0, 15) . '...',
            'jwt'      => substr($jwt, 0, 20) . '...',
        ]);

        // 1. Fetch full product data from the Spring Boot ERP with both headers
        $response = Http::withToken($jwt)
            ->withHeaders(['X-ERP-TOKEN' => $erpToken])
            ->get("{$erpUrl}/erp/products/sku/{$this->sku}");

        if ($response->failed()) {
            Log::error("Failed to fetch product {$this->sku} from ERP.", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return;
        }

        $erpProduct = $response->json();

        // 1.5 Ensure "ERP Products" Category and Homepage Customizations exist
        $categoryId = 2; // Default fallback
        $erpCategoryTranslation = DB::table('category_translations')->where('slug', 'erp-products')->first();
        
        if ($erpCategoryTranslation) {
            $categoryId = $erpCategoryTranslation->category_id;
            
            // Ensure url_path is set correctly if it was empty
            if (empty($erpCategoryTranslation->url_path)) {
                DB::table('category_translations')
                    ->where('id', $erpCategoryTranslation->id)
                    ->update(['url_path' => 'erp-products']);
                Log::info("Updated empty ERP Products Category url_path to 'erp-products'.");
            }
        } else {
            // Find root category
            $rootCategory = DB::table('categories')->whereNull('parent_id')->first() 
                ?? DB::table('categories')->where('id', 1)->first();
            $rootCategoryId = $rootCategory ? $rootCategory->id : 1;
            
            // Create Category
            $categoryId = DB::table('categories')->insertGetId([
                'parent_id'  => $rootCategoryId,
                'position'   => 2,
                'status'     => 1,
                '_lft'       => 84, 
                '_rgt'       => 85,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create Category Translation
            DB::table('category_translations')->insert([
                'category_id'      => $categoryId,
                'locale'           => 'en',
                'name'             => 'ERP Products',
                'slug'             => 'erp-products',
                'url_path'         => 'erp-products',
                'description'      => 'Products synchronized from our ERP backend.',
                'locale_id'        => 1,
            ]);
            
            Log::info("Auto-created ERP Products Category with ID {$categoryId}.");
        }

        // Ensure Homepage Theme Customization for ERP Products exists
        $customization = DB::table('theme_customizations')
            ->where('type', 'product_carousel')
            ->where('name', 'ERP Products')
            ->first();
            
        if (!$customization) {
            $customizationId = DB::table('theme_customizations')->insertGetId([
                'theme_code' => 'default',
                'type'       => 'product_carousel',
                'name'       => 'ERP Products',
                'sort_order' => 3,
                'status'     => 1,
                'channel_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('theme_customization_translations')->insert([
                'theme_customization_id' => $customizationId,
                'locale'                 => 'en',
                'options'                => json_encode([
                    'title'   => 'ERP Products',
                    'filters' => [
                        'category_id' => (string)$categoryId,
                        'sort'        => 'created_at-desc',
                        'limit'       => '10'
                    ]
                ])
            ]);
            
            Log::info("Auto-created Homepage Product Carousel for ERP Products.");
        }

        // 2. Map ERP data to Bagisto structure
        $mappedData = [
            'channel'              => 'default',
            'locale'               => 'en',
            'name'                 => $erpProduct['name'] ?? 'Unknown Product',
            'price'                => $erpProduct['price'] ?? 0.00,
            'weight'               => $erpProduct['weight'] ?? 1.0,
            'status'               => $erpProduct['status'] ?? 1,
            'visible_individually' => 1,
            'guest_checkout'       => 1,
            'featured'             => 1,
            'new'                  => 1,
            'short_description'    => $erpProduct['short_description'] ?? '',
            'description'          => $erpProduct['description'] ?? '',
            'url_key'              => strtolower(str_replace(' ', '-', $erpProduct['name'] ?? $this->sku)),
            'inventories'          => [
                1 => $erpProduct['quantity'] ?? 1, // Assign stock (Source ID 1)
            ],
            'channels'             => [
                1 // Assign to Channel ID 1
            ],
            'categories'           => [
                1, // Assign to Root category ID 1
                $categoryId  // Assign to Dynamic Category ID
            ],
        ];

        // 3. Check if the product already exists in Bagisto
        $existingProduct = $productRepository->findOneWhere(['sku' => $this->sku]);

        if ($existingProduct) {
            // Update existing product
            Log::info("Updating existing Bagisto product: {$this->sku}");
            $product = $productRepository->update($mappedData, $existingProduct->id);
            
            // Dispatch update event to trigger Flat Indexer and other indices
            Event::dispatch('catalog.product.update.after', $product);
        } else {
            // Create new product
            Log::info("Creating new Bagisto product: {$this->sku}");
            
            $baseData = [
                'sku'                 => $this->sku,
                'type'                => 'simple', 
                'attribute_family_id' => 1,        // Default attribute family
            ];

            $newProduct = $productRepository->create($baseData);
            
            // Dispatch create event
            Event::dispatch('catalog.product.create.after', $newProduct);
            
            // Immediately update it with the mapped EAV data
            $updatedProduct = $productRepository->update($mappedData, $newProduct->id);
            
            // Dispatch update event to rebuild Flat Index and Price/Inventory/Elasticsearch indices
            Event::dispatch('catalog.product.update.after', $updatedProduct);
        }

        Log::info("Successfully synced product {$this->sku} from ERP.");
    }
}

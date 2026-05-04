<?php

namespace Webkul\ErpConnector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        $erpUrl = config('erp.url');
        $erpToken = config('erp.api_token');

        // 1. Fetch full product data from the Spring Boot ERP
        $response = Http::withToken($erpToken)
            ->get("{$erpUrl}/erp/products/sku/{$this->sku}");

        if ($response->failed()) {
            Log::error("Failed to fetch product {$this->sku} from ERP.", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return;
        }

        $erpProduct = $response->json();

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
        ];

        // 3. Check if the product already exists in Bagisto
        $existingProduct = $productRepository->findOneWhere(['sku' => $this->sku]);

        if ($existingProduct) {
            // Update existing product
            Log::info("Updating existing Bagisto product: {$this->sku}");
            $productRepository->update($mappedData, $existingProduct->id);
        } else {
            // Create new product
            Log::info("Creating new Bagisto product: {$this->sku}");
            
            $baseData = [
                'sku'                 => $this->sku,
                'type'                => 'simple', 
                'attribute_family_id' => 1,        // Default attribute family
            ];

            $newProduct = $productRepository->create($baseData);
            
            // Immediately update it with the mapped EAV data
            $productRepository->update($mappedData, $newProduct->id);
        }

        Log::info("Successfully synced product {$this->sku} from ERP.");
    }
}

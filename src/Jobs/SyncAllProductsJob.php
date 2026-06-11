<?php

namespace Webkul\ErpConnector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\ErpConnector\Helpers\Config as ErpConfig;
use Webkul\ErpConnector\Services\KeycloakTokenService;

class SyncAllProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ProductRepository $productRepository, ErpConfig $erpConfig)
    {
        Log::info("Starting server-side SyncAllProductsJob...");

        $url   = rtrim($erpConfig->getErpUrl(), '/');
        $token = $erpConfig->getErpToken();

        if (empty($url) || empty($token)) {
            Log::error('SyncAllProductsJob: ERP URL or Token not configured in admin settings.');
            $this->saveSyncStatus(false, 0, 0, 0, 0, 'Configuration missing. Set URL and Token in settings.');
            return;
        }

        try {
            $jwt = app(KeycloakTokenService::class)->getToken();

            // Fetch the full product list from ERP
            $response = Http::withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->timeout(30)
                ->get("{$url}/erp/products");

            if ($response->failed()) {
                $errorMsg = 'ERP returned status ' . $response->status();
                Log::error("SyncAllProductsJob: " . $errorMsg);
                $this->saveSyncStatus(false, 0, 0, 0, 0, $errorMsg);
                return;
            }

            $erpProducts = $response->json() ?? [];
            $toSync    = [];
            $toDisable = [];

            foreach ($erpProducts as $product) {
                $sku     = $product['sku'] ?? null;
                $publish = $product['publish'] ?? true;

                if (! $sku) continue;

                if ($publish) {
                    $toSync[] = $sku;
                } else {
                    $toDisable[] = $sku;
                }
            }

            $total = count($toSync);
            $created = 0;
            $updated = 0;
            $failed = 0;
            $disabledCount = 0;
            $errors = [];

            // Sync each SKU sequentially in the queue process
            foreach ($toSync as $sku) {
                try {
                    // Check if it already exists to count created vs updated
                    $existing = $productRepository->findOneWhere(['sku' => $sku]);
                    
                    // Dispatch synchronously to run sequentially and capture counts
                    SyncProductFromErpJob::dispatchSync($sku);
                    
                    if ($existing) {
                        $updated++;
                    } else {
                        $created++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = $sku . ': ' . $e->getMessage();
                    Log::error("SyncAllProductsJob SKU [{$sku}] Sync failed: " . $e->getMessage());
                }
            }

            // Finalize: Disable SKUs not in synced list
            $categoryId = $this->getErpCategoryId();
            if ($categoryId) {
                $erpCategoryProductIds = DB::table('product_categories')
                    ->where('category_id', $categoryId)
                    ->pluck('product_id');

                $erpProductsInDb = DB::table('products')
                    ->whereIn('id', $erpCategoryProductIds)
                    ->pluck('sku');

                foreach ($erpProductsInDb as $sku) {
                    if (! in_array($sku, $toSync)) {
                        try {
                            $this->disableProductBySku($productRepository, $sku);
                            $disabledCount++;
                        } catch (\Exception $e) {
                            $errors[] = $sku . ': ' . $e->getMessage();
                            Log::error("SyncAllProductsJob SKU [{$sku}] disable failed: " . $e->getMessage());
                        }
                    }
                }
            }

            // Also disable explicitly unpublished products
            foreach ($toDisable as $sku) {
                try {
                    $this->disableProductBySku($productRepository, $sku);
                    $disabledCount++;
                } catch (\Exception $e) {
                    $errors[] = $sku . ': ' . $e->getMessage();
                    Log::error("SyncAllProductsJob SKU [{$sku}] disable failed: " . $e->getMessage());
                }
            }

            Log::info("SyncAllProductsJob finished successfully.", [
                'total' => $total,
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
                'disabled' => $disabledCount,
            ]);

            $this->saveSyncStatus(true, $total, $created, $updated, $disabledCount, '', $failed, $errors);

        } catch (\Exception $e) {
            Log::error("SyncAllProductsJob exception: " . $e->getMessage());
            $this->saveSyncStatus(false, 0, 0, 0, 0, $e->getMessage());
        }
    }

    /**
     * Set status = 0 on a Bagisto product by SKU.
     */
    private function disableProductBySku(ProductRepository $productRepository, string $sku): void
    {
        $product = $productRepository->findOneWhere(['sku' => $sku]);

        if (! $product) {
            return;
        }

        $productRepository->update([
            'channel' => 'default',
            'locale'  => 'en',
            'status'  => 0,
        ], $product->id);

        Event::dispatch('catalog.product.update.after', $product);
    }

    /**
     * Get the ERP Category ID.
     */
    private function getErpCategoryId(): ?int
    {
        $translation = DB::table('category_translations')->where('slug', 'erp-products')->first();
        return $translation ? (int) $translation->category_id : null;
    }

    /**
     * Save the sync status in the DB using core_config
     */
    private function saveSyncStatus($success, $total, $created, $updated, $disabled, $message = '', $failed = 0, $errors = [])
    {
        $data = [
            'status' => $success ? 'success' : 'error',
            'timestamp' => now()->toIso8601String(),
            'total' => $total,
            'created' => $created,
            'updated' => $updated,
            'disabled' => $disabled,
            'failed' => $failed,
            'message' => $message,
            'errors' => array_slice($errors, 0, 10), // Limit error logs in DB
        ];

        // Using direct updateOrInsert to save configuration reliably
        DB::table('core_config')->updateOrInsert(
            ['code' => 'erp.settings.general.last_sync_info'],
            ['value' => json_encode($data)]
        );
    }
}

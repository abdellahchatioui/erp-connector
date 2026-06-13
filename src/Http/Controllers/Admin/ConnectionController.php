<?php

namespace Webkul\ErpConnector\Http\Controllers\Admin;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\ErpConnector\Helpers\Config;
use Webkul\ErpConnector\Services\KeycloakTokenService;
use Webkul\Product\Repositories\ProductRepository;

class ConnectionController extends Controller
{
    /**
     * @var Config
     */
    protected $erpConfig;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(Config $erpConfig, ProductRepository $productRepository)
    {
        $this->erpConfig = $erpConfig;
        $this->productRepository = $productRepository;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONNECTION TEST
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Test the connection to the ERP
     */
    public function test()
    {
        $url = request()->input('backend_url') ?: $this->erpConfig->getErpUrl();
        $token = request()->input('erp_token') ?: $this->erpConfig->getErpToken();

        if (! $url || ! $token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Configuration missing. Please set URL and Token in settings.',
            ]);
        }

        try {
            $token = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            // Token is raw/unencrypted (newly typed by user), use as-is
        }

        $keycloakOverrides = [
            'token_url' => request()->input('keycloak_token_url') ?: $this->erpConfig->getKeycloakTokenUrl(),
            'client_id' => request()->input('keycloak_client_id') ?: $this->erpConfig->getKeycloakClientId(),
            'username' => request()->input('keycloak_username') ?: $this->erpConfig->getKeycloakUsername(),
            'password' => request()->input('keycloak_password') ?: $this->erpConfig->getKeycloakPassword(),
        ];

        if (! empty($keycloakOverrides['password'])) {
            try {
                $keycloakOverrides['password'] = Crypt::decryptString($keycloakOverrides['password']);
            } catch (DecryptException $e) {
                // raw password
            }
        }

        try {
            $url = rtrim($url, '/');
            $jwt = app(KeycloakTokenService::class)->getToken($keycloakOverrides);

            $response = Http::withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->timeout(5)
                ->get("{$url}/api/erp/verify");

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('erp::app.admin.connection.success'),
                ]);
            }

            $errorMsg = $response->body() ?: $response->reason();

            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $errorMsg]),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $e->getMessage()]),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYNC PHASE 1 — INIT
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch the full product list from ERP and return two SKU lists:
     *   - toSync   : products with publish=true  → import/update in Bagisto
     *   - toDisable: products with publish=false → disable in Bagisto
     *
     * Also returns SKUs that exist in Bagisto (ERP category) but are completely
     * absent from the ERP response — those will be disabled in finalise.
     */
    public function syncInit()
    {
        try {
            [$url, $token, $jwt] = $this->resolveErpCredentials();

            $response = Http::withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->timeout(15)
                ->get("{$url}/erp/products");

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch products from ERP: '.$response->status(),
                ], 500);
            }

            $erpProducts = $response->json() ?? [];

            $toSync = [];
            $toDisable = [];

            foreach ($erpProducts as $product) {
                $sku = $product['sku'] ?? null;
                $publish = $product['publish'] ?? true; // default true for backward compat

                if (! $sku) {
                    continue;
                }

                if ($publish) {
                    $toSync[] = $sku;
                } else {
                    $toDisable[] = $sku;
                }
            }

            return response()->json([
                'success' => true,
                'to_sync' => $toSync,
                'to_disable' => $toDisable,
                'total' => count($toSync),
            ]);

        } catch (\Exception $e) {
            Log::error('ERP syncInit error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYNC PHASE 2 — SINGLE SKU
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sync one single SKU from ERP into Bagisto.
     * Returns created | updated | failed so the UI can track counters.
     */
    public function syncSku()
    {
        $sku = request()->input('sku');

        if (! $sku) {
            return response()->json(['success' => false, 'status' => 'failed', 'message' => 'No SKU provided.']);
        }

        try {
            [$url, $token, $jwt] = $this->resolveErpCredentials();

            $response = Http::withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->timeout(10)
                ->get("{$url}/erp/products/sku/{$sku}");

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'status' => 'failed',
                    'sku' => $sku,
                    'message' => 'ERP returned '.$response->status(),
                ]);
            }

            $erpProduct = $response->json();
            $categoryId = $this->ensureErpCategory();
            $isNew = false;

            $existing = $this->productRepository->findOneWhere(['sku' => $sku]);

            $mappedData = [
                'channel' => 'default',
                'locale' => 'en',
                'name' => $erpProduct['name'] ?? 'Unknown Product',
                'price' => $erpProduct['price'] ?? 0.00,
                'weight' => $erpProduct['weight'] ?? 1.0,
                'status' => 1, // publish=true → enabled
                'visible_individually' => 1,
                'guest_checkout' => 1,
                'featured' => 1,
                'new' => 1,
                'short_description' => $erpProduct['description'] ?? '',
                'description' => $erpProduct['description'] ?? '',
                'url_key' => strtolower(str_replace(' ', '-', $erpProduct['name'] ?? $sku)),
                'inventories' => [1 => $erpProduct['quantity'] ?? 1],
                'channels' => [1],
                'categories' => [1, $categoryId],
            ];

            if ($existing) {
                $product = $this->productRepository->update($mappedData, $existing->id);
                Event::dispatch('catalog.product.update.after', $product);
                $status = 'updated';
            } else {
                $isNew = true;
                $base = ['sku' => $sku, 'type' => 'simple', 'attribute_family_id' => 1];
                $product = $this->productRepository->create($base);
                Event::dispatch('catalog.product.create.after', $product);
                $updated = $this->productRepository->update($mappedData, $product->id);
                Event::dispatch('catalog.product.update.after', $updated);
                $status = 'created';
            }

            Log::info("ERP Sync [{$status}]: {$sku}");

            return response()->json([
                'success' => true,
                'status' => $status,
                'sku' => $sku,
            ]);

        } catch (\Exception $e) {
            Log::error("ERP syncSku error [{$sku}]: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'status' => 'failed',
                'sku' => $sku,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYNC PHASE 3 — FINALIZE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Disable all products in the ERP category that were NOT in the synced list
     * (removed from ERP entirely), plus all products flagged with publish=false.
     * Never deletes — only sets status = 0.
     */
    public function syncFinalize()
    {
        $processedSkus = request()->input('processed_skus', []);
        $toDisable = request()->input('to_disable', []);

        $disabledCount = 0;
        $errors = [];

        try {
            $categoryId = $this->getErpCategoryId();

            // Find all Bagisto products in the ERP category
            if ($categoryId) {
                $erpCategoryProductIds = DB::table('product_categories')
                    ->where('category_id', $categoryId)
                    ->pluck('product_id');

                $erpProducts = DB::table('products')
                    ->whereIn('id', $erpCategoryProductIds)
                    ->pluck('sku');

                // Disable SKUs not in processedSkus list (removed from ERP)
                foreach ($erpProducts as $sku) {
                    if (! in_array($sku, $processedSkus)) {
                        try {
                            $this->disableProductBySku($sku);
                            $disabledCount++;
                        } catch (\Exception $e) {
                            $errors[] = ['sku' => $sku, 'error' => $e->getMessage()];
                        }
                    }
                }
            }

            // Also disable products explicitly flagged publish=false
            foreach ($toDisable as $sku) {
                try {
                    $this->disableProductBySku($sku);
                    $disabledCount++;
                } catch (\Exception $e) {
                    $errors[] = ['sku' => $sku, 'error' => $e->getMessage()];
                }
            }

            Log::info("ERP Sync Finalized. Disabled: {$disabledCount}. Errors: ".count($errors));

            return response()->json([
                'success' => true,
                'disabled_count' => $disabledCount,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('ERP syncFinalize error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return the latest automatic sync status for the admin UI.
     */
    public function syncStatus()
    {
        $lastSyncJson = core()->getConfigData('erp.settings.general.last_sync_info');
        $lastSyncInfo = $lastSyncJson ? json_decode($lastSyncJson, true) : [];

        if (! is_array($lastSyncInfo)) {
            $lastSyncInfo = [];
        }

        return response()->json(array_merge([
            'auto_sync_enabled' => $this->erpConfig->isAutoSyncEnabled(),
            'interval' => $this->erpConfig->getAutoSyncInterval(),
            'interval_label' => $this->erpConfig->getAutoSyncIntervalLabel(),
            'status' => 'idle',
            'source' => 'auto',
            'timestamp' => null,
            'started_at' => null,
            'finished_at' => null,
            'last_sync_at' => null,
            'next_sync_at' => null,
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'disabled' => 0,
            'failed' => 0,
            'message' => '',
            'errors' => [],
        ], $lastSyncInfo, [
            'auto_sync_enabled' => $this->erpConfig->isAutoSyncEnabled(),
            'interval' => $this->erpConfig->getAutoSyncInterval(),
            'interval_label' => $this->erpConfig->getAutoSyncIntervalLabel(),
            'next_sync_at' => $lastSyncInfo['next_sync_at'] ?? null,
        ]));
    }

    /**
     * Save only the automatic sync settings.
     */
    public function saveAutoSyncSettings(Request $request)
    {
        $data = $request->validate([
            'auto_sync_enabled' => ['nullable', 'boolean'],
            'auto_sync_interval' => ['required', 'in:test-1,test-2,1,3,6,12,24'],
        ]);

        $channelCode = core()->getRequestedChannelCode();
        $now = now();
        $configRepository = app(\Webkul\Core\Repositories\CoreConfigRepository::class);

        $configRepository->updateOrCreate(
            [
                'code'         => 'erp.settings.general.auto_sync_enabled',
                'channel_code' => $channelCode,
                'locale_code'  => null,
            ],
            ['value' => ! empty($data['auto_sync_enabled']) ? '1' : '0']
        );

        $configRepository->updateOrCreate(
            [
                'code'         => 'erp.settings.general.auto_sync_interval',
                'channel_code' => $channelCode,
                'locale_code'  => null,
            ],
            ['value' => $data['auto_sync_interval']]
        );

        // Instantly update last_sync_info in core_config so that UI countdown updates immediately
        $lastSyncJson = core()->getConfigData('erp.settings.general.last_sync_info');
        $lastSyncInfo = $lastSyncJson ? json_decode($lastSyncJson, true) : [];
        if (! is_array($lastSyncInfo)) {
            $lastSyncInfo = [];
        }

        $lastSyncInfo['auto_sync_enabled'] = ! empty($data['auto_sync_enabled']);
        $lastSyncInfo['interval'] = $data['auto_sync_interval'];
        
        // Dynamically compute next expected sync time
        if ($lastSyncInfo['auto_sync_enabled']) {
            $lastSyncInfo['next_sync_at'] = $this->erpConfig->getNextSyncAt($now);
        } else {
            $lastSyncInfo['next_sync_at'] = null;
        }

        $configRepository->updateOrCreate(
            [
                'code'         => 'erp.settings.general.last_sync_info',
                'channel_code' => null,
                'locale_code'  => null,
            ],
            ['value' => json_encode($lastSyncInfo)]
        );

        return response()->json([
            'success' => true,
            'message' => 'Auto sync settings saved.',
            'auto_sync_enabled' => ! empty($data['auto_sync_enabled']),
            'interval' => $this->erpConfig->getAutoSyncInterval(),
            'interval_label' => $this->erpConfig->getAutoSyncIntervalLabel(),
        ]);
    }

    /**
     * Build a [url, token, jwt] tuple from core_config.
     *
     * @return array [string $url, string $token, string $jwt]
     */
    private function resolveErpCredentials(): array
    {
        $url = rtrim($this->erpConfig->getErpUrl(), '/');
        $token = $this->erpConfig->getErpToken();

        if (! $url || ! $token) {
            throw new \RuntimeException('ERP URL or Token not configured in admin settings.');
        }

        $jwt = app(KeycloakTokenService::class)->getToken();

        return [$url, $token, $jwt];
    }

    /**
     * Ensure the "ERP Products" category exists and return its ID.
     */
    private function ensureErpCategory(): int
    {
        $translation = DB::table('category_translations')->where('slug', 'erp-products')->first();

        if ($translation) {
            if (empty($translation->url_path)) {
                DB::table('category_translations')
                    ->where('id', $translation->id)
                    ->update(['url_path' => 'erp-products']);
            }

            return $translation->category_id;
        }

        $root = DB::table('categories')->whereNull('parent_id')->first()
                   ?? DB::table('categories')->where('id', 1)->first();
        $rootId = $root ? $root->id : 1;

        $categoryId = DB::table('categories')->insertGetId([
            'parent_id' => $rootId,
            'position' => 2,
            'status' => 1,
            '_lft' => 84,
            '_rgt' => 85,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('category_translations')->insert([
            'category_id' => $categoryId,
            'locale' => 'en',
            'name' => 'ERP Products',
            'slug' => 'erp-products',
            'url_path' => 'erp-products',
            'description' => 'Products synchronized from our ERP backend.',
            'locale_id' => 1,
        ]);

        Log::info("Auto-created ERP Products Category with ID {$categoryId}.");

        return $categoryId;
    }

    /**
     * Return the ERP Products category ID if it exists, or null.
     */
    private function getErpCategoryId(): ?int
    {
        $translation = DB::table('category_translations')->where('slug', 'erp-products')->first();

        return $translation ? (int) $translation->category_id : null;
    }

    /**
     * Set status = 0 on a Bagisto product by SKU (safe disable, no delete).
     */
    private function disableProductBySku(string $sku): void
    {
        $product = $this->productRepository->findOneWhere(['sku' => $sku]);

        if (! $product) {
            return; // Nothing to disable
        }

        $this->productRepository->update([
            'channel' => 'default',
            'locale' => 'en',
            'status' => 0,
        ], $product->id);

        Event::dispatch('catalog.product.update.after', $product);

        Log::info("ERP Sync: Disabled product {$sku} in Bagisto.");
    }
}

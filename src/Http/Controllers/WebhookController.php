<?php

namespace Webkul\ErpConnector\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

use Webkul\ErpConnector\Jobs\SyncProductFromErpJob;

class WebhookController extends Controller
{
    /**
     * Handle incoming product sync webhooks from ERP.
     */
    public function handleProductSync(Request $request)
    {
        Log::info('Received product sync webhook from ERP', $request->all());

        // Validate that a SKU was provided
        $sku = $request->input('sku');

        if (empty($sku)) {
            return response()->json([
                'success' => false,
                'message' => 'SKU is required.'
            ], 400);
        }

        // Dispatch the Pull Model Job to fetch data asynchronously
        SyncProductFromErpJob::dispatch($sku);
        
        return response()->json([
            'success' => true,
            'message' => "Product sync event received for SKU: {$sku}. Background job dispatched."
        ]);
    }

    /**
     * Handle incoming order sync webhooks from ERP.
     */
    public function handleOrderSync(Request $request)
    {
        Log::info('Received order sync webhook from ERP', $request->all());

        // @todo: Process order logic
        
        return response()->json([
            'success' => true,
            'message' => 'Order sync event received.'
        ]);
    }
}

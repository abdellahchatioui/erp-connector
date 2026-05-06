<?php

namespace Webkul\ErpConnector\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Webkul\ErpConnector\Helpers\Config;

class ConnectionController extends Controller
{
    /**
     * @var Config
     */
    protected $erpConfig;

    public function __construct(Config $erpConfig)
    {
        $this->erpConfig = $erpConfig;
    }

    /**
     * Test the connection to the ERP
     */
    public function test()
    {
        $url = $this->erpConfig->getErpUrl();
        $token = $this->erpConfig->getErpToken();

        if (!$url || !$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Configuration missing. Please set URL and Token in settings.'
            ]);
        }

        try {
            // Call a health-check or simple endpoint on the ERP
            $response = Http::timeout(5)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->get("{$url}/api/erp/verify");

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => trans('erp::app.admin.connection.success')
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $response->reason()])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $e->getMessage()])
            ]);
        }
    }
}

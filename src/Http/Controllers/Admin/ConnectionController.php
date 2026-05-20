<?php

namespace Webkul\ErpConnector\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
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
        $url = request()->input('backend_url') ?: $this->erpConfig->getErpUrl();
        $token = request()->input('erp_token') ?: $this->erpConfig->getErpToken();

        if (!$url || !$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Configuration missing. Please set URL and Token in settings.'
            ]);
        }

        // Decrypt the token if it is encrypted (e.g. read from database value populated in input)
        try {
            $token = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            // Token is raw/unencrypted (newly typed by user), use as-is
        }

        try {
            $url = rtrim($url, '/');

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

            $errorMsg = $response->body() ?: $response->reason();
            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $errorMsg])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $e->getMessage()])
            ]);
        }
    }
}

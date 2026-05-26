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

        // Decrypt the ERP token if it is encrypted (e.g. read from database value populated in input)
        try {
            $token = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            // Token is raw/unencrypted (newly typed by user), use as-is
        }

        // Gather Keycloak overrides if provided in the test request
        $keycloakOverrides = [
            'token_url' => request()->input('keycloak_token_url') ?: $this->erpConfig->getKeycloakTokenUrl(),
            'client_id' => request()->input('keycloak_client_id') ?: $this->erpConfig->getKeycloakClientId(),
            'username'  => request()->input('keycloak_username') ?: $this->erpConfig->getKeycloakUsername(),
            'password'  => request()->input('keycloak_password') ?: $this->erpConfig->getKeycloakPassword(),
        ];

        // Decrypt the Keycloak password if it is encrypted
        if (!empty($keycloakOverrides['password'])) {
            try {
                $keycloakOverrides['password'] = Crypt::decryptString($keycloakOverrides['password']);
            } catch (DecryptException $e) {
                // Password is raw/unencrypted (newly typed by user), use as-is
            }
        }

        try {
            $url = rtrim($url, '/');

            // 1. Get JWT access token from Keycloak
            $jwt = app(\Webkul\ErpConnector\Services\KeycloakTokenService::class)->getToken($keycloakOverrides);

            \Log::info('ERP Connection Test', [
                'url'       => "{$url}/api/erp/verify",
                'jwt'       => substr($jwt, 0, 30) . '...',
                'erpToken'  => substr($token, 0, 10) . '...',
            ]);

            // 2. Call ERP verify endpoint with BOTH headers
            $response = Http::withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $token])
                ->timeout(5)
                ->get("{$url}/api/erp/verify");

            if ($response->successful()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => trans('erp::app.admin.connection.success')
                ]);
            }

            $errorMsg = $response->body() ?: $response->reason();
            return response()->json([
                'status'  => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $errorMsg])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => trans('erp::app.admin.connection.failed', ['message' => $e->getMessage()])
            ]);
        }
    }
}

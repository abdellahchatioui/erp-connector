<?php
namespace Webkul\ErpConnector\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class KeycloakTokenService
{
    protected $config;
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Retrieve a JWT from Keycloak using password flow.
     * Cached for 4 minutes (slightly less than token expiry).
     * @param array $overrides Optional credentials to test before saving
     */
    public function getToken(array $overrides = []): string
    {
        // Don't cache if we're testing unsaved credentials
        if (!empty($overrides)) {
            return $this->fetchToken($overrides);
        }

        return Cache::remember('keycloak_jwt', now()->addMinutes(4), function () {
            $erpConfig = app(\Webkul\ErpConnector\Helpers\Config::class);
            return $this->fetchToken([
                'token_url' => $erpConfig->getKeycloakTokenUrl(),
                'client_id' => $erpConfig->getKeycloakClientId(),
                'username'  => $erpConfig->getKeycloakUsername(),
                'password'  => $erpConfig->getKeycloakPassword(),
            ]);
        });
    }

    private function fetchToken(array $config): string
    {
        $tokenUrl = $config['token_url'] ?? null;
        $clientId = $config['client_id'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;

        if (!$tokenUrl || !$clientId || !$username || !$password) {
            throw new \Exception("Keycloak configuration is missing in the ERP Connector settings.");
        }

        $response = $this->client->post($tokenUrl, [
            'form_params' => [
                'grant_type'    => 'password',
                'client_id'     => $clientId,
                'username'      => $username,
                'password'      => $password,
            ],
                'timeout' => 5,
            ]);
            $body = json_decode((string) $response->getBody(), true);
            return $body['access_token'];
        }
}


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
        $this->config = config('keycloak');
        $this->client = new Client();
    }

    /**
     * Retrieve a JWT from Keycloak using client‑credentials flow.
     * Cached for 55 minutes (slightly less than token expiry).
     */
    public function getToken(): string
    {
        return Cache::remember('keycloak_jwt', now()->addMinutes(4), function () {
            $response = $this->client->post($this->config['token_url'], [
                'form_params' => [
                    'grant_type'    => 'password',
                    'client_id'     => $this->config['client_id'],
                    'username'      => $this->config['username'],
                    'password'      => $this->config['password'],
                ],
                'timeout' => 5,
            ]);
            $body = json_decode((string) $response->getBody(), true);
            return $body['access_token'];
        });
    }
}

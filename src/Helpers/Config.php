<?php

namespace Webkul\ErpConnector\Helpers;

use Illuminate\Support\Facades\Crypt;

class Config
{
    /**
     * Get ERP Backend URL
     *
     * @return string|null
     */
    public function getErpUrl()
    {
        return core()->getConfigData('erp.settings.general.backend_url');
    }

    /**
     * Get ERP Token (Decrypted)
     *
     * @return string|null
     */
    public function getErpToken()
    {
        $token = core()->getConfigData('erp.settings.general.erp_token');

        if (! $token) {
            return null;
        }

        try {
            return Crypt::decryptString($token);
        } catch (\Exception $e) {
            // Fallback for plain text tokens if not yet encrypted
            return $token;
        }
    }

    /**
     * Set ERP Token (Encrypted)
     *
     * @param string $token
     * @return void
     */
    public function setErpToken($token)
    {
        $encryptedToken = Crypt::encryptString($token);
        
        // This would be used for programmatic updates
        // For UI updates, we use an event listener
    }

    /**
     * Get Keycloak Token URL
     *
     * @return string|null
     */
    public function getKeycloakTokenUrl()
    {
        return core()->getConfigData('erp.settings.general.keycloak_token_url');
    }

    /**
     * Get Keycloak Client ID
     *
     * @return string|null
     */
    public function getKeycloakClientId()
    {
        return core()->getConfigData('erp.settings.general.keycloak_client_id');
    }

    /**
     * Get Keycloak Username
     *
     * @return string|null
     */
    public function getKeycloakUsername()
    {
        return core()->getConfigData('erp.settings.general.keycloak_username');
    }

    /**
     * Get Keycloak Password (Decrypted)
     *
     * @return string|null
     */
    public function getKeycloakPassword()
    {
        $password = core()->getConfigData('erp.settings.general.keycloak_password');

        if (! $password) {
            return null;
        }

        try {
            return Crypt::decryptString($password);
        } catch (\Exception $e) {
            // Fallback for plain text password if not yet encrypted
            return $password;
        }
    }
}

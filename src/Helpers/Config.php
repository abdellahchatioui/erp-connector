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
}

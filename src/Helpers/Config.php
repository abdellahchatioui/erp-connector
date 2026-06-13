<?php

namespace Webkul\ErpConnector\Helpers;

use Carbon\Carbon;
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
     * @param  string  $token
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

    /**
     * Check if automatic product sync is enabled.
     */
    public function isAutoSyncEnabled(): bool
    {
        return filter_var(
            core()->getConfigData('erp.settings.general.auto_sync_enabled'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Get the selected automatic sync interval.
     */
    public function getAutoSyncInterval(): string
    {
        return (string) (core()->getConfigData('erp.settings.general.auto_sync_interval') ?: '6');
    }

    /**
     * Get a readable label for the selected automatic sync interval.
     */
    public function getAutoSyncIntervalLabel(): string
    {
        return match ($this->getAutoSyncInterval()) {
            'test-1' => '1 Minute (Testing)',
            'test-2' => '2 Minutes (Testing)',
            '1' => 'Every Hour',
            '3' => 'Every 3 Hours',
            '6' => 'Every 6 Hours',
            '12' => 'Every 12 Hours',
            '24' => 'Daily (24 Hours)',
            default => 'Every 6 Hours',
        };
    }

    /**
     * Calculate the next expected automatic sync time.
     */
    public function getNextSyncAt($from = null): string
    {
        $date = $from ? Carbon::parse($from) : now();

        return match ($this->getAutoSyncInterval()) {
            'test-1' => $date->copy()->addMinute()->toIso8601String(),
            'test-2' => $date->copy()->addMinutes(2)->toIso8601String(),
            '1' => $date->copy()->addHour()->toIso8601String(),
            '3' => $date->copy()->addHours(3)->toIso8601String(),
            '6' => $date->copy()->addHours(6)->toIso8601String(),
            '12' => $date->copy()->addHours(12)->toIso8601String(),
            '24' => $date->copy()->addDay()->toIso8601String(),
            default => $date->copy()->addHours(6)->toIso8601String(),
        };
    }
}

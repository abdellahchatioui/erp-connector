<?php

namespace Webkul\ErpConnector\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ErpConnectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'erp');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'erp');


        $this->app['router']->aliasMiddleware('erp.verify', \Webkul\ErpConnector\Http\Middleware\VerifyErpToken::class);

        // Professional Hook: Encrypt the token and keycloak password in the request before they're saved to the database
        if ($this->app->runningInConsole() === false && request()->isMethod('post') && request()->has('erp.settings.general')) {
            $general = request()->input('erp.settings.general');
            $updated = false;

            if (isset($general['erp_token']) && !empty($general['erp_token'])) {
                $general['erp_token'] = \Illuminate\Support\Facades\Crypt::encryptString($general['erp_token']);
                $updated = true;
            }

            if (isset($general['keycloak_password']) && !empty($general['keycloak_password'])) {
                $general['keycloak_password'] = \Illuminate\Support\Facades\Crypt::encryptString($general['keycloak_password']);
                $updated = true;
            }

            if ($updated) {
                request()->merge([
                    'erp' => array_merge(request()->input('erp', []), [
                        'settings' => array_merge(request()->input('erp.settings', []), [
                            'general' => $general
                        ])
                    ])
                ]);
            }
        }

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    

        // Bind KeycloakTokenService as singleton
        $this->app->singleton(
            \Webkul\ErpConnector\Services\KeycloakTokenService::class,
            function ($app) {
                return new \Webkul\ErpConnector\Services\KeycloakTokenService();
            }
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/system.php', 'core'
        );

        $this->app->register(EventServiceProvider::class);
    }
}

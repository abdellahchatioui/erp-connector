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

        $this->publishes([
            __DIR__ . '/../Config/erp.php' => config_path('erp.php'),
        ]);

        $this->app['router']->aliasMiddleware('erp.verify', \Webkul\ErpConnector\Http\Middleware\VerifyErpToken::class);

        // Professional Hook: Encrypt the token in the request before it's saved to the database
        if ($this->app->runningInConsole() === false && request()->isMethod('post') && request()->has('erp.settings.general.erp_token')) {
            $token = request()->input('erp.settings.general.erp_token');
            
            if ($token) {
                request()->merge([
                    'erp' => array_merge(request()->input('erp'), [
                        'settings' => array_merge(request()->input('erp.settings'), [
                            'general' => array_merge(request()->input('erp.settings.general'), [
                                'erp_token' => \Illuminate\Support\Facades\Crypt::encryptString($token)
                            ])
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
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/erp.php', 'erp'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/system.php', 'core'
        );

        $this->app->register(EventServiceProvider::class);
    }
}

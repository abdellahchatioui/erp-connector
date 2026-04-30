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

        $this->publishes([
            __DIR__ . '/../Config/erp.php' => config_path('erp.php'),
        ]);
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

        $this->app->register(EventServiceProvider::class);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use Webkul\ErpConnector\Http\Controllers\Admin\ConnectionController;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {
        Route::prefix('erp-connector')->group(function () {
            Route::get('connection-test', [ConnectionController::class, 'index'])->name('admin.erp.connection.test');
            Route::post('connection-test', [ConnectionController::class, 'test'])->name('admin.erp.connection.run');

            // Chunked product sync routes
            Route::post('sync-products/init',     [ConnectionController::class, 'syncInit'])->name('admin.erp.sync.init');
            Route::post('sync-products/sku',      [ConnectionController::class, 'syncSku'])->name('admin.erp.sync.sku');
            Route::post('sync-products/finalize', [ConnectionController::class, 'syncFinalize'])->name('admin.erp.sync.finalize');
        });
    });
});

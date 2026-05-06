<?php

use Illuminate\Support\Facades\Route;
use Webkul\ErpConnector\Http\Controllers\Admin\ConnectionController;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {
        Route::prefix('erp-connector')->group(function () {
            Route::get('connection-test', [ConnectionController::class, 'index'])->name('admin.erp.connection.test');
            Route::post('connection-test', [ConnectionController::class, 'test'])->name('admin.erp.connection.run');
        });
    });
});

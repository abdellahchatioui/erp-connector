<?php

use Illuminate\Support\Facades\Route;
use Webkul\ErpConnector\Http\Controllers\WebhookController;

Route::group(['prefix' => 'api/erp', 'middleware' => ['api', 'erp.verify']], function () {
    Route::post('/webhook/product', [WebhookController::class, 'handleProductSync']);
    Route::post('/webhook/order', [WebhookController::class, 'handleOrderSync']);
});

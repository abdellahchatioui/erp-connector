<?php

namespace Webkul\ErpConnector\Listeners;

use Webkul\ErpConnector\Jobs\PushOrderToErpJob;
use Illuminate\Support\Facades\Log;

class SendOrderToErp
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function handle($order)
    {
        Log::info("Order placed in Bagisto (ID: {$order->id}). Dispatching to ERP.");

        // Dispatch background job so we don't slow down the customer's checkout
        PushOrderToErpJob::dispatch($order);
    }
}

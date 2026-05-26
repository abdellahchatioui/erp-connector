<?php

namespace Webkul\ErpConnector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Contracts\Order;

class PushOrderToErpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @param  \Webkul\Sales\Contracts\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Initialize ERP config variables
        $erpConfig = app(\Webkul\ErpConnector\Helpers\Config::class);
        $erpUrl    = rtrim($erpConfig->getErpUrl(), '/');
        $erpToken  = $erpConfig->getErpToken();

        if (empty($erpUrl) || empty($erpToken)) {
            Log::error('PushOrderToErpJob: ERP URL or Token not configured in admin settings.');
            return;
        }

        // Retrieve JWT from Keycloak
        $jwt = app(\Webkul\ErpConnector\Services\KeycloakTokenService::class)->getToken();
        \Log::info('PushOrderToErpJob: Sending order to ERP', [
            'erpUrl'   => $erpUrl,
            'erpToken' => substr($erpToken, 0, 15) . '...',
            'jwt'      => substr($jwt, 0, 20) . '...'
        ]);

        Log::info("Pushing Order ID {$this->order->id} to ERP.");

        // Map Bagisto order into the structure expected by the Spring Boot ERP
        $mappedOrder = [
            'order_id'         => $this->order->id,
            'increment_id'     => $this->order->increment_id,
            'status'           => $this->order->status,
            'customer_email'   => $this->order->customer_email,
            'customer_name'    => $this->order->customer_first_name . ' ' . $this->order->customer_last_name,
            'grand_total'      => $this->order->grand_total,
            'base_grand_total' => $this->order->base_grand_total,
            'created_at'       => is_object($this->order->created_at) ? $this->order->created_at->toIso8601String() : $this->order->created_at,
            'items'            => $this->mapOrderItems(),
            'billing_address'  => $this->mapAddress($this->order->billing_address),
            'shipping_address' => $this->mapAddress($this->order->shipping_address),
        ];

        try {
            // Send HTTP POST request to the ERP with a timeout to prevent hanging
            $response = Http::timeout(10)
                ->withToken($jwt)
                ->withHeaders(['X-ERP-TOKEN' => $erpToken])
                ->post("{$erpUrl}/api/erp/orders", $mappedOrder);

            if ($response->failed()) {
                Log::error("Failed to push Order ID {$this->order->id} to ERP.", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            } else {
                Log::info("Successfully pushed Order ID {$this->order->id} to ERP.");
            }
        } catch (\Exception $e) {
            Log::error("Exception while pushing Order ID {$this->order->id} to ERP: " . $e->getMessage());
        }
    }

    /**
     * Map order items
     * 
     * @return array
     */
    protected function mapOrderItems()
    {
        $items = [];
        
        foreach ($this->order->items as $item) {
            $items[] = [
                'product_id' => $item->product_id,
                'sku'        => $item->sku,
                'name'       => $item->name,
                'qty'        => $item->qty_ordered,
                'price'      => $item->price,
                'total'      => $item->total,
            ];
        }

        return $items;
    }

    /**
     * Map address
     * 
     * @param mixed $address
     * @return array|null
     */
    protected function mapAddress($address)
    {
        if (! $address) {
            return null;
        }

        // Bagisto address1 can sometimes be an array or multi-line
        $address1 = is_array($address->address1) ? implode(', ', $address->address1) : $address->address1;

        return [
            'first_name' => $address->first_name,
            'last_name'  => $address->last_name,
            'email'      => $address->email,
            'address1'   => $address1,
            'city'       => $address->city,
            'state'      => $address->state,
            'postcode'   => $address->postcode,
            'country'    => $address->country,
            'phone'      => $address->phone,
        ];
    }
}

<?php

namespace App\Actions\Order;

use App\Events\OrderStatusChanged;
use App\Models\Order;

class UpdateOrderFulfillmentAction
{
    public function execute(Order $order, array $data): Order
    {
        $previousStatus = $order->fulfillment_status;

        $order->update($data);
        $order->refresh();

        if (isset($data['fulfillment_status']) && $order->fulfillment_status !== $previousStatus) {
            event(new OrderStatusChanged($order));
        }

        return $order;
    }
}

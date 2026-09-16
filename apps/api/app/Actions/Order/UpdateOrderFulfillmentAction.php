<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;

class UpdateOrderFulfillmentAction
{
    public function execute(Order $order, array $data): Order
    {
        $previousStatus = $order->fulfillment_status;

        $order->update($data);
        $order->refresh();

        if (isset($data['fulfillment_status']) && $order->fulfillment_status !== $previousStatus) {
            $order->user->notify(new OrderStatusChangedNotification($order));
        }

        return $order;
    }
}

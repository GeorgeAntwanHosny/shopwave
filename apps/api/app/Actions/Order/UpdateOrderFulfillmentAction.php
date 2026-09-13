<?php

namespace App\Actions\Order;

use App\Models\Order;

class UpdateOrderFulfillmentAction
{
    public function execute(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->fresh();
    }
}

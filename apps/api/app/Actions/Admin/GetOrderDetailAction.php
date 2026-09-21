<?php

namespace App\Actions\Admin;

use App\Models\Order;

class GetOrderDetailAction
{
    public function execute(Order $order): Order
    {
        return $order->load(['user', 'vendor', 'items']);
    }
}

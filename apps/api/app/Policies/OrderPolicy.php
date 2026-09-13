<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function viewAsVendor(User $user, Order $order): bool
    {
        return $user->vendor && $user->vendor->id === $order->vendor_id;
    }

    public function updateAsVendor(User $user, Order $order): bool
    {
        return $this->viewAsVendor($user, $order);
    }
}

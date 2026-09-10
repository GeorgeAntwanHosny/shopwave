<?php

namespace App\Actions\Checkout;

use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmailAction
{
    /**
     * @param  Collection<int, \App\Models\Order>  $orders
     */
    public function execute(Collection $orders): void
    {
        foreach ($orders as $order) {
            Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));
        }
    }
}

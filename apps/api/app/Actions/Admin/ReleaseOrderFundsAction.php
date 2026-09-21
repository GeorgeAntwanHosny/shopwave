<?php

namespace App\Actions\Admin;

use App\Models\Order;
use App\Notifications\FundsReleasedNotification;
use App\Services\StripePaymentService;
use Illuminate\Validation\ValidationException;

class ReleaseOrderFundsAction
{
    public function __construct(protected StripePaymentService $stripePaymentService)
    {
    }

    public function execute(Order $order): Order
    {
        if ($order->transferred_at) {
            throw ValidationException::withMessages(['order' => ['This order has already been paid out.']]);
        }
        if ($order->status === 'refunded') {
            throw ValidationException::withMessages(['order' => ['This order was refunded — funds cannot be released.']]);
        }

        $amountCents = (int) round(((float) $order->vendor_payout_amount) * 100);

        $transfer = $this->stripePaymentService->createTransfer(
            $amountCents,
            'usd',
            $order->vendor->stripe_account_id,
            "order_{$order->stripe_payment_intent_id}",
            ['order_id' => (string) $order->id, 'released_by' => 'admin']
        );

        $order->update(['stripe_transfer_id' => $transfer->id, 'transferred_at' => now()]);
        $order->fresh()->vendor->notify(new FundsReleasedNotification($order->fresh()));

        return $order->fresh();
    }
}

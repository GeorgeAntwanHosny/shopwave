<?php

namespace App\Actions\Checkout;

use App\Services\StripePaymentService;
use Illuminate\Support\Collection;

class CreateVendorTransfersAction
{
    public function __construct(protected StripePaymentService $stripePaymentService)
    {
    }

    /**
     * @param  Collection<int, \App\Models\Order>  $orders
     */
    public function execute(Collection $orders): void
    {
        foreach ($orders as $order) {
            if ($order->transferred_at) {
                continue; // already transferred (redelivered webhook)
            }

            $amountCents = (int) round(((float) $order->vendor_payout_amount) * 100);

            if ($amountCents <= 0) {
                continue; // e.g. a coupon covered the entire vendor subtotal
            }

            try {
                $transfer = $this->stripePaymentService->createTransfer(
                    $amountCents,
                    'usd',
                    $order->vendor->stripe_account_id,
                    "order_{$order->stripe_payment_intent_id}",
                    ['order_id' => (string) $order->id]
                );

                $order->update(['stripe_transfer_id' => $transfer->id, 'transferred_at' => now()]);
            } catch (\Throwable $e) {
                // The charge already succeeded and the order already exists —
                // don't fail the whole webhook over a transfer hiccup.
                // transferred_at stays null, marking this order as needing a
                // manual/retried payout. See Known Issues.
                report($e);
            }
        }
    }
}

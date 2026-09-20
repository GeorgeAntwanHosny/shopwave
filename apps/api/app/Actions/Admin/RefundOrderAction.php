<?php

namespace App\Actions\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderRefundedNotification;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundOrderAction
{
    public function __construct(protected StripePaymentService $stripePaymentService)
    {
    }

    public function execute(Order $order, ?string $reason = null): Order
    {
        if ($order->status === 'refunded') {
            throw ValidationException::withMessages(['order' => ['This order has already been refunded.']]);
        }

        // If the vendor's payout already went out, claw it back first — a
        // customer refund does NOT automatically reverse a completed
        // Transfer under Stripe's "Separate Charges and Transfers" pattern.
        if ($order->transferred_at && $order->stripe_transfer_id) {
            $payoutCents = (int) round(((float) $order->vendor_payout_amount) * 100);
            $this->stripePaymentService->reverseTransfer($order->stripe_transfer_id, $payoutCents);
        }

        $amountCents = (int) round(((float) $order->total) * 100);
        $refund = $this->stripePaymentService->createRefund($order->stripe_payment_intent_id, $amountCents, $reason);

        DB::transaction(function () use ($order, $refund, $reason) {
            $order->update([
                'status' => 'refunded',
                'stripe_refund_id' => $refund->id,
                'refunded_at' => now(),
                'refund_reason' => $reason,
            ]);

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
                }
            }
        });

        $order->user->notify(new OrderRefundedNotification($order));

        return $order->fresh();
    }
}

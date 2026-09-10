<?php

namespace App\Actions\Checkout;

use App\Models\CheckoutSession;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProcessSuccessfulCheckoutAction
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Converts a checkout session's cart snapshot into real per-vendor
     * orders — this is the "webhook-driven order creation" README
     * describes. Also decrements stock and increments coupon used_count
     * here, not at "apply to cart" time (Phase 4's deferred item).
     *
     * @return Collection<int, Order>
     */
    public function execute(CheckoutSession $checkoutSession): Collection
    {
        // Idempotency: Stripe can redeliver the same webhook more than once.
        if ($checkoutSession->status === 'completed') {
            return $checkoutSession->orders;
        }

        $snapshot = $checkoutSession->cart_snapshot;
        $orders = collect();

        DB::transaction(function () use ($checkoutSession, $snapshot, &$orders) {
            $commissionPercent = (string) config('services.platform_commission_percent', 10);

            foreach ($snapshot['vendors'] as $group) {
                $subtotal = $group['subtotal'];
                $discount = $group['coupon']['discount_amount'] ?? '0.00';
                $afterDiscount = bcsub($subtotal, $discount, 2);
                $platformFee = bcdiv(bcmul($afterDiscount, $commissionPercent, 4), '100', 2);
                $vendorPayout = bcsub($afterDiscount, $platformFee, 2);

                $order = Order::create([
                    'user_id' => $checkoutSession->user_id,
                    'vendor_id' => $group['vendor_id'],
                    'checkout_session_id' => $checkoutSession->id,
                    'coupon_id' => $group['coupon']['id'] ?? null,
                    'coupon_code' => $group['coupon']['code'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'platform_fee_amount' => $platformFee,
                    'vendor_payout_amount' => $vendorPayout,
                    'total' => $afterDiscount,
                    'status' => 'paid',
                    'stripe_payment_intent_id' => $checkoutSession->stripe_payment_intent_id,
                ]);

                foreach ($group['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Guarded decrement — never goes negative even if stock
                    // changed between checkout creation and webhook delivery.
                    Product::where('id', $item['product_id'])
                        ->where('stock_quantity', '>=', $item['quantity'])
                        ->decrement('stock_quantity', $item['quantity']);
                }

                if (! empty($group['coupon']['id'])) {
                    Coupon::where('id', $group['coupon']['id'])->increment('used_count');
                }

                $orders->push($order);
            }

            $checkoutSession->update(['status' => 'completed']);
        });

        // Authenticated-only checkout means the cart key is always
        // deterministic — no need for the full resolveCartKey() machinery
        // (which also handles guest tokens) just to clear it here.
        $this->cartService->clear("cart:user:{$checkoutSession->user_id}");

        return $orders;
    }
}

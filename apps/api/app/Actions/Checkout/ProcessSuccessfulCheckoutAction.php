<?php

namespace App\Actions\Checkout;

use App\Events\LowStockAlert;
use App\Events\NewOrderReceived;
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
     * @return Collection<int, Order>
     */
    public function execute(CheckoutSession $checkoutSession): Collection
    {
        if ($checkoutSession->status === 'completed') {
            return $checkoutSession->orders;
        }

        $snapshot = $checkoutSession->cart_snapshot;
        $orders = collect();
        $lowStockProducts = collect();

        DB::transaction(function () use ($checkoutSession, $snapshot, &$orders, &$lowStockProducts) {
            $commissionPercent = (string) config('services.platform_commission_percent', 10);
            $lowStockThreshold = (int) config('shopwave.low_stock_threshold');

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

                    $product = Product::find($item['product_id']);

                    if ($product) {
                        $previousStock = $product->stock_quantity;

                        $decremented = Product::where('id', $item['product_id'])
                            ->where('stock_quantity', '>=', $item['quantity'])
                            ->decrement('stock_quantity', $item['quantity']);

                        // Only alert on the moment stock CROSSES the
                        // threshold — not on every subsequent purchase while
                        // already low, which would spam identical alerts.
                        if ($decremented > 0) {
                            $newStock = $previousStock - $item['quantity'];
                            if ($previousStock > $lowStockThreshold && $newStock <= $lowStockThreshold) {
                                $lowStockProducts->push($product->fresh());
                            }
                        }
                    }
                }

                if (! empty($group['coupon']['id'])) {
                    Coupon::where('id', $group['coupon']['id'])->increment('used_count');
                }

                $orders->push($order);
            }

            $checkoutSession->update(['status' => 'completed']);
        });

        $this->cartService->clear("cart:user:{$checkoutSession->user_id}");

        // Fired only after the transaction has committed — an event for an
        // order that ultimately rolled back would be worse than none at all.
        foreach ($orders as $order) {
            event(new NewOrderReceived($order));
        }
        foreach ($lowStockProducts as $product) {
            event(new LowStockAlert($product));
        }

        return $orders;
    }
}

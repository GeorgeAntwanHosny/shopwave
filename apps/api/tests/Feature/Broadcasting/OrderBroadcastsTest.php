<?php

namespace Tests\Feature\Broadcasting;

use App\Actions\Checkout\ProcessSuccessfulCheckoutAction;
use App\Events\LowStockAlert;
use App\Events\NewOrderReceived;
use App\Models\CheckoutSession;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class OrderBroadcastsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    protected function snapshotFor(Vendor $vendor, Product $product, int $quantity, string $price): array
    {
        $subtotal = bcmul($price, (string) $quantity, 2);

        return [
            'vendors' => [[
                'vendor_id' => $vendor->id,
                'shop_name' => $vendor->shop_name,
                'items' => [[
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ]],
                'subtotal' => $subtotal,
                'coupon' => null,
                'total_after_discount' => $subtotal,
            ]],
            'unavailable_items' => [],
            'grand_total' => $subtotal,
            'item_count' => $quantity,
        ];
    }

    public function test_a_new_order_dispatches_new_order_received(): void
    {
        Event::fake([NewOrderReceived::class, LowStockAlert::class]);

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 20]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 1, '50.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Event::assertDispatched(NewOrderReceived::class, fn ($e) => $e->order->vendor_id === $vendor->id);
    }

    public function test_stock_crossing_the_low_stock_threshold_dispatches_low_stock_alert(): void
    {
        Event::fake([NewOrderReceived::class, LowStockAlert::class]);

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        // Test env's LOW_STOCK_THRESHOLD is 5 — starts at 6, buying 2 drops
        // it to 4, crossing the threshold.
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 6]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 2, '10.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Event::assertDispatched(LowStockAlert::class, fn ($e) => $e->product->id === $product->id);
    }

    public function test_stock_already_below_threshold_does_not_redispatch_low_stock_alert(): void
    {
        Event::fake([NewOrderReceived::class, LowStockAlert::class]);

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 3]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 1, '10.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Event::assertNotDispatched(LowStockAlert::class);
    }
}

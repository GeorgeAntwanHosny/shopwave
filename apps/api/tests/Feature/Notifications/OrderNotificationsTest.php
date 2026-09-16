<?php

namespace Tests\Feature\Notifications;

use App\Actions\Checkout\ProcessSuccessfulCheckoutAction;
use App\Models\CheckoutSession;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\LowStockAlertNotification;
use App\Notifications\NewOrderReceivedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
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

    public function test_a_new_order_notifies_the_vendor(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 20]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 1, '50.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Notification::assertSentTo($vendor, NewOrderReceivedNotification::class);
    }

    public function test_stock_crossing_the_low_stock_threshold_notifies_the_vendor(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 6]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 2, '10.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Notification::assertSentTo($vendor, LowStockAlertNotification::class);
    }

    public function test_stock_already_below_threshold_does_not_renotify(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 3]);

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $this->snapshotFor($vendor, $product, 1, '10.00'),
            'status' => 'pending',
        ]);

        app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        Notification::assertNotSentTo($vendor, LowStockAlertNotification::class);
    }
}

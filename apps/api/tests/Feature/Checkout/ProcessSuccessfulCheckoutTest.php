<?php

namespace Tests\Feature\Checkout;

use App\Actions\Checkout\ProcessSuccessfulCheckoutAction;
use App\Models\CheckoutSession;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class ProcessSuccessfulCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_it_creates_one_order_per_vendor_decrements_stock_and_increments_coupon_use(): void
    {
        $user = User::factory()->create();
        $vendorA = Vendor::factory()->create();
        $productA = Product::factory()->create(['vendor_id' => $vendorA->id, 'price' => 50, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->create(['vendor_id' => $vendorA->id, 'type' => 'fixed', 'value' => 10, 'used_count' => 0]);

        $snapshot = [
            'vendors' => [[
                'vendor_id' => $vendorA->id,
                'shop_name' => $vendorA->shop_name,
                'items' => [[
                    'product_id' => $productA->id,
                    'name' => $productA->name,
                    'price' => '50.00',
                    'quantity' => 2,
                    'subtotal' => '100.00',
                ]],
                'subtotal' => '100.00',
                'coupon' => ['id' => $coupon->id, 'code' => $coupon->code, 'type' => 'fixed', 'value' => '10.00', 'discount_amount' => '10.00'],
                'total_after_discount' => '90.00',
            ]],
            'unavailable_items' => [],
            'grand_total' => '90.00',
            'item_count' => 2,
        ];

        $checkoutSession = CheckoutSession::factory()->create([
            'user_id' => $user->id,
            'cart_snapshot' => $snapshot,
            'status' => 'pending',
        ]);

        $orders = app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        $this->assertCount(1, $orders);
        $this->assertDatabaseHas('orders', [
            'vendor_id' => $vendorA->id,
            'subtotal' => '100.00',
            'discount_amount' => '10.00',
            'platform_fee_amount' => '9.00', // 10% of the 90 after discount
            'vendor_payout_amount' => '81.00',
        ]);
        $this->assertEquals(8, $productA->fresh()->stock_quantity);
        $this->assertEquals(1, $coupon->fresh()->used_count);
        $this->assertEquals('completed', $checkoutSession->fresh()->status);
    }

    public function test_it_is_idempotent_on_a_redelivered_webhook(): void
    {
        $checkoutSession = CheckoutSession::factory()->create(['status' => 'completed']);

        $orders = app(ProcessSuccessfulCheckoutAction::class)->execute($checkoutSession);

        $this->assertCount(0, $orders); // no duplicate creation, no error
    }
}

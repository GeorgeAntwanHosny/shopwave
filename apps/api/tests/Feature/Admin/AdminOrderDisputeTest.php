<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\StripePaymentService;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Stripe\Refund;
use Stripe\Transfer;
use Tests\TestCase;

class AdminOrderDisputeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_refund_an_order_not_yet_transferred(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $order = Order::factory()->create([
            'status' => 'paid', 'total' => 50, 'transferred_at' => null,
            'stripe_payment_intent_id' => 'pi_test123',
        ]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2]);

        $refund = Refund::constructFrom(['id' => 're_test123']);
        $this->mock(StripePaymentService::class, function ($mock) use ($refund) {
            $mock->shouldReceive('createRefund')->once()->andReturn($refund);
        });

        Sanctum::actingAs($admin);
        $response = $this->postJson("/api/v1/admin/orders/{$order->id}/refund", ['reason' => 'Damaged item']);

        $response->assertStatus(200);
        $this->assertEquals('refunded', $order->fresh()->status);
        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_refunding_an_already_transferred_order_reverses_the_transfer_first(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create([
            'status' => 'paid', 'total' => 50, 'vendor_payout_amount' => 40,
            'transferred_at' => now(), 'stripe_transfer_id' => 'tr_test123',
            'stripe_payment_intent_id' => 'pi_test123',
        ]);

        $refund = Refund::constructFrom(['id' => 're_test123']);
        $this->mock(StripePaymentService::class, function ($mock) use ($refund) {
            $mock->shouldReceive('reverseTransfer')->once()->with('tr_test123', 4000);
            $mock->shouldReceive('createRefund')->once()->andReturn($refund);
        });

        Sanctum::actingAs($admin);
        $response = $this->postJson("/api/v1/admin/orders/{$order->id}/refund");

        $response->assertStatus(200);
    }

    public function test_admin_can_release_funds_for_an_untransferred_order(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id, 'status' => 'paid', 'vendor_payout_amount' => 40,
            'transferred_at' => null, 'stripe_payment_intent_id' => 'pi_test123',
        ]);

        $transfer = Transfer::constructFrom(['id' => 'tr_new123']);
        $this->mock(StripePaymentService::class, function ($mock) use ($transfer) {
            $mock->shouldReceive('createTransfer')->once()->andReturn($transfer);
        });

        Sanctum::actingAs($admin);
        $response = $this->postJson("/api/v1/admin/orders/{$order->id}/release-funds");

        $response->assertStatus(200);
        $this->assertNotNull($order->fresh()->transferred_at);
    }

    public function test_cannot_release_funds_twice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create(['transferred_at' => now()]);
        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/admin/orders/{$order->id}/release-funds");

        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\FundsReleasedNotification;
use App\Notifications\OrderRefundedNotification;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Stripe\Refund;
use Stripe\Transfer;
use Tests\TestCase;

class AdminOrderDisputeNotifiesVendorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_refunding_an_order_notifies_both_customer_and_vendor(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id, 'status' => 'paid', 'total' => 50,
            'transferred_at' => null, 'stripe_payment_intent_id' => 'pi_test123',
        ]);

        $refund = Refund::constructFrom(['id' => 're_test123']);
        $this->mock(StripePaymentService::class, fn ($mock) => $mock->shouldReceive('createRefund')->once()->andReturn($refund));

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/orders/{$order->id}/refund");

        Notification::assertSentTo($order->fresh()->user, OrderRefundedNotification::class);
        Notification::assertSentTo($vendor, OrderRefundedNotification::class);
    }

    public function test_releasing_funds_notifies_the_vendor(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create([
            'vendor_id' => $vendor->id, 'status' => 'paid', 'vendor_payout_amount' => 40,
            'transferred_at' => null, 'stripe_payment_intent_id' => 'pi_test123',
        ]);

        $transfer = Transfer::constructFrom(['id' => 'tr_new123']);
        $this->mock(StripePaymentService::class, fn ($mock) => $mock->shouldReceive('createTransfer')->once()->andReturn($transfer));

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/orders/{$order->id}/release-funds");

        Notification::assertSentTo($vendor, FundsReleasedNotification::class);
    }
}

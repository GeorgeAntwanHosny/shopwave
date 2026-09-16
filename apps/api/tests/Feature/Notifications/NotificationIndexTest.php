<?php

namespace Tests\Feature\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\NewOrderReceivedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
{
    public function test_user_can_list_their_notifications(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $user->notify(new OrderStatusChangedNotification($order));
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_mark_all_read_clears_unread_notifications(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $user->notify(new OrderStatusChangedNotification($order));
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/notifications/mark-all-read');
        $response = $this->getJson('/api/v1/notifications');

        $this->assertTrue($response->json('data.0.read'));
    }

    public function test_a_vendor_sees_notifications_from_both_their_customer_and_vendor_identities(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);
        $order = Order::factory()->create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
        $user->notify(new OrderStatusChangedNotification($order));
        $vendor->notify(new NewOrderReceivedNotification($order));
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications');

        $this->assertCount(2, $response->json('data'));
    }
}

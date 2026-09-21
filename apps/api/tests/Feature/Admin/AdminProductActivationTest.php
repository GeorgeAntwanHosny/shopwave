<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductDeactivatedNotification;
use App\Notifications\ProductReactivatedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProductActivationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_deactivate_a_product_and_vendor_is_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/products/{$product->id}/deactivate", ['reason' => 'Policy violation']);

        $this->assertFalse($product->fresh()->is_active);
        Notification::assertSentTo($product->vendor, ProductDeactivatedNotification::class);
    }

    public function test_admin_can_reactivate_a_product_and_vendor_is_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create(['is_active' => false]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/products/{$product->id}/activate");

        $this->assertTrue($product->fresh()->is_active);
        Notification::assertSentTo($product->vendor, ProductReactivatedNotification::class);
    }
}

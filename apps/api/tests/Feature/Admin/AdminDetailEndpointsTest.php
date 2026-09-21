<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDetailEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_view_vendor_detail_with_counts(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $vendor = Vendor::factory()->create();
        Product::factory()->count(2)->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/admin/vendors/{$vendor->id}");

        $response->assertStatus(200)->assertJsonPath('data.products_count', 2);
    }

    public function test_admin_can_view_product_detail_with_sales_stats(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create();
        $order = Order::factory()->create(['status' => 'paid']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 3]);
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.units_sold', 3)
            ->assertJsonPath('data.orders_count', 1);
    }

    public function test_admin_can_view_order_detail_with_user_vendor_and_items(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create();
        OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.user'));
        $this->assertNotNull($response->json('data.vendor'));
        $this->assertCount(1, $response->json('data.items'));
    }

    public function test_products_and_orders_can_be_filtered_by_vendor(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $vendor = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $mineProduct = Product::factory()->create(['vendor_id' => $vendor->id]);
        Product::factory()->create(['vendor_id' => $other->id]);
        $mineOrder = Order::factory()->create(['vendor_id' => $vendor->id]);
        Order::factory()->create(['vendor_id' => $other->id]);
        Sanctum::actingAs($admin);

        $productsResponse = $this->getJson("/api/v1/admin/products?vendor_id={$vendor->id}");
        $ordersResponse = $this->getJson("/api/v1/admin/orders?vendor_id={$vendor->id}");

        $this->assertEquals([$mineProduct->id], collect($productsResponse->json('data.products'))->pluck('id')->all());
        $this->assertEquals([$mineOrder->id], collect($ordersResponse->json('data.orders'))->pluck('id')->all());
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProductModerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_flag_and_unflag_a_product(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create(['is_flagged' => false]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/products/{$product->id}/flag", ['reason' => 'Suspicious pricing']);
        $this->assertTrue($product->fresh()->is_flagged);

        $this->postJson("/api/v1/admin/products/{$product->id}/unflag");
        $this->assertFalse($product->fresh()->is_flagged);
    }

    public function test_admin_can_deactivate_a_product(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/products/{$product->id}/deactivate");
        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_products_can_be_filtered_by_flagged_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $flagged = Product::factory()->create(['is_flagged' => true]);
        Product::factory()->create(['is_flagged' => false]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/admin/products?flagged=1');

        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertEquals([$flagged->id], $ids->all());
    }
}

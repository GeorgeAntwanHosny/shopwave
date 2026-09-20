<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminVendorTest extends TestCase
{
    protected Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset permission cache for test isolation
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure admin role exists specifically for sanctum guard
        $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    }

    public function test_admin_can_list_vendors(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole($this->adminRole);
        Vendor::factory()->count(2)->create();

        Sanctum::actingAs($admin, ['*'], 'sanctum');

        $response = $this->getJson('/api/v1/admin/vendors');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.vendors'));
    }

    public function test_admin_can_suspend_and_reactivate_a_vendor(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole($this->adminRole);
        $vendor = Vendor::factory()->create(['is_suspended' => false]);

        Sanctum::actingAs($admin, ['*'], 'sanctum');

        $this->postJson("/api/v1/admin/vendors/{$vendor->id}/suspend", ['reason' => 'Fraud reports']);
        $this->assertTrue($vendor->fresh()->is_suspended);

        $this->postJson("/api/v1/admin/vendors/{$vendor->id}/reactivate");
        $this->assertFalse($vendor->fresh()->is_suspended);
    }

    public function test_suspended_vendors_products_are_hidden_from_public_catalog(): void
    {
        $vendor = Vendor::factory()->create(['is_suspended' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products');

        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertFalse($ids->contains($product->id));
    }
}

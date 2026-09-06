<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductStoreTest extends TestCase
{
    public function test_onboarded_vendor_can_create_a_product(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson('/api/v1/vendor/products', [
            'name' => 'Cool Gadget',
            'price' => 49.99,
            'stock_quantity' => 10,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'Cool Gadget');
        $this->assertDatabaseHas('products', ['vendor_id' => $vendor->id, 'name' => 'Cool Gadget']);
    }

    public function test_vendor_with_incomplete_onboarding_cannot_create_a_product(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => false]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson('/api/v1/vendor/products', [
            'name' => 'Cool Gadget', 'price' => 49.99, 'stock_quantity' => 10,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_vendor_cannot_create_a_product(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/vendor/products', [
            'name' => 'Cool Gadget', 'price' => 49.99, 'stock_quantity' => 10,
        ]);

        $response->assertStatus(403);
    }
}

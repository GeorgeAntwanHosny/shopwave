<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductUpdateTest extends TestCase
{
    public function test_owner_can_update_their_product(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Old Name']);
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/products/{$product->id}", ['name' => 'New Name']);

        $response->assertStatus(200)->assertJsonPath('data.name', 'New Name');
    }

    public function test_non_owner_cannot_update_the_product(): void
    {
        $owner = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($otherVendor->user);

        $response = $this->putJson("/api/v1/vendor/products/{$product->id}", ['name' => 'Hacked Name']);

        $response->assertStatus(403);
    }
}

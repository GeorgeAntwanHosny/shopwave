<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductDestroyTest extends TestCase
{
    public function test_owner_can_delete_their_product(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->deleteJson("/api/v1/vendor/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_non_owner_cannot_delete_the_product(): void
    {
        $owner = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($otherVendor->user);

        $response = $this->deleteJson("/api/v1/vendor/products/{$product->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}

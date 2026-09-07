<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductShowTest extends TestCase
{
    public function test_owner_can_view_their_product_detail(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson("/api/v1/vendor/products/{$product->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $product->id);
    }

    public function test_non_owner_cannot_view_the_product_detail(): void
    {
        $owner = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($otherVendor->user);

        $response = $this->getJson("/api/v1/vendor/products/{$product->id}");

        $response->assertStatus(403);
    }
}

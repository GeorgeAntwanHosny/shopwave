<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorProductIndexTest extends TestCase
{
    public function test_vendor_sees_only_their_own_products_including_inactive(): void
    {
        $vendor = Vendor::factory()->create();
        $other = Vendor::factory()->create();

        $mine = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);
        $myInactive = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => false]);
        Product::factory()->create(['vendor_id' => $other->id]);

        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/products');

        $response->assertStatus(200);
        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertTrue($ids->contains($mine->id));
        $this->assertTrue($ids->contains($myInactive->id));
        $this->assertCount(2, $ids);
    }

    public function test_vendor_products_can_be_filtered_by_status(): void
    {
        $vendor = Vendor::factory()->create();
        $active = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true]);
        Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => false]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/products?is_active=1');

        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertEquals([$active->id], $ids->all());
    }

    public function test_vendor_products_search_matches_a_single_character(): void
    {
        $vendor = Vendor::factory()->create();
        $match = Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Zebra Print Scarf']);
        Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Kitchen Blender']);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/products?q=Z');

        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertTrue($ids->contains($match->id));
    }
}

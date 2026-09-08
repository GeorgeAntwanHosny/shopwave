<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorCouponIndexTest extends TestCase
{
    public function test_vendor_sees_only_their_own_coupons(): void
    {
        $vendor = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $mine = Coupon::factory()->create(['vendor_id' => $vendor->id]);
        Coupon::factory()->create(['vendor_id' => $other->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/coupons');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$mine->id], $ids->all());
    }
    public function test_vendor_with_no_coupons_sees_empty_list(): void
    {
        $vendor = Vendor::factory()->create();
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/coupons');

        $response->assertStatus(200)->assertJsonPath('data', []);
    }
}

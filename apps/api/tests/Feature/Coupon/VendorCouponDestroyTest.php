<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorCouponDestroyTest extends TestCase
{
    public function test_owner_can_delete_their_coupon(): void
    {
        $vendor = Vendor::factory()->create();
        $coupon = Coupon::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->deleteJson("/api/v1/vendor/coupons/{$coupon->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    public function test_non_owner_cannot_delete_the_coupon(): void
    {
        $owner = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $coupon = Coupon::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($other->user);

        $response = $this->deleteJson("/api/v1/vendor/coupons/{$coupon->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id]);
    }
}

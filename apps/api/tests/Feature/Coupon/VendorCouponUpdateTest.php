<?php

namespace Tests\Feature\Coupon;

use App\Models\Coupon;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorCouponUpdateTest extends TestCase
{
    public function test_owner_can_update_their_coupon(): void
    {
        $vendor = Vendor::factory()->create();
        $coupon = Coupon::factory()->create(['vendor_id' => $vendor->id, 'value' => 10]);
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/coupons/{$coupon->id}", ['value' => 20]);

        $response->assertStatus(200)->assertJsonPath('data.value', '20.00');
    }

    public function test_non_owner_cannot_update_the_coupon(): void
    {
        $owner = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $coupon = Coupon::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($other->user);

        $response = $this->putJson("/api/v1/vendor/coupons/{$coupon->id}", ['value' => 20]);

        $response->assertStatus(403);
    }
}

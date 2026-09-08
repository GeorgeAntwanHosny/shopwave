<?php

namespace Tests\Feature\Coupon;

use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorCouponStoreTest extends TestCase
{
    public function test_onboarded_vendor_can_create_a_coupon(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson('/api/v1/vendor/coupons', [
            'code' => 'save10',
            'type' => 'percentage',
            'value' => 10,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.code', 'SAVE10');
        $this->assertDatabaseHas('coupons', [
            'vendor_id' => $vendor->id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
        ]);
    }

    public function test_percentage_coupon_over_100_is_rejected(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => true]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson('/api/v1/vendor/coupons', [
            'code' => 'TOOBIG', 'type' => 'percentage', 'value' => 150,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('coupons', [
            'vendor_id' => $vendor->id,
            'code' => 'TOOBIG',
        ]);
    }

    public function test_vendor_with_incomplete_onboarding_cannot_create_a_coupon(): void
    {
        $vendor = Vendor::factory()->create(['stripe_onboarding_complete' => false]);
        Sanctum::actingAs($vendor->user);

        $response = $this->postJson('/api/v1/vendor/coupons', [
            'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('coupons', [
            'vendor_id' => $vendor->id,
            'code' => 'SAVE10',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\CheckoutSession;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 300);
        $fee = round($subtotal * 0.10, 2);

        return [
            'user_id' => User::factory(),
            'vendor_id' => Vendor::factory(),
            'checkout_session_id' => CheckoutSession::factory(),
            'coupon_id' => null,
            'coupon_code' => null,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'platform_fee_amount' => $fee,
            'vendor_payout_amount' => $subtotal - $fee,
            'total' => $subtotal,
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########'),
            'stripe_transfer_id' => null,
            'transferred_at' => null,
        ];
    }
}

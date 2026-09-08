<?php

namespace App\Actions\Coupon;

use App\Models\Coupon;
use App\Models\User;

class CreateCouponAction
{
    public function execute(User $user, array $data): Coupon
    {
        return Coupon::create([
            'vendor_id' => $user->vendor->id,
            'code' => strtoupper($data['code']),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}   

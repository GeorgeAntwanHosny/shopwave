<?php

namespace App\Policies;

use App\Models\Coupon;
use App\Models\User;

class CouponPolicy
{
    public function update(User $user, Coupon $coupon): bool
    {
        return $user->vendor && $user->vendor->id === $coupon->vendor_id;
    }

    public function delete(User $user, Coupon $coupon): bool
    {
        return $this->update($user, $coupon);
    }
}

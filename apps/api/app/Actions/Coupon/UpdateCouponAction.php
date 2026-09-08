<?php

namespace App\Actions\Coupon;

use App\Models\Coupon;

class UpdateCouponAction
{
    public function execute(Coupon $coupon, array $data): Coupon
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        $coupon->update($data);

        return $coupon->fresh();
    }
}

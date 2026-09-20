<?php

namespace App\Actions\Admin;

use App\Models\Vendor;
use App\Notifications\VendorReactivatedNotification;

class ReactivateVendorAction
{
    public function execute(Vendor $vendor): Vendor
    {
        $vendor->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        $vendor->notify(new VendorReactivatedNotification($vendor));

        return $vendor->fresh();
    }
}

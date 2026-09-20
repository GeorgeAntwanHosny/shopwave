<?php

namespace App\Actions\Admin;

use App\Models\Vendor;
use App\Notifications\VendorSuspendedNotification;

class SuspendVendorAction
{
    public function execute(Vendor $vendor, ?string $reason): Vendor
    {
        $vendor->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);

        $vendor->notify(new VendorSuspendedNotification($vendor, $reason));

        return $vendor->fresh();
    }
}

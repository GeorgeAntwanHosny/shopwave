<?php

namespace App\Actions\Admin;

use App\Models\Vendor;

class GetVendorDetailAction
{
    public function execute(Vendor $vendor): array
    {
        $vendor->loadCount(['products', 'orders']);
        $vendor->load('user:id,name,email');

        return $vendor->toArray();
    }
}

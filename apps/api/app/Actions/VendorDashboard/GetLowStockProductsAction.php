<?php

namespace App\Actions\VendorDashboard;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;

class GetLowStockProductsAction
{
    public function execute(Vendor $vendor): Collection
    {
        return $vendor->products()
            ->where('is_active', true)
            ->where('stock_quantity', '<=', config('shopwave.low_stock_threshold'))
            ->orderBy('stock_quantity')
            ->get(['id', 'name', 'slug', 'stock_quantity']);
    }
}

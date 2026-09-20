<?php

namespace App\Actions\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;

class GetPlatformStatsAction
{
    public function execute(): array
    {
        $paidOrders = Order::where('status', 'paid');

        return [
            'gmv' => number_format((float) (clone $paidOrders)->sum('total'), 2, '.', ''),
            'platform_revenue' => number_format((float) (clone $paidOrders)->sum('platform_fee_amount'), 2, '.', ''),
            'orders_count' => (clone $paidOrders)->count(),
            'pending_payouts_count' => (clone $paidOrders)->whereNull('transferred_at')->count(),
            'vendors_count' => Vendor::count(),
            'active_products_count' => Product::where('is_active', true)->count(),
            'flagged_products_count' => Product::where('is_flagged', true)->count(),
        ];
    }
}

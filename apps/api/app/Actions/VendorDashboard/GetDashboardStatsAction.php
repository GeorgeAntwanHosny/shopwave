<?php

namespace App\Actions\VendorDashboard;

use App\Models\Vendor;

class GetDashboardStatsAction
{
    /**
     * These are read-only display aggregates, not money-moving
     * calculations — General Convention #13 (bcmath for all money math)
     * applies to values that get written back (checkout, transfers), not
     * to a SUM() used purely to populate a dashboard card.
     */
    public function execute(Vendor $vendor): array
    {
        $paidOrders = $vendor->orders()->where('status', 'paid');

        $ordersCount = (clone $paidOrders)->count();
        $totalRevenue = (float) (clone $paidOrders)->sum('vendor_payout_amount');
        $pendingPayouts = (clone $paidOrders)->whereNull('transferred_at')->count();
        $averageOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0.0;

        $lowStockCount = $vendor->products()
            ->where('is_active', true)
            ->where('stock_quantity', '<=', config('shopwave.low_stock_threshold'))
            ->count();

        return [
            'total_revenue' => number_format($totalRevenue, 2, '.', ''),
            'orders_count' => $ordersCount,
            'average_order_value' => number_format($averageOrderValue, 2, '.', ''),
            'pending_payouts_count' => $pendingPayouts,
            'low_stock_count' => $lowStockCount,
        ];
    }
}

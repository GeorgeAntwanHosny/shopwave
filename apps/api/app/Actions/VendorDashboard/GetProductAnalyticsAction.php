<?php

namespace App\Actions\VendorDashboard;

use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetProductAnalyticsAction
{
    public function execute(Vendor $vendor, ?string $dateFrom, ?string $dateTo): Collection
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.vendor_id', $vendor->id)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.quantity) as units_sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'units_sold' => (int) $row->units_sold,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }
}

<?php

namespace App\Actions\Admin;

use App\Models\Order;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class GetPlatformOrdersChartAction
{
    public function execute(int $days = 30): array
    {
        // 1. Determine start date (midnight of N days ago)
        $startDate = Carbon::today()->subDays($days - 1);

        // 2. Query aggregate counts and map as a flat [date_string => count] dictionary
        $rows = Order::where('created_at', '>=', $startDate)
            ->where('status', 'paid') // Filter for valid/paid orders
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as orders_count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('orders_count', 'date_key');

        // 3. Guarantee a continuous date series, defaulting missing days to 0
        $series = [];
        $period = CarbonPeriod::create($startDate, Carbon::today());

        foreach ($period as $date) {
            $formattedDate = $date->toDateString();

            $series[] = [
                'date' => $formattedDate,
                'orders_count' => (int) ($rows[$formattedDate] ?? 0),
            ];
        }

        return $series;
    }
}

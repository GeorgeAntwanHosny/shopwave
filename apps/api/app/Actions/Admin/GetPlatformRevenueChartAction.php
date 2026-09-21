<?php

namespace App\Actions\Admin;

use App\Models\Order;
use Illuminate\Support\Carbon;

class GetPlatformRevenueChartAction
{
    public function execute(int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $rows = Order::where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total) as gmv, SUM(platform_fee_amount) as platform_revenue')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $row = $rows->get($date);
            $series[] = [
                'date' => $date,
                'gmv' => number_format((float) ($row->gmv ?? 0), 2, '.', ''),
                'platform_revenue' => number_format((float) ($row->platform_revenue ?? 0), 2, '.', ''),
            ];
        }

        return $series;
    }
}

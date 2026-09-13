<?php

namespace App\Actions\VendorDashboard;

use App\Models\Vendor;
use Illuminate\Support\Carbon;

class GetRevenueChartAction
{
    public function execute(Vendor $vendor, int $days = 30): array
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $rows = $vendor->orders()
            ->where('status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(vendor_payout_amount) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $series[] = [
                'date' => $date,
                'revenue' => number_format((float) ($rows[$date] ?? 0), 2, '.', ''),
            ];
        }

        return $series;
    }
}

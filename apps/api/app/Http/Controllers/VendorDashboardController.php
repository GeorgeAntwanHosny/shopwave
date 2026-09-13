<?php

namespace App\Http\Controllers;

use App\Actions\VendorDashboard\GetDashboardStatsAction;
use App\Actions\VendorDashboard\GetLowStockProductsAction;
use App\Actions\VendorDashboard\GetProductAnalyticsAction;
use App\Actions\VendorDashboard\GetRevenueChartAction;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorDashboardController extends Controller
{
    public function stats(Request $request, GetDashboardStatsAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->user()->vendor), 'Dashboard stats retrieved.');
    }

    public function revenueChart(Request $request, GetRevenueChartAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->user()->vendor), 'Revenue chart retrieved.');
    }

    public function lowStock(Request $request, GetLowStockProductsAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($request->user()->vendor), 'Low-stock products retrieved.');
    }

    public function analytics(Request $request, GetProductAnalyticsAction $action): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $data = $action->execute($request->user()->vendor, $filters['date_from'] ?? null, $filters['date_to'] ?? null);

        return ApiResponse::success($data, 'Product analytics retrieved.');
    }
}

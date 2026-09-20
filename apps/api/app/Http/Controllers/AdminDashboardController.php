<?php

namespace App\Http\Controllers;

use App\Actions\Admin\GetPlatformStatsAction;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(GetPlatformStatsAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Platform stats retrieved.');
    }
}

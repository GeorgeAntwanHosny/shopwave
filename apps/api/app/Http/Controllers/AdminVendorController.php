<?php

namespace App\Http\Controllers;

use App\Actions\Admin\GetVendorDetailAction;
use App\Actions\Admin\ReactivateVendorAction;
use App\Actions\Admin\SuspendVendorAction;
use App\Http\Responses\ApiResponse;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    public function index(): JsonResponse
    {
        $vendors = Vendor::withCount(['products', 'orders'])->latest()->paginate(15);

        return ApiResponse::success([
            'vendors' => $vendors->items(),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'total' => $vendors->total(),
            ],
        ], 'Vendors retrieved.');
    }

    public function show(Vendor $vendor, GetVendorDetailAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($vendor), 'Vendor retrieved.');
    }

    public function suspend(Request $request, Vendor $vendor, SuspendVendorAction $action): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $updated = $action->execute($vendor, $data['reason'] ?? null);

        return ApiResponse::success($updated, 'Vendor suspended.');
    }

    public function reactivate(Vendor $vendor, ReactivateVendorAction $action): JsonResponse
    {
        $updated = $action->execute($vendor);

        return ApiResponse::success($updated, 'Vendor reactivated.');
    }
}

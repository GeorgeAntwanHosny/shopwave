<?php

namespace App\Http\Controllers;

use App\Actions\Admin\GetOrderDetailAction;
use App\Actions\Admin\ReleaseOrderFundsAction;
use App\Actions\Admin\RefundOrderAction;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:paid,refunded'],
            'transferred' => ['nullable', 'boolean'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
        ]);

        $query = Order::with(['user', 'vendor', 'items']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['transferred']) && $filters['transferred'] !== '') {
            $filters['transferred'] ? $query->whereNotNull('transferred_at') : $query->whereNull('transferred_at');
        }
        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return ApiResponse::success([
            'orders' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ], 'Orders retrieved.');
    }

    public function show(Order $order, GetOrderDetailAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($order), 'Order retrieved.');
    }

    public function refund(Request $request, Order $order, RefundOrderAction $action): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $updated = $action->execute($order, $data['reason'] ?? null);

        return ApiResponse::success($updated, 'Order refunded.');
    }

    public function releaseFunds(Order $order, ReleaseOrderFundsAction $action): JsonResponse
    {
        $updated = $action->execute($order);

        return ApiResponse::success($updated, 'Funds released to vendor.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Order\UpdateOrderFulfillmentAction;
use App\Http\Requests\Order\UpdateOrderFulfillmentRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:paid,refunded'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = $request->user()->vendor->orders()->with(['items', 'user']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return ApiResponse::success([
            'orders' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ], 'Received orders retrieved.');
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('viewAsVendor', $order);

        return ApiResponse::success($order->load(['items.review.reply', 'user']), 'Order retrieved.');
    }

    public function update(UpdateOrderFulfillmentRequest $request, Order $order, UpdateOrderFulfillmentAction $action): JsonResponse
    {
        $updated = $action->execute($order, $request->validated());

        return ApiResponse::success($updated, 'Order updated.');
    }
}

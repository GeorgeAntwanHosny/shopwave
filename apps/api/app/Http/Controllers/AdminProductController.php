<?php

namespace App\Http\Controllers;

use App\Actions\Admin\ActivateProductAction;
use App\Actions\Admin\DeactivateProductAction;
use App\Actions\Admin\FlagProductAction;
use App\Actions\Admin\GetProductDetailAction;
use App\Actions\Admin\UnflagProductAction;
use App\Actions\Product\ListProductsAction;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    public function index(Request $request, ListProductsAction $action): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'flagged' => ['nullable', 'boolean'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,rating'],
        ]);

        $products = $action->execute(Product::query()->with('vendor'), $filters);

        return ApiResponse::success([
            'products' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ], 'Products retrieved.');
    }

    public function show(Product $product, GetProductDetailAction $action): JsonResponse
    {
        return ApiResponse::success($action->execute($product), 'Product retrieved.');
    }

    public function flag(Request $request, Product $product, FlagProductAction $action): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $updated = $action->execute($product, $data['reason']);

        return ApiResponse::success($updated, 'Product flagged.');
    }

    public function unflag(Product $product, UnflagProductAction $action): JsonResponse
    {
        $updated = $action->execute($product);

        return ApiResponse::success($updated, 'Product unflagged.');
    }

    public function deactivate(Request $request, Product $product, DeactivateProductAction $action): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $updated = $action->execute($product, $data['reason'] ?? null);

        return ApiResponse::success($updated, 'Product deactivated.');
    }

    public function activate(Request $request, Product $product, ActivateProductAction $action): JsonResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $updated = $action->execute($product, $data['reason'] ?? null);

        return ApiResponse::success($updated, 'Product reactivated.');
    }
}

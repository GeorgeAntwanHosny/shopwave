<?php

namespace App\Http\Controllers;

use App\Actions\Admin\DeactivateProductAction;
use App\Actions\Admin\FlagProductAction;
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

    public function deactivate(Product $product, DeactivateProductAction $action): JsonResponse
    {
        $updated = $action->execute($product);

        return ApiResponse::success($updated, 'Product deactivated.');
    }
}

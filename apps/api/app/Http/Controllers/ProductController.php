<?php

namespace App\Http\Controllers;

use App\Actions\Product\ListProductsAction;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsAction $action): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'rating_min' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,rating'],
        ]);

        $products = $action->execute(Product::query()->where('is_active', true), $filters);

        return ApiResponse::success([
            'products' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ], 'Products retrieved.');
    }

    public function featured(): JsonResponse
    {
        $products = Cache::remember('products:featured', now()->addMinutes(15), function () {
            return Product::where('is_active', true)
                ->with(['vendor', 'images'])
                ->latest()
                ->take(8)
                ->get();
        });

        return ApiResponse::success($products, 'Featured products retrieved.');
    }

    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            return ApiResponse::error('Product not found.', null, 404);
        }

        $data = Cache::remember("products:detail:{$product->slug}", now()->addMinutes(30), function () use ($product) {
            return $product->load(['vendor', 'category', 'images']);
        });

        return ApiResponse::success($data, 'Product retrieved.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\ListProductsAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorProductController extends Controller
{
    public function index(Request $request, ListProductsAction $action): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,rating'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Deliberately no forced is_active=true here — a vendor manages
        // both their active and inactive listings from this screen.
        $products = $action->execute($request->user()->vendor->products(), $filters);

        return ApiResponse::success([
            'products' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ], 'Your products retrieved.');
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->execute($request->user(), $request->validated());

        return ApiResponse::success($product, 'Product created.', 201);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        return ApiResponse::success($product->load('images'), 'Product retrieved.');
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action): JsonResponse
    {
        $updated = $action->execute($product, $request->validated());

        return ApiResponse::success($updated, 'Product updated.');
    }

    public function destroy(Product $product, DeleteProductAction $action): JsonResponse
    {
        Gate::authorize('delete', $product);
        $action->execute($product);

        return ApiResponse::success(null, 'Product deleted.');
    }
}

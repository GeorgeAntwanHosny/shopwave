<?php

namespace App\Http\Controllers;

use App\Actions\Product\DeleteProductImageAction;
use App\Actions\Product\ReorderProductImagesAction;
use App\Actions\Product\UploadProductImagesAction;
use App\Http\Requests\Product\ReorderProductImagesRequest;
use App\Http\Requests\Product\UploadProductImagesRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class VendorProductImageController extends Controller
{
    public function store(UploadProductImagesRequest $request, Product $product, UploadProductImagesAction $action): JsonResponse
    {
        Gate::authorize('update', $product);
        $images = $action->execute($product, $request->file('images'));

        return ApiResponse::success($images, 'Images uploaded.', 201);
    }

    public function destroy(Product $product, ProductImage $image, DeleteProductImageAction $action): JsonResponse
    {
        Gate::authorize('update', $product);
        abort_if($image->product_id !== $product->id, 404);
        $action->execute($image);

        return ApiResponse::success(null, 'Image deleted.');
    }

    public function reorder(ReorderProductImagesRequest $request, Product $product, ReorderProductImagesAction $action): JsonResponse
    {
        Gate::authorize('update', $product);
        $action->execute($product, $request->validated()['image_ids']);

        return ApiResponse::success($product->load('images')->images, 'Images reordered.');
    }
}

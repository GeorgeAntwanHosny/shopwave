<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductImage;

class ReorderProductImagesAction
{
    public function execute(Product $product, array $orderedImageIds): void
    {
        foreach ($orderedImageIds as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }
    }
}

<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

class UploadProductImagesAction
{
    /**
     * @param  UploadedFile[]  $files
     */
    public function execute(Product $product, array $files): array
    {
        $startOrder = (int) $product->images()->max('sort_order') + 1;
        $images = [];

        foreach ($files as $i => $file) {
            $path = $file->store("products/{$product->id}", 'public');
            $images[] = $product->images()->create([
                'path' => $path,
                'sort_order' => $startOrder + $i,
            ]);
        }

        return $images;
    }
}

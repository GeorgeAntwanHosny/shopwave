<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    public function saved(Product $product): void
    {
        Cache::forget("products:detail:{$product->slug}");
        Cache::forget('products:featured');
    }

    public function deleted(Product $product): void
    {
        Cache::forget("products:detail:{$product->slug}");
        Cache::forget('products:featured');
    }

    /**
     * Clean up stored image files before the DB rows (and their FK
     * cascade) are gone.
     */
    public function deleting(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
    }
}

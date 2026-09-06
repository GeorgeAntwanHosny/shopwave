<?php

namespace App\Actions\Product;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class DeleteProductImageAction
{
    public function execute(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }
}

<?php

namespace App\Actions\Admin;

use App\Models\Product;
use App\Notifications\ProductDeactivatedNotification;

class DeactivateProductAction
{
    public function execute(Product $product, ?string $reason = null): Product
    {
        $product->update(['is_active' => false]);

        $product->vendor->notify(new ProductDeactivatedNotification($product, $reason));

        return $product->fresh();
    }
}

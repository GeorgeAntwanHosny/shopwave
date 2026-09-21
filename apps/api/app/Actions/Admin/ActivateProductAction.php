<?php

namespace App\Actions\Admin;

use App\Models\Product;
use App\Notifications\ProductReactivatedNotification;

class ActivateProductAction
{
    public function execute(Product $product, ?string $reason = null): Product
    {
        $product->update(['is_active' => true]);

        $product->vendor->notify(new ProductReactivatedNotification($product, $reason));

        return $product->fresh();
    }
}

<?php

namespace App\Actions\Admin;

use App\Models\Product;

class DeactivateProductAction
{
    public function execute(Product $product): Product
    {
        $product->update(['is_active' => false]);

        return $product->fresh();
    }
}

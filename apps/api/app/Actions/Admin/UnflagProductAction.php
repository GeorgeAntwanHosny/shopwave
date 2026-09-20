<?php

namespace App\Actions\Admin;

use App\Models\Product;

class UnflagProductAction
{
    public function execute(Product $product): Product
    {
        $product->update([
            'is_flagged' => false,
            'flagged_reason' => null,
            'flagged_at' => null,
        ]);

        return $product->fresh();
    }
}

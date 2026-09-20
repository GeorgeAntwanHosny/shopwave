<?php

namespace App\Actions\Admin;

use App\Models\Product;

class FlagProductAction
{
    public function execute(Product $product, string $reason): Product
    {
        $product->update([
            'is_flagged' => true,
            'flagged_reason' => $reason,
            'flagged_at' => now(),
        ]);

        return $product->fresh();
    }
}

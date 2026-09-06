<?php

namespace App\Actions\Product;

use App\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete(); // ProductObserver::deleting() removes stored image files first
    }
}

<?php

namespace App\Actions\Review;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductReviewsAction
{
    public function execute(Product $product): LengthAwarePaginator
    {
        return Review::where('product_id', $product->id)
            ->with(['user:id,name', 'reply'])
            ->latest()
            ->paginate(10);
    }
}

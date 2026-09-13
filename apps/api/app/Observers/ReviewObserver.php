<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;

class ReviewObserver
{

    public function saved(Review $review): void
    {
        $this->recalculateProduct($review->product_id);
        $this->recalculateVendor($review->vendor_id);
    }

    public function deleted(Review $review): void
    {
        $this->recalculateProduct($review->product_id);
        $this->recalculateVendor($review->vendor_id);
    }

    protected function recalculateProduct(int $productId): void
    {
        $stats = Review::where('product_id', $productId)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        Product::where('id', $productId)->update([
            'average_rating' => round($stats->avg_rating ?? 0, 2),
            'rating_count' => $stats->review_count ?? 0,
        ]);
    }

    protected function recalculateVendor(int $vendorId): void
    {
        $stats = Review::where('vendor_id', $vendorId)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        Vendor::where('id', $vendorId)->update([
            'average_rating' => round($stats->avg_rating ?? 0, 2),
            'rating_count' => $stats->review_count ?? 0,
        ]);
    }
}

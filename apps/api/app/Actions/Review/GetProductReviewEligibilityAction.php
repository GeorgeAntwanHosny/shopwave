<?php

namespace App\Actions\Review;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class GetProductReviewEligibilityAction
{
    /**
     * @return array{reviewable_items: array, my_reviews: array}
     */
    public function execute(Product $product, User $user): array
    {
        $reviewableItems = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('fulfillment_status', 'delivered');
            })
            ->whereDoesntHave('review')
            ->with('order:id,created_at')
            ->get()
            ->map(fn (OrderItem $item) => [
                'order_item_id' => $item->id,
                'order_id' => $item->order_id,
                'quantity' => $item->quantity,
                'purchased_at' => $item->order->created_at,
            ])
            ->values();

        $myReviews = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->get(['id', 'rating', 'comment', 'created_at']);

        return [
            'reviewable_items' => $reviewableItems,
            'my_reviews' => $myReviews,
        ];
    }
}

<?php

namespace App\Actions\Review;

use App\Events\NewReviewPosted;
use App\Models\OrderItem;
use App\Models\Review;

class CreateReviewAction
{
    public function execute(OrderItem $orderItem, array $data): Review
    {
        $review = Review::create([
            'order_item_id' => $orderItem->id,
            'user_id' => $orderItem->order->user_id,
            'product_id' => $orderItem->product_id,
            'vendor_id' => $orderItem->order->vendor_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        event(new NewReviewPosted($review));

        return $review;
    }
}

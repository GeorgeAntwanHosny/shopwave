<?php

namespace App\Actions\Review;

use App\Models\OrderItem;
use App\Models\Review;
use App\Notifications\NewReviewPostedNotification;

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

        $review->vendor->notify(new NewReviewPostedNotification($review));

        return $review;
    }
}

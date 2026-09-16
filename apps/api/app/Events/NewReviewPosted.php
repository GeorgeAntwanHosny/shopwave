<?php

namespace App\Events;

use App\Models\Review;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewReviewPosted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Review $review)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("vendor.{$this->review->vendor_id}")];
    }

    public function broadcastAs(): string
    {
        return 'NewReviewPosted';
    }

    public function broadcastWith(): array
    {
        return [
            'review_id' => $this->review->id,
            'product_name' => $this->review->product->name,
            'rating' => $this->review->rating,
        ];
    }
}

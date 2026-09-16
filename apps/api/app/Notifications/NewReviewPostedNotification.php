<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewReviewPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Review $review)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'id' => $this->id,
            'type' => 'NewReviewPosted',
            'message' => "New {$this->review->rating}★ review on {$this->review->product->name}.",
            'href' => '/vendor/products',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'NewReviewPosted';
    }
}

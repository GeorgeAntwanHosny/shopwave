<?php

namespace App\Notifications;

use App\Models\ReviewReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ReviewReplyPostedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ReviewReply $reply)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $review = $this->reply->review;

        return [
            'id' => $this->id,
            'type' => 'ReviewReplyPosted',
            'message' => "{$review->vendor->shop_name} replied to your review on {$review->product->name}.",
            'href' => "/products/{$review->product->slug}",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'ReviewReplyPosted';
    }
}

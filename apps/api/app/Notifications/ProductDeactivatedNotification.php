<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProductDeactivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product, public ?string $reason = null)
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
            'type' => 'ProductDeactivated',
            'message' => $this->reason
                ? "\"{$this->product->name}\" was deactivated by an admin: {$this->reason}"
                : "\"{$this->product->name}\" was deactivated by an admin.",
            'href' => "/vendor/products/{$this->product->id}/edit",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'ProductDeactivated';
    }
}

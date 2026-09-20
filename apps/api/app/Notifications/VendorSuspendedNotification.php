<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class VendorSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Vendor $vendor, public ?string $reason)
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
            'type' => 'VendorSuspended',
            'message' => $this->reason ? "Your shop has been suspended: {$this->reason}" : 'Your shop has been suspended.',
            'href' => '/dashboard',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'VendorSuspended';
    }
}

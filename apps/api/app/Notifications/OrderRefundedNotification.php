<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public bool $transferWasReversed = false,
        public ?string $reason = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $reasonSuffix = $this->reason ? " Reason: {$this->reason}" : '';

        if ($notifiable instanceof Vendor) {
            $message = $this->transferWasReversed
                ? "Order #{$this->order->id} was refunded — \${$this->order->vendor_payout_amount} was reversed from your payout.{$reasonSuffix}"
                : "Order #{$this->order->id} was refunded before payout — no funds were transferred to you for it.{$reasonSuffix}";

            return [
                'id' => $this->id,
                'type' => 'OrderRefunded',
                'message' => $message,
                'href' => "/vendor/orders/{$this->order->id}",
            ];
        }

        return [
            'id' => $this->id,
            'type' => 'OrderRefunded',
            'message' => "Your order #{$this->order->id} has been refunded (\${$this->order->total}).{$reasonSuffix}",
            'href' => "/orders/{$this->order->id}",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastType(): string
    {
        return 'OrderRefunded';
    }
}

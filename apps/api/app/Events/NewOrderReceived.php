<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewOrderReceived implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("vendor.{$this->order->vendor_id}")];
    }

    public function broadcastAs(): string
    {
        return 'NewOrderReceived';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'customer_name' => $this->order->user->name,
            'total' => (string) $this->order->total,
        ];
    }
}

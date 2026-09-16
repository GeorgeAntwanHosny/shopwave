<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderStatusChanged implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->order->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusChanged';
    }

    public function broadcastWith(): array
    {
        Log::info('Broadcasting OrderStatusChanged event', [
            'order_id' => $this->order->id,
            'fulfillment_status' => $this->order->fulfillment_status,
        ]);
        return [
            'order_id' => $this->order->id,
            'fulfillment_status' => $this->order->fulfillment_status,
        ];
    }
}

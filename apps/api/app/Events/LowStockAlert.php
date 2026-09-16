<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LowStockAlert implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Product $product)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("vendor.{$this->product->vendor_id}")];
    }

    public function broadcastAs(): string
    {
        return 'LowStockAlert';
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock_quantity' => $this->product->stock_quantity,
        ];
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TestBroadcastEvent implements ShouldBroadcastNow
{
    public function broadcastOn(): Channel
    {
        return new Channel('test');
    }

    public function broadcastAs(): string
    {
        return 'TestEvent';
    }

    public function broadcastWith(): array
    {
        return ['message' => 'hello', 'sent_at' => now()->toISOString()];
    }
}

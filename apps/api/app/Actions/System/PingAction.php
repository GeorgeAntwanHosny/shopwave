<?php

namespace App\Actions\System;

class PingAction
{
    /**
     * Return a minimal payload proving the API is reachable and alive.
     *
     * @return array{status: string, service: string, timestamp: string}
     */
    public function execute(): array
    {
        return [
            'status' => 'ok',
            'service' => 'shopwave-api',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The frontend authenticates via Sanctum bearer tokens, not cookie
        // sessions — the default 'web' guard on the broadcasting auth route
        // would reject every request, so it's explicitly auth:sanctum here,
        // consistent with every other endpoint in this API.
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        require base_path('routes/channels.php');
    }
}

<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsVendor
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->vendor) {
            return ApiResponse::error('This action requires a vendor account.', null, 403);
        }

        return $next($request);
    }
}

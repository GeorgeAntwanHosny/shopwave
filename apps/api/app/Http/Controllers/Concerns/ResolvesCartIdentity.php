<?php

namespace App\Http\Controllers\Concerns;

use App\Services\CartService;
use Illuminate\Http\Request;

trait ResolvesCartIdentity
{
    protected function resolveCart(Request $request, CartService $cartService): array
    {
        // $request->user('sanctum') resolves the user if a valid bearer
        // token is present, and simply returns null otherwise — unlike the
        // auth:sanctum middleware, it never aborts, which is exactly what
        // a guest-accessible cart needs.
        return $cartService->resolveCartKey($request->user('sanctum'), $request->header('X-Cart-Token'));
    }
}

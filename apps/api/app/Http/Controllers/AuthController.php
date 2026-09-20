<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action, CartService $cartService): JsonResponse
    {
        $result = $action->execute($request->validated());
        $this->mergeGuestCartIfPresent($request, $result['user'], $cartService);

        return ApiResponse::success($result, 'Registered successfully.', 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action, CartService $cartService): JsonResponse
    {
        $result = $action->execute($request->validated());
        $this->mergeGuestCartIfPresent($request, $result['user'], $cartService);

        return ApiResponse::success($result, 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('vendor');

        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'vendor' => $user->vendor,
                'is_admin' => $user->hasRole('admin'),
            ],
        ], 'Profile retrieved successfully.');
    }

    protected function mergeGuestCartIfPresent(Request $request, User $user, CartService $cartService): void
    {
        $guestToken = $request->header('X-Cart-Token');

        if ($guestToken) {
            $identity = $cartService->resolveCartKey(null, $guestToken);
            $cartService->mergeGuestIntoUser($identity['key'], "cart:user:{$user->id}");
        }
    }
}

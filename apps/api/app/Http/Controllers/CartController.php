<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCartIdentity;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\ApplyCouponRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesCartIdentity;

    public function show(Request $request, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cart = $cartService->getCart($identity['key']);

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Cart retrieved.');
    }

    public function storeItem(AddCartItemRequest $request, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cartService->addItem($identity['key'], (int) $request->validated('product_id'), (int) $request->validated('quantity'));
        $cart = $cartService->getCart($identity['key']);

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Item added to cart.');
    }

    public function updateItem(UpdateCartItemRequest $request, Product $product, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cartService->setItemQuantity($identity['key'], $product->id, (int) $request->validated('quantity'));
        $cart = $cartService->getCart($identity['key']);

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Cart updated.');
    }

    public function destroyItem(Request $request, Product $product, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cartService->removeItem($identity['key'], $product->id);
        $cart = $cartService->getCart($identity['key']);

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Item removed.');
    }

    public function applyCoupon(ApplyCouponRequest $request, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cart = $cartService->applyCoupon($identity['key'], $request->validated('code'));

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Coupon applied.');
    }

    public function destroyCoupon(Request $request, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $cartService->removeCoupon($identity['key']);
        $cart = $cartService->getCart($identity['key']);

        return ApiResponse::success(array_merge($cart, ['cart_token' => $identity['guest_token']]), 'Coupon removed.');
    }
}

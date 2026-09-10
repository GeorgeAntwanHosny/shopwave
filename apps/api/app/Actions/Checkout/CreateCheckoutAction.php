<?php

namespace App\Actions\Checkout;

use App\Models\CheckoutSession;
use App\Models\User;
use App\Services\CartService;
use App\Services\StripePaymentService;
use Illuminate\Validation\ValidationException;

class CreateCheckoutAction
{
    public function __construct(
        protected CartService $cartService,
        protected StripePaymentService $stripePaymentService,
    ) {
    }

    /**
     * @return array{client_secret: string, payment_intent_id: string}
     */
    public function execute(User $user, string $cartKey): array
    {
        $cart = $this->cartService->getCart($cartKey);

        if (empty($cart['vendors'])) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        if (! empty($cart['unavailable_items'])) {
            throw ValidationException::withMessages(['cart' => ['Some items in your cart are no longer available. Please remove them before checking out.']]);
        }

        foreach ($cart['vendors'] as $group) {
            foreach ($group['items'] as $item) {
                if ($item['stock_limited']) {
                    throw ValidationException::withMessages(['cart' => ["Only {$item['available_stock']} of \"{$item['name']}\" left in stock. Please update your cart."]]);
                }
            }
        }

        $amountCents = (int) round(((float) $cart['grand_total']) * 100);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages(['cart' => ['Your cart total must be greater than zero.']]);
        }

        $paymentIntent = $this->stripePaymentService->createPaymentIntent(
            $amountCents,
            'usd',
            ['user_id' => (string) $user->id]
        );

        CheckoutSession::create([
            'user_id' => $user->id,
            'stripe_payment_intent_id' => $paymentIntent->id,
            'cart_snapshot' => $cart,
            'status' => 'pending',
        ]);

        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
    }
}

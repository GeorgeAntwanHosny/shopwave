<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    /** A cart untouched this long is treated as abandoned. */
    protected const TTL_SECONDS = 60 * 60 * 24 * 7;

    /**
     * Resolve which Redis cart this request belongs to. Authenticated
     * requests always use the durable per-user key; anonymous requests use
     * a client-supplied guest token (a UUID the frontend stores and sends
     * back via X-Cart-Token), generating a fresh one if none was supplied.
     *
     * @return array{key: string, guest_token: ?string, is_new_guest_token: bool}
     */
    public function resolveCartKey(?User $user, ?string $guestToken): array
    {
        if ($user) {
            return ['key' => "cart:user:{$user->id}", 'guest_token' => null, 'is_new_guest_token' => false];
        }

        // Sanitize to a UUID-safe charset and cap length — a guest token is
        // client-supplied and becomes part of a Redis key.
        $guestToken = $guestToken ? substr(preg_replace('/[^a-zA-Z0-9\-]/', '', $guestToken), 0, 100) : null;

        if ($guestToken) {
            return ['key' => "cart:guest:{$guestToken}", 'guest_token' => $guestToken, 'is_new_guest_token' => false];
        }

        $newToken = (string) Str::uuid();

        return ['key' => "cart:guest:{$newToken}", 'guest_token' => $newToken, 'is_new_guest_token' => true];
    }

    /**
     * Add to (or increase) a product's quantity in the cart. Uses HINCRBY,
     * Redis's atomic increment primitive for hash fields — the same class
     * of atomic operation as SETNX, applied to a quantity counter instead
     * of an existence flag — so concurrent add-to-cart requests for the
     * same product can never race or clobber each other.
     */
    public function addItem(string $cartKey, int $productId, int $quantity): void
    {
        $product = Product::where('id', $productId)->where('is_active', true)->first();

        if (! $product) {
            throw ValidationException::withMessages(['product_id' => ['This product is not available.']]);
        }

        if ($product->vendor->is_suspended) {
            throw ValidationException::withMessages(['product_id' => ['This item is currently unavailable.']]);
        }

        $newQuantity = Redis::hincrby($cartKey, (string) $productId, $quantity);

        if ($newQuantity > $product->stock_quantity) {
            Redis::hincrby($cartKey, (string) $productId, -$quantity);
            throw ValidationException::withMessages([
                'quantity' => ["Only {$product->stock_quantity} left in stock."],
            ]);
        }

        $this->refreshTtl($cartKey);
    }

    /**
     * Set a product's quantity to an exact value (e.g. a cart-page quantity
     * stepper), removing it entirely if set to 0 or below.
     */
    public function setItemQuantity(string $cartKey, int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartKey, $productId);
            return;
        }

        $product = Product::where('id', $productId)->where('is_active', true)->first();

        if (! $product) {
            throw ValidationException::withMessages(['product_id' => ['This product is not available.']]);
        }

        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$product->stock_quantity} left in stock."],
            ]);
        }

        Redis::hset($cartKey, (string) $productId, $quantity);
        $this->refreshTtl($cartKey);
    }

    public function removeItem(string $cartKey, int $productId): void
    {
        Redis::hdel($cartKey, (string) $productId);
    }

    /**
     * Hydrate the raw Redis hash into a full cart payload: items grouped
     * by vendor (Phase 5 checkout splits by vendor, so the cart is shaped
     * that way from the start), live product data (price/stock/name are
     * never trusted from the cart itself), stock-limited warnings, and the
     * applied coupon's effect on its own vendor's subtotal only.
     */
    public function getCart(string $cartKey): array
    {
        $raw = Redis::hgetall($cartKey); // ['productId' => 'quantity', ...]

        if (empty($raw)) {
            return $this->emptyCartPayload();
        }

        $productIds = array_map('intval', array_keys($raw));
        $products = Product::whereIn('id', $productIds)->with(['vendor', 'images'])->get()->keyBy('id');

        $vendorGroups = [];
        $unavailableItems = [];

        foreach ($raw as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;
            $product = $products->get($productId);

            if (! $product || ! $product->is_active) {
                $unavailableItems[] = ['product_id' => $productId, 'reason' => 'no_longer_available'];
                continue;
            }

            $stockLimited = $quantity > $product->stock_quantity;
            $effectiveQuantity = $stockLimited ? $product->stock_quantity : $quantity;
            $subtotal = bcmul((string) $product->price, (string) $effectiveQuantity, 2);

            $vendorId = $product->vendor_id;
            $vendorGroups[$vendorId] ??= [
                'vendor_id' => $vendorId,
                'shop_name' => $product->vendor->shop_name,
                'items' => [],
                'subtotal' => '0.00',
            ];

            $vendorGroups[$vendorId]['items'][] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => (string) $product->price,
                'quantity' => $quantity,
                'effective_quantity' => $effectiveQuantity,
                'stock_limited' => $stockLimited,
                'available_stock' => $product->stock_quantity,
                'image' => optional($product->images->first())->url,
                'subtotal' => $subtotal,
            ];

            $vendorGroups[$vendorId]['subtotal'] = bcadd($vendorGroups[$vendorId]['subtotal'], $subtotal, 2);
        }

        $appliedCoupon = $this->getAppliedCoupon($cartKey);
        $grandTotal = '0.00';
        $itemCount = 0;

        foreach ($vendorGroups as &$group) {
            $group['coupon'] = null;
            $group['total_after_discount'] = $group['subtotal'];

            if ($appliedCoupon && $appliedCoupon->vendor_id === $group['vendor_id']) {
                $discount = $this->calculateDiscount($appliedCoupon, $group['subtotal']);
                $group['coupon'] = [
                    'id' => $appliedCoupon->id,
                    'code' => $appliedCoupon->code,
                    'type' => $appliedCoupon->type,
                    'value' => (string) $appliedCoupon->value,
                    'discount_amount' => $discount,
                ];
                $group['total_after_discount'] = bcsub($group['subtotal'], $discount, 2);
            }

            $grandTotal = bcadd($grandTotal, $group['total_after_discount'], 2);
            $itemCount += array_sum(array_column($group['items'], 'quantity'));
        }
        unset($group);

        return [
            'vendors' => array_values($vendorGroups),
            'unavailable_items' => $unavailableItems,
            'grand_total' => $grandTotal,
            'item_count' => $itemCount,
        ];
    }

    protected function emptyCartPayload(): array
    {
        return ['vendors' => [], 'unavailable_items' => [], 'grand_total' => '0.00', 'item_count' => 0];
    }

    /**
     * Apply a coupon to the cart. A coupon only ever discounts its own
     * vendor's line items within a multi-vendor cart — min_order_amount is
     * checked against that vendor's subtotal specifically, not the whole
     * cart's total.
     */
    public function applyCoupon(string $cartKey, string $code): array
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower($code)])->first();

        if (! $coupon || ! $coupon->isCurrentlyValid()) {
            throw ValidationException::withMessages(['code' => ['This coupon is invalid or has expired.']]);
        }

        $cart = $this->getCart($cartKey);
        $vendorGroup = collect($cart['vendors'])->firstWhere('vendor_id', $coupon->vendor_id);

        if (! $vendorGroup) {
            throw ValidationException::withMessages(['code' => ['This coupon does not apply to any items in your cart.']]);
        }

        if ($coupon->min_order_amount && bccomp($vendorGroup['subtotal'], (string) $coupon->min_order_amount, 2) < 0) {
            throw ValidationException::withMessages([
                'code' => ["This coupon requires a minimum order of \${$coupon->min_order_amount} from this vendor."],
            ]);
        }

        Redis::setex("{$cartKey}:coupon", self::TTL_SECONDS, (string) $coupon->id);

        return $this->getCart($cartKey);
    }

    public function removeCoupon(string $cartKey): void
    {
        Redis::del("{$cartKey}:coupon");
    }

    protected function getAppliedCoupon(string $cartKey): ?Coupon
    {
        $id = Redis::get("{$cartKey}:coupon");

        if (! $id) {
            return null;
        }

        $coupon = Coupon::find($id);

        // If the coupon was deleted/deactivated after being applied, treat
        // it as silently no longer in effect rather than erroring the cart.
        return $coupon && $coupon->isCurrentlyValid() ? $coupon : null;
    }

    protected function calculateDiscount(Coupon $coupon, string $subtotal): string
    {
        if ($coupon->type === 'percentage') {
            return bcdiv(bcmul($subtotal, (string) $coupon->value, 4), '100', 2);
        }

        $discount = (string) $coupon->value;

        return bccomp($discount, $subtotal, 2) > 0 ? $subtotal : $discount;
    }

    /**
     * Atomically fold a guest cart's items into the user's cart on
     * login/register, then discard the guest cart. Quantities combine
     * (HINCRBY) rather than overwrite, so a returning user's existing
     * saved cart isn't clobbered by what they added this session as a
     * guest. The guest's applied coupon is deliberately NOT carried over —
     * coupons are re-validated per cart, and silently forwarding one could
     * apply a discount the user never re-confirmed post-login.
     */
    public function mergeGuestIntoUser(string $guestKey, string $userKey): void
    {
        $guestItems = Redis::hgetall($guestKey);

        if (empty($guestItems)) {
            return;
        }

        Redis::pipeline(function ($pipe) use ($guestItems, $userKey, $guestKey) {
            foreach ($guestItems as $productId => $quantity) {
                $pipe->hincrby($userKey, $productId, (int) $quantity);
            }
            $pipe->del($guestKey);
            $pipe->del("{$guestKey}:coupon");
        });

        $this->refreshTtl($userKey);
    }

    protected function refreshTtl(string $cartKey): void
    {
        Redis::expire($cartKey, self::TTL_SECONDS);
    }

    /**
     * Empties a cart entirely — used after a successful checkout, once its
     * contents have been converted into real Order rows.
     */
    public function clear(string $cartKey): void
    {
        Redis::del($cartKey);
        Redis::del("{$cartKey}:coupon");
    }
}

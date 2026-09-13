<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'vendor_id', 'checkout_session_id', 'coupon_id', 'coupon_code',
        'subtotal', 'discount_amount', 'platform_fee_amount', 'vendor_payout_amount',
        'total', 'status', 'fulfillment_status', 'tracking_number', 'carrier',
        'stripe_payment_intent_id', 'stripe_transfer_id', 'transferred_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'vendor_payout_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'transferred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function checkoutSession(): BelongsTo
    {
        return $this->belongsTo(CheckoutSession::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

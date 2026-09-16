<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Vendor extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_slug',
        'stripe_account_id',
        'stripe_onboarding_complete',
        'average_rating',
        'rating_count',
    ];

    protected $casts = [
        'stripe_onboarding_complete' => 'boolean',
        'average_rating' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'vendor.'.$this->id;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checkout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('platform_fee_amount', 10, 2);
            $table->decimal('vendor_payout_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['paid', 'refunded'])->default('paid');
            $table->string('stripe_payment_intent_id');
            $table->string('stripe_transfer_id')->nullable();
            // Null until the per-vendor Transfer actually succeeds — the
            // charge and the transfer are two separate Stripe calls, so an
            // order can legitimately be "paid" but not yet (or never)
            // transferred if the Transfer call fails. See Known Issues.
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('vendor_id');
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

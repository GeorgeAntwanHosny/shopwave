<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_refund_id')->nullable()->after('stripe_transfer_id');
            $table->timestamp('refunded_at')->nullable()->after('transferred_at');
            $table->string('refund_reason')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_refund_id', 'refunded_at', 'refund_reason']);
        });
    }
};

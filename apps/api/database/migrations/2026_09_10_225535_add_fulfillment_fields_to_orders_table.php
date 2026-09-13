<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deliberately separate from `orders.status` (payment lifecycle:
     * paid/refunded) — fulfillment is a different dimension and shouldn't
     * overload a column that Phase 5's webhook/checkout code already relies
     * on meaning "was this order paid."
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('fulfillment_status', ['processing', 'shipped', 'delivered'])
                ->default('processing')
                ->after('status');
            $table->string('tracking_number')->nullable()->after('fulfillment_status');
            $table->string('carrier')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_status', 'tracking_number', 'carrier']);
        });
    }
};

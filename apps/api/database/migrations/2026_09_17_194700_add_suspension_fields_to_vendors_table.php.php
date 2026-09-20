<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('stripe_onboarding_complete');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['is_suspended', 'suspended_at', 'suspension_reason']);
        });
    }
};

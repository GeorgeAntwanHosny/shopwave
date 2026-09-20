<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('is_active');
            $table->string('flagged_reason')->nullable()->after('is_flagged');
            $table->timestamp('flagged_at')->nullable()->after('flagged_reason');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_flagged', 'flagged_reason', 'flagged_at']);
        });
    }
};

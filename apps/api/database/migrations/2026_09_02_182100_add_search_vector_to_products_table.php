<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PostgreSQL full-text search: a generated tsvector column over
     * name + description, backed by a GIN index for fast lookups.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE products
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('english', coalesce(name, '') || ' ' || coalesce(description, ''))
            ) STORED
        ");

        DB::statement('CREATE INDEX products_search_vector_index ON products USING GIN (search_vector)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_search_vector_index');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('search_vector');
        });
    }
};

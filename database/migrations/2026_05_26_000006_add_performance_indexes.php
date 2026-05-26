<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure orders has composite index on (user_id, created_at) for analytics queries
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'orders_user_id_created_at_idx');
        });

        // Ensure inventories has index on variant_id for fast lock lookups
        Schema::table('inventories', function (Blueprint $table) {
            $table->index('variant_id', 'inventories_variant_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_created_at_idx');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex('inventories_variant_id_idx');
        });
    }
};

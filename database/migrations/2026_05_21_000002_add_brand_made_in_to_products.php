<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')
                  ->constrained('brands')->nullOnDelete();
            $table->string('made_in', 50)->nullable()->after('brand_id');
            $table->json('compatibility')->nullable()->after('made_in');

            $table->index('brand_id');
            $table->index('made_in');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['made_in', 'compatibility']);
        });
    }
};

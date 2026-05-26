<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_spending', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->enum('tier', ['bronze', 'silver', 'gold'])->default('bronze');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['user_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_spending');
    }
};

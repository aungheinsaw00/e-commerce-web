<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country', 50)->nullable();
            $table->string('tier', 30)->nullable(); // OEM, Tier1, Performance, Fluids, Tyres
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

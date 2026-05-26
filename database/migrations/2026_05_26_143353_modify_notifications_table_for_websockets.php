<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'metadata') && !Schema::hasColumn('notifications', 'data')) {
                $table->renameColumn('metadata', 'data');
            }
            
            $indexes = Schema::getIndexes('notifications');
            $hasIndex = false;
            foreach ($indexes as $index) {
                if (in_array('created_at', $index['columns'])) {
                    $hasIndex = true;
                    break;
                }
            }
            
            if (!$hasIndex) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->renameColumn('data', 'metadata');
        });
    }
};

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
        Schema::table('woundmed_usage_log', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['graft_size_id']);
            
            // Modify the column to be nullable
            $table->unsignedBigInteger('graft_size_id')->nullable()->change();
            
            // Re-add the foreign key with SET NULL
            $table->foreign('graft_size_id')
                  ->references('graft_size_id')
                  ->on('woundmed_graft_sizes')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_usage_log', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['graft_size_id']);
            
            // Make the column NOT nullable again
            $table->unsignedBigInteger('graft_size_id')->nullable(false)->change();
            
            // Re-add the original foreign key with CASCADE
            $table->foreign('graft_size_id')
                  ->references('graft_size_id')
                  ->on('woundmed_graft_sizes')
                  ->onDelete('cascade');
        });
    }
};

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
        Schema::table('woundmed_returns', function (Blueprint $table) {
            // Add entry_type column: 'manual' or 'upload'
            $table->enum('entry_type', ['manual', 'upload'])
                  ->default('manual')
                  ->after('graft_log_id')
                  ->comment('Type of entry: manual or file upload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->dropColumn('entry_type');
        });
    }
};

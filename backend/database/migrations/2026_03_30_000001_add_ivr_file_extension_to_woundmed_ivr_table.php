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
        if (!Schema::hasColumn('woundmed_ivr', 'ivr_file_extension')) {
            Schema::table('woundmed_ivr', function (Blueprint $table) {
                $table->string('ivr_file_extension', 10)->nullable()->after('ivr_file');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('woundmed_ivr', 'ivr_file_extension')) {
            Schema::table('woundmed_ivr', function (Blueprint $table) {
                $table->dropColumn('ivr_file_extension');
            });
        }
    }
};

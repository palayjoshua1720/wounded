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
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            $table->tinyInteger('status')
                  ->default(0)
                  ->comment('0 = success, 1 = failed')
                  ->after('entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

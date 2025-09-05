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
            $table->char('prev_hash', 64)->nullable()->after('status')
                  ->comment('Hash of the previous log entry for chain integrity');
            $table->char('row_hash', 64)->after('prev_hash')
                  ->comment('Hash of this row including prev_hash for tamper evidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['prev_hash', 'row_hash']);
        });
    }
};

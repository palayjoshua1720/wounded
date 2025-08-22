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
            $table->string('attempted_identifier', 255)
                  ->nullable()
                  ->after('user_id')
                  ->comment('Stores attempted email/username if user_id not found');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            $table->dropColumn('attempted_identifier');
        });
    }
};

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
            $table->string('entity_type')
                  ->nullable()
                  ->after('entity')
                  ->comment('Type of entity affected: clinics, users, manufacturers, authentication, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            $table->dropColumn('entity_type');
        });
    }
};

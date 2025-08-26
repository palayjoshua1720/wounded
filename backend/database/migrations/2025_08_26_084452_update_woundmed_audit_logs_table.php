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
            // Rename "action" column to "action_message"
            $table->renameColumn('action', 'action_message');

            // Add new "action_type" column after "action_message"
            $table->string('action_type')->nullable()
            ->after('ip_address')
                ->comment('Short identifier of the action, e.g., login, logout, add_clinician, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            // Revert the changes
            $table->renameColumn('action_message', 'action');
            $table->dropColumn('action_type');
        });
    }
};

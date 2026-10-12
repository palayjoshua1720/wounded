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
        Schema::table('woundmed_patient_info', function (Blueprint $table) {
            // Nullable FK to the assigned clinician (woundmed_users.id, user_role = 3).
            // Only clinic users (role 2) populate this value via the UI.
            $table->unsignedBigInteger('assigned_clinician_id')->nullable()->after('clinic_id');

            $table->foreign('assigned_clinician_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('set null');

            $table->index('assigned_clinician_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_patient_info', function (Blueprint $table) {
            $table->dropForeign(['assigned_clinician_id']);
            $table->dropIndex(['assigned_clinician_id']);
            $table->dropColumn('assigned_clinician_id');
        });
    }
};

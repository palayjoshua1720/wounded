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
            // Add nullable clinic_id column (since not all patients may belong to a clinic)
            $table->unsignedBigInteger('clinic_id')->nullable()->after('user_id');

            // Add foreign key constraint (assuming your clinic table is named 'woundmed_clinics')
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('set null'); // If clinic is deleted, keep patient but nullify the link
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_patient_info', function (Blueprint $table) {
            // Drop foreign key and column on rollback
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};

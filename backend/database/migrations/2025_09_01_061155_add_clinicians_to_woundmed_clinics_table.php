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
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->json('assigned_clinician_ids')->nullable()->after('clinic_public_id')
                ->comment('List of assigned clinician IDs in JSON format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->dropColumn('clinician_ids');
        });
    }
};

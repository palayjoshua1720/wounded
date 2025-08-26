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
            // Custom system-generated clinic code
            $table->string('clinic_code')->unique()->after('clinic_id')
                ->comment('System-generated unique clinic code, e.g., CL-20250825-0001');

            // Official public clinic ID (if applicable, can be nullable)
            $table->string('clinic_public_id')->nullable()->unique()->after('clinic_code')
                ->comment('Official or public identifier provided to the clinic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->dropColumn(['clinic_code', 'clinic_public_id']);
        });
    }
};

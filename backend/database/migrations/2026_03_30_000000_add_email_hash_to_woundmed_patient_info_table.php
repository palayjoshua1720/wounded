<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add email_hash column for HIPAA-compliant blind-index lookups.
     * The EncryptsData trait requires this column for searchable encrypted email fields.
     */
    public function up(): void
    {
        // Add email_hash to woundmed_patient_info (if not already exists)
        if (!Schema::hasColumn('woundmed_patient_info', 'email_hash')) {
            Schema::table('woundmed_patient_info', function (Blueprint $table) {
                $table->string('email_hash', 64)->nullable()->after('email')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('woundmed_patient_info', 'email_hash')) {
            Schema::table('woundmed_patient_info', function (Blueprint $table) {
                $table->dropIndex(['email_hash']);
                $table->dropColumn('email_hash');
            });
        }
    }
};

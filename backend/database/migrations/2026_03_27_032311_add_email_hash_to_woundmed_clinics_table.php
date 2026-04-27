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
     * This column stores an HMAC-SHA256 hash of the email for searchable queries
     * without exposing the actual email address.
     */
    public function up(): void
    {
        // Add email_hash to woundmed_clinics (if not already exists)
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('woundmed_clinics', 'email_hash')) {
                $table->string('email_hash', 64)->nullable()->after('email')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            if (Schema::hasColumn('woundmed_clinics', 'email_hash')) {
                $table->dropColumn('email_hash');
            }
        });
    }
};

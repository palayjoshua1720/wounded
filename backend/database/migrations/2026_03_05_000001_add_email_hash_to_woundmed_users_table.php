<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a deterministic HMAC-SHA256 hash of the email for fast, indexed lookups.
     * The hash is keyed with HMAC_HASH_KEY (from .env) so it cannot be reversed
     * or brute-forced without that key — HIPAA-compliant blind index pattern.
     */
    public function up(): void
    {
        Schema::table('woundmed_users', function (Blueprint $table) {
            // 64 hex chars (SHA-256 output) - unique so duplicate emails are still rejected
            $table->string('email_hash', 64)->nullable()->unique()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->dropUnique(['email_hash']);
            $table->dropColumn('email_hash');
        });
    }
};

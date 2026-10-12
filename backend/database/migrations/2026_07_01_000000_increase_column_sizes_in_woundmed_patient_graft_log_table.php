<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Increases column sizes for woundmed_patient_graft_log and
     * woundmed_inventory_ledger tables to accommodate encrypted data.
     * Encrypted data (base64 JSON ciphertext) is significantly larger
     * than plain text (typically 180-350+ characters).
     */
    public function up(): void
    {
        Schema::table('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->string('serial_number', 500)->change();
            $table->string('location', 500)->nullable()->change();
            $table->string('wound_site', 500)->change();
        });

        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->string('serial_number', 500)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->string('serial_number', 255)->change();
            $table->string('location', 255)->nullable()->change();
            $table->string('wound_site', 255)->change();
        });

        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->string('serial_number', 255)->change();
        });
    }
};

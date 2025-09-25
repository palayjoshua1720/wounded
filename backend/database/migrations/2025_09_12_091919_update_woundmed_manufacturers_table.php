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
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            // Rename columns
            $table->renameColumn('email', 'primary_email');
            $table->renameColumn('phone', 'contact_number');

            // Add new columns
            $table->string('secondary_email')->nullable()->after('primary_email');
            $table->string('website')->nullable()->after('secondary_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            // Revert column renames
            $table->renameColumn('primary_email', 'email');
            $table->renameColumn('contact_number', 'phone');

            // Drop added columns
            $table->dropColumn(['secondary_email', 'website']);
        });
    }
};

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
        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->unsignedBigInteger('clinic_id')->nullable()->change();
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            // First set any null clinic_ids to a valid clinic or default
            // Note: rollback may fail if null values exist
            $table->dropForeign(['clinic_id']);
            $table->unsignedBigInteger('clinic_id')->nullable(false)->change();
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('cascade');
        });
    }
};

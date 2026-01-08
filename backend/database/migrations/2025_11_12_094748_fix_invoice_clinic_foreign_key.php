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
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['clinic_id']);
            
            // Add the correct foreign key constraint
            $table->foreign('clinic_id')
                  ->references('clinic_id')
                  ->on('woundmed_clinics')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            // Drop the correct foreign key constraint
            $table->dropForeign(['clinic_id']);
            
            // Add the original foreign key constraint (pointing to the wrong table)
            $table->foreign('clinic_id')
                  ->references('id')
                  ->on('clinics')
                  ->onDelete('cascade');
        });
    }
};
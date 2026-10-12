<?php

// ============================================================================
// PATIENT GRAFT LOG MODULE - MIGRATION
// ----------------------------------------------------------------------------
// This migration creates the standalone table for the Patient Graft Log
// feature. To remove this module, rollback this migration and delete every
// file marked with "PATIENT GRAFT LOG MODULE".
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->id('graft_log_id');

            // Patient (required)
            $table->unsignedBigInteger('patient_id');

            // Inventory ledger row (the consumed serial)
            $table->unsignedBigInteger('ledger_id')->nullable();

            // Denormalized snapshot fields (resilient if ledger is later removed)
            $table->string('serial_number');
            $table->unsignedBigInteger('graft_size_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();

            // Clinical fields
            $table->date('date_of_service');
            $table->string('location')->nullable();
            $table->string('wound_site');
            $table->unsignedSmallInteger('wound_number')->nullable();
            $table->unsignedSmallInteger('week_number')->nullable();
            $table->unsignedBigInteger('clinician_id');
            $table->text('notes')->nullable();

            // Auditing
            $table->unsignedBigInteger('logged_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('woundmed_patient_info')
                ->onDelete('cascade');

            $table->foreign('ledger_id')
                ->references('ledger_id')
                ->on('woundmed_inventory_ledger')
                ->onDelete('set null');

            $table->foreign('graft_size_id')
                ->references('graft_size_id')
                ->on('woundmed_graft_sizes')
                ->onDelete('set null');

            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('woundmed_brands')
                ->onDelete('set null');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('set null');

            $table->foreign('invoice_id')
                ->references('id')
                ->on('woundmed_invoices')
                ->onDelete('set null');

            $table->foreign('clinician_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('restrict');

            $table->foreign('logged_by')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('set null');

            // Indexes
            $table->index('patient_id');
            $table->index('ledger_id');
            $table->index('date_of_service');
            $table->index('clinician_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woundmed_patient_graft_log');
    }
};

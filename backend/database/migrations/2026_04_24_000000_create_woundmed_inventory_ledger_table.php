<?php

// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - MIGRATION
// ----------------------------------------------------------------------------
// This migration creates the standalone table for the Inventory Ledger
// Management feature. To remove this module, rollback this migration and
// delete all files marked with "INVENTORY LEDGER MANAGEMENT MODULE".
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->id('ledger_id');
            $table->string('serial_number')->unique();
            $table->enum('product_type', ['graft', 'other_product'])->default('graft');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->tinyInteger('status')->default(0)->comment('0=Expected, 1=Delivered, 2=Used, 3=Partially Used, 4=Reassigned, 5=Unused, 6=Expired');
            $table->boolean('is_used')->default(false);
            $table->string('graft_usage_id')->nullable()->comment('Static field for future graft log connection');
            $table->enum('invoice_status', ['unpaid', 'paid'])->default('unpaid');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('woundmed_brands')
                ->onDelete('set null');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('cascade');

            $table->foreign('invoice_id')
                ->references('id')
                ->on('woundmed_invoices')
                ->onDelete('set null');

            // Indexes for performance
            $table->index('serial_number');
            $table->index('product_type');
            $table->index('status');
            $table->index('is_used');
            $table->index('invoice_status');
            $table->index('clinic_id');
            $table->index('brand_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woundmed_inventory_ledger');
    }
};

<?php

// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - MIGRATION
// ----------------------------------------------------------------------------
// Adds order_id column to woundmed_inventory_ledger table.
// To remove this module, rollback this migration.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('clinic_id');
            $table->foreign('order_id')
                ->references('order_id')
                ->on('woundmed_orders')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('woundmed_inventory_ledger', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};

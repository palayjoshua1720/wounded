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
        Schema::table('woundmed_orders', function (Blueprint $table) {
            // Add manufacturer_id after ivr_id
            $table->unsignedBigInteger('manufacturer_id')->nullable()->after('ivr_id');

            // Add FK
            $table->foreign('manufacturer_id')
                ->references('manufacturer_id')
                ->on('woundmed_manufacturers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_orders', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign(['manufacturer_id']);
            $table->dropColumn('manufacturer_id');
        });
    }
};

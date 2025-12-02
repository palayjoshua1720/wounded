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
            $table->string('order_number')->nullable()->after('order_code');
            $table->string('tracking_code')->nullable()->after('tracking_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
            $table->dropColumn('tracking_code');
        });
    }
};

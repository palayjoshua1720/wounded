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
            $table->string('order_file', 255)
                ->nullable()
                ->after('items');

            $table->string('tracking_link', 255)
                ->nullable()
                ->after('tracking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_file',
                'tracking_link',
            ]);
        });
    }
};

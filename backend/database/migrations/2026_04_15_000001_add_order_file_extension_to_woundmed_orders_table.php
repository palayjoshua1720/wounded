<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('woundmed_orders', 'order_file_extension')) {
            Schema::table('woundmed_orders', function (Blueprint $table) {
                $table->string('order_file_extension', 10)->nullable()->after('order_file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('woundmed_orders', 'order_file_extension')) {
            Schema::table('woundmed_orders', function (Blueprint $table) {
                $table->dropColumn('order_file_extension');
            });
        }
    }
};

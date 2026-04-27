<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->string('ivr_file_extension', 10)->nullable()->after('ivr_file');
            $table->string('order_file_extension', 10)->nullable()->after('order_file');
            $table->string('onboarding_file_extension', 10)->nullable()->after('onboarding_file');
        });
    }

    public function down(): void
    {
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->dropColumn([
                'ivr_file_extension',
                'order_file_extension',
                'onboarding_file_extension',
            ]);
        });
    }
};

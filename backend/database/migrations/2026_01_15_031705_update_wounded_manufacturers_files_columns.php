<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Raw rename – preserves type, nullability, comment
        DB::statement(
            "ALTER TABLE woundmed_manufacturers 
             CHANGE filepath ivr_file 
             VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'For IVR Template'"
        );

        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->string('order_file', 255)->nullable()->after('ivr_file');
            $table->string('onboarding_file', 255)->nullable()->after('order_file');
        });
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE woundmed_manufacturers 
             CHANGE ivr_file filepath 
             VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'For IVR Template'"
        );

        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->dropColumn(['order_file', 'onboarding_file']);
        });
    }
};

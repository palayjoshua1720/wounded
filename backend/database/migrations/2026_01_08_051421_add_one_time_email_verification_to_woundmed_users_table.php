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
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->tinyInteger('one_time_email_verification')->default(0); // 0 = disabled, 1 = enabled
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->dropColumn('one_time_email_verification');
        });
    }
};

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
            $table->tinyInteger('tfa_enabled')->default(0); // 0 = disabled, 1 = enabled
            $table->string('tfa_secret')->nullable();       // store the 2FA secret or code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->dropColumn(['tfa_enabled', 'tfa_secret']);
        });
    }
};

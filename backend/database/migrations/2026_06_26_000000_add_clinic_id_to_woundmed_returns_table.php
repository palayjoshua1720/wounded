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
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_id')->nullable()->after('graft_log_id');

            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn('clinic_id');
        });
    }
};

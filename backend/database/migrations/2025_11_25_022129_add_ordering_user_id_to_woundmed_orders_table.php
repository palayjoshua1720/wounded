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
            $table->unsignedBigInteger('ordering_user_id')->nullable()->after('user_id');

            $table->foreign('ordering_user_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_orders', function (Blueprint $table) {
            $table->dropForeign(['ordering_user_id']);
            $table->dropColumn('ordering_user_id');
        });
    }
};

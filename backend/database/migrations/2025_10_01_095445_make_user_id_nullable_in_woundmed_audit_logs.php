<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['user_id']);

            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Re-add foreign key with ON DELETE SET NULL
            $table->foreign('user_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('woundmed_audit_logs', function (Blueprint $table) {
            // Drop modified foreign key
            $table->dropForeign(['user_id']);

            // Revert back to NOT NULL
            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            // Re-add foreign key with cascade
            $table->foreign('user_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('cascade');
        });
    }
};

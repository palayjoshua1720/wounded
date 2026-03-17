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
        Schema::create('woundmed_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('notification_id');
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('woundmed_users')->onDelete('cascade');
            $table->unique(['user_id', 'notification_id'], 'user_notification_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_notification_reads');
    }
};

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
        Schema::create('magic_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id');
            $table->unsignedBigInteger('order_id');
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('manufacturer_id')->references('manufacturer_id')->on('woundmed_manufacturers')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('woundmed_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magic_tokens');
    }
};

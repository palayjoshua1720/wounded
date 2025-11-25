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
        Schema::create('woundmed_returns', function (Blueprint $table) {
            $table->id('return_id');

    // Foreign Keys
    $table->unsignedBigInteger('graft_log_id');
    $table->unsignedBigInteger('brand_id');
    $table->unsignedBigInteger('graft_size_id');

    // Additional fields
    $table->string('reason')->nullable();

    $table->timestamp('returned_at')->nullable();
    $table->timestamps(); // created_at, updated_at

    // Foreign Key Constraints
    $table->foreign('graft_log_id')
        ->references('graft_log_id')
        ->on('woundmed_usage_log')
        ->onDelete('cascade');

    $table->foreign('brand_id')
        ->references('brand_id')
        ->on('woundmed_brands')
        ->onDelete('restrict');

    $table->foreign('graft_size_id')
        ->references('graft_size_id')
        ->on('woundmed_graft_sizes')
        ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_returns');
    }
};

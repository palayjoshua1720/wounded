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
            $table->unsignedBigInteger('graft_log_id')->nullable();
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('graft_size_id');
            $table->text('reason');
            $table->timestamp('returned_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign key constraints
            $table->foreign('graft_log_id')
                ->references('graft_log_id')
                ->on('woundmed_usage_log')
                ->onDelete('set null');

            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('woundmed_brands')
                ->onDelete('cascade');

            $table->foreign('graft_size_id')
                ->references('graft_size_id')
                ->on('woundmed_graft_sizes')
                ->onDelete('cascade');
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

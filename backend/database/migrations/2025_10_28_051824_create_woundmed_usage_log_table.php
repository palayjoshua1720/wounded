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
        Schema::create('woundmed_usage_log', function (Blueprint $table) {
            $table->id(); 

            // Foreign Keys
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('logged_by'); // clinician user
            $table->unsignedBigInteger('graft_size_id');

            // Core Fields
            $table->string('serial_number')->unique();
            $table->date('date_of_service');
            $table->date('expired_at')->nullable();
            $table->string('filepath')->nullable(); // for sticker
            $table->string('wound_part'); // location to apply

            // Status & Description
            $table->tinyInteger('log_status')
                  ->comment('0 - Used, 1 - Unused, 2 - Expired, 3 - Returned');
            $table->text('description')->nullable();

            // Quantity & Timestamps
            $table->integer('quantity_used')->default(1);
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Foreign Key Constraints
            $table->foreign('patient_id')
                  ->references('patient_id')
                  ->on('woundmed_patients')
                  ->onDelete('cascade');

            $table->foreign('logged_by')
                  ->references('user_id')
                  ->on('woundmed_users')
                  ->onDelete('cascade');

            $table->foreign('graft_size_id')
                  ->references('graft_size_id')
                  ->on('woundmed_graft_sizes')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_usage_log');
    }
};

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
        Schema::create('woundmed_clinic_clinician', function (Blueprint $table) {
            $table->id();

            // Foreign key to clinics (use clinic_id, not id)
            $table->unsignedBigInteger('clinic_id');
            $table->foreign('clinic_id')
                ->references('clinic_id')->on('woundmed_clinics')
                ->onDelete('cascade');

            // Foreign key to users (clinicians live in woundmed_users.id)
            $table->unsignedBigInteger('clinician_id');
            $table->foreign('clinician_id')
                ->references('id')->on('woundmed_users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_clinic_clinician');
    }
};

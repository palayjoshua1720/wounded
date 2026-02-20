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
        Schema::create('woundmed_biller_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('invoice_number');
            $table->date('service_date');
            $table->date('submission_date')->nullable();
            $table->decimal('medicare_amount', 10, 2);
            $table->decimal('provider_amount', 10, 2);
            $table->string('status');
            $table->string('clinician')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_biller_tracking');
    }
};

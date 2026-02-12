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
        Schema::create('serial_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('woundmed_invoices')->onDelete('cascade');
            $table->string('serial_number');
            $table->string('status')->default('pending'); // pending, paid, cancelled, etc.
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamp('paid_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('metadata')->nullable(); // Additional data about the payment
            $table->timestamps();
            
            // Ensure unique combination of invoice and serial number
            $table->unique(['invoice_id', 'serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_payments');
    }
};

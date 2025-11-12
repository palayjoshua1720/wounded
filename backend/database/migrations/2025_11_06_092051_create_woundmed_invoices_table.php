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
        Schema::create('woundmed_invoices', function (Blueprint $table) {
            $table->bigIncrements('invoice_id');

            // Foreign key to woundmed_orders
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')
                ->references('order_id')
                ->on('woundmed_orders')
                ->onDelete('cascade');

            // Foreign key to woundmed_users
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('set null');

            $table->date('invoice_date')->nullable();

            $table->tinyInteger('payment_status')
                ->default(0)
                ->comment('0 - pending, 1 - unpaid, 2 - paid');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_invoices');
    }
};

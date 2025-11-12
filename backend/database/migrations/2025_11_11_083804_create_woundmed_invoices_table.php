<?php
// database/migrations/2024_01_01_create_invoices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWoundmedInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('woundmed_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('clinic_id')->constrained('woundmed_clinics', 'clinic_id')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending_review', 'pending', 'paid', 'overdue', 'cancelled'])->default('pending_review');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->string('order_id')->nullable();
            $table->json('serials')->nullable(); // Store serial numbers as JSON array
            $table->text('notes')->nullable();
            $table->boolean('needs_review')->default(true);
            $table->enum('sync_status', ['synced', 'out_of_sync'])->default('out_of_sync');
            $table->boolean('partial_payment')->default(false);
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('woundmed_invoices');
    }
}
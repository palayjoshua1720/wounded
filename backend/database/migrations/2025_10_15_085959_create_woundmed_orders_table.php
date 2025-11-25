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
        Schema::create('woundmed_graft_sizes', function (Blueprint $table) {
            $table->id('graft_size_id');  // PK: Auto-incrementing graft_size_id

            $table->foreignId('brand_id')
                ->constrained('woundmed_brands', 'brand_id')
                ->onDelete('cascade');  // FK: References brand_id, delete sizes if brand deleted

            $table->string('size');  // e.g., "2cm x 2cm"
            $table->decimal('area', 8, 2);  // e.g., 4.00 cm² (precision: 8 digits total, 2 decimal)
            $table->decimal('price', 10, 2);  // e.g., 99.99 (precision: 10 digits total, 2 decimal)
            $table->tinyInteger('graft_status')
                ->default(0)
                ->comment('0=Active, 1=Inactive, 2=Archive');

            $table->timestamps();  // created_at (required), updated_at (nullable)
            $table->softDeletes();  // deleted_at (nullable for soft delete)

            $table->index(['brand_id', 'graft_status']);  // Optional: Index for faster queries by brand/status
        });

        Schema::create('woundmed_orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->string('order_code')->unique();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('user_id'); // clinician_id
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('graft_id')->nullable();
            $table->unsignedBigInteger('ivr_id')->nullable();
            $table->string('tracking_num')->nullable(); // ex. TRK00001
            $table->json('items'); // array of item details
            $table->tinyInteger('order_status')->default(0);
            // 0 - Submitted, 1 - Acknowledged, 2 - Shipped, 3 - Delivered
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // ✅ Correct FK names
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('cascade');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('woundmed_patient_info')
                ->onDelete('cascade');

            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('woundmed_brands')
                ->onDelete('set null');

            $table->foreign('graft_id')
                ->references('graft_size_id')
                ->on('woundmed_graft_sizes')
                ->onDelete('set null');

            $table->foreign('ivr_id')
                ->references('ivr_id')
                ->on('woundmed_ivr')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_orders');
        Schema::dropIfExists('woundmed_graft_sizes');
    }
};

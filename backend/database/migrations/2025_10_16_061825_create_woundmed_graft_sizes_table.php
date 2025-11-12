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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_graft_sizes');
    }
};

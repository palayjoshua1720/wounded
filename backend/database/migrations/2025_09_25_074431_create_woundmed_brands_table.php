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
        Schema::create('woundmed_brands', function (Blueprint $table) {
            $table->bigIncrements('brand_id'); // Primary Key
            $table->unsignedBigInteger('manufacturer_id'); // under what manufacturer
            $table->tinyInteger('product_type')->comment('0 - Graft, 1 - Device');
            $table->string('mue');
            $table->text('description')->nullable();
            $table->tinyInteger('brand_status')
                ->default(0)
                ->comment('0 - Active, 1 - Inactive, 2 - Archive');
            $table->timestamps();   // created_at & updated_at
            $table->softDeletes();  // deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_brands');
    }
};

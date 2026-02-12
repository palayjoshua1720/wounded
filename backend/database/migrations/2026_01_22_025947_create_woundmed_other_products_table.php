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
        Schema::create('woundmed_other_products', function (Blueprint $table) {
            $table->bigIncrements('other_product_id');
            $table->tinyInteger('product_type')
                ->comment('0 = wound supplies, 1 = devices');
            $table->string('product_name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active, 2 = archived');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_other_products');
    }
};

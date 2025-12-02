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
        Schema::create('woundmed_patient_info', function (Blueprint $table) {
            $table->bigIncrements('patient_id');
            $table->unsignedBigInteger('user_id'); // FK to woundmed_users
            $table->string('patient_name');
            $table->string('email');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            // foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('woundmed_users')
                ->onDelete('cascade');
        });

        Schema::create('woundmed_ivr', function (Blueprint $table) {
            $table->bigIncrements('ivr_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('manufacturer_id');
            $table->unsignedBigInteger('patient_id');
            $table->text('description')->nullable();
            $table->tinyInteger('eligibility_status')
                ->default(0)
                ->comment('0 - Pending, 1 - Eligible, 2 - Not Eligible');
            $table->tinyInteger('ivr_status')
                ->default(0)
                ->comment('0 - On the List, 1 - Archive');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();

            // use this instead of $table->timestamps()
            $table->timestamp('created_at')->nullable();

            // FKs
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('cascade');

            $table->foreign('brand_id')
                ->references('brand_id')
                ->on('woundmed_brands')
                ->onDelete('cascade');

            $table->foreign('manufacturer_id')
                ->references('manufacturer_id')
                ->on('woundmed_manufacturers')
                ->onDelete('cascade');

            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('woundmed_patient_info')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_ivr');
        Schema::dropIfExists('woundmed_patient_info');
    }
};

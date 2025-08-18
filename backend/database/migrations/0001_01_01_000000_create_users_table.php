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
        Schema::create('woundmed_clinics', function (Blueprint $table) {
            $table->id('clinic_id');

            $table->string('clinic_name');
            $table->string('email')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();

            $table->tinyInteger('clinic_status')->default(0)->comment('0=Active, 1=Inactive, 2=Archive');

            $table->timestamps();
        });

        Schema::create('woundmed_manufacturers', function (Blueprint $table) {
            $table->id('manufacturer_id');

            $table->string('manufacturer_name');
            $table->string('email')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('filepath')->nullable()->comment('For IVR Template');

            $table->tinyInteger('manufacturer_status')
                ->default(0)
                ->comment('0=Active, 1=Inactive, 2=Archive');

            $table->timestamps();
        });

        Schema::create('woundmed_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->foreign('clinic_id')
                ->references('clinic_id')
                ->on('woundmed_clinics')
                ->onDelete('set null');
            $table->foreign('manufacturer_id')
                ->references('manufacturer_id')
                ->on('woundmed_manufacturers')
                ->onDelete('set null');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->tinyInteger('user_role')->comment('0=Admin, 1=Office staff, 2=Clinics, 3=Clinician, 4=Manufacturer, 5=Biller');
            $table->tinyInteger('user_status')->default(0)->comment('0=Active, 1=Inactive, 2=Archive');

            $table->string('phone', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Increases column sizes to accommodate encrypted data.
     * Encrypted data is typically 3-4x larger than plain text.
     */
    public function up(): void
    {
        // woundmed_users table
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->string('first_name', 500)->change();
            $table->string('middle_name', 500)->nullable()->change();
            $table->string('last_name', 500)->change();
            $table->string('email', 500)->change();
            $table->string('phone', 500)->nullable()->change();
        });

        // woundmed_clinics table
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->string('clinic_name', 500)->change();
            $table->string('email', 500)->change();
            $table->string('contact_person', 500)->nullable()->change();
            $table->string('phone', 500)->nullable()->change();
            $table->text('address')->nullable()->change();
        });

        // woundmed_manufacturers table
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->string('manufacturer_name', 500)->change();
            $table->string('contact_person', 500)->nullable()->change();
            $table->string('contact_number', 500)->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->string('primary_email', 500)->nullable()->change();
            $table->string('order_email', 500)->nullable()->change();
            $table->string('eligibility_email', 500)->nullable()->change();
            $table->string('secondary_email', 500)->nullable()->change();
            $table->string('website', 500)->nullable()->change();
        });

        // woundmed_patient_info table
        Schema::table('woundmed_patient_info', function (Blueprint $table) {
            $table->string('patient_name', 500)->change();
            $table->string('email', 500)->nullable()->change();
        });

        // woundmed_brands table
        Schema::table('woundmed_brands', function (Blueprint $table) {
            $table->string('brand_name', 500)->change();
            $table->text('description')->nullable()->change();
        });

        // woundmed_ivr table
        Schema::table('woundmed_ivr', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->text('ivr_file')->nullable()->change();
        });

        // woundmed_orders table
        Schema::table('woundmed_orders', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
            $table->text('order_file')->nullable()->change();
            $table->string('tracking_num', 500)->nullable()->change();
            $table->string('tracking_link', 500)->nullable()->change();
        });

        // woundmed_invoices table
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            $table->text('bill_to')->nullable()->change();
        });

        // woundmed_returns table
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
            $table->text('other')->nullable()->change();
            $table->text('ocr_serial_number')->nullable()->change();
            $table->text('ocr_product_code')->nullable()->change();
        });

        // woundmed_usage_log table
        Schema::table('woundmed_usage_log', function (Blueprint $table) {
            $table->string('serial_number', 500)->nullable()->change();
            $table->text('filepath')->nullable()->change();
            $table->string('wound_part', 500)->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        // woundmed_biller_tracking table
        Schema::table('woundmed_biller_tracking', function (Blueprint $table) {
            $table->string('patient_name', 500)->change();
            $table->string('invoice_number', 500)->change();
            $table->string('clinician', 500)->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // woundmed_other_products table
        Schema::table('woundmed_other_products', function (Blueprint $table) {
            $table->string('product_name', 500)->change();
            $table->text('description')->nullable()->change();
        });

        // serial_payments table
        Schema::table('serial_payments', function (Blueprint $table) {
            $table->string('serial_number', 500)->change();
            $table->string('payment_method', 500)->nullable()->change();
            $table->string('payment_reference', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reversing encrypted column sizes is not recommended
        // as it may truncate encrypted data
        
        // woundmed_users table
        Schema::table('woundmed_users', function (Blueprint $table) {
            $table->string('first_name', 255)->change();
            $table->string('middle_name', 255)->nullable()->change();
            $table->string('last_name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('phone', 20)->nullable()->change();
        });

        // woundmed_clinics table
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->string('clinic_name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('contact_person', 255)->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
        });

        // woundmed_manufacturers table
        Schema::table('woundmed_manufacturers', function (Blueprint $table) {
            $table->string('manufacturer_name', 255)->change();
            $table->string('contact_person', 255)->nullable()->change();
            $table->string('contact_number', 20)->nullable()->change();
            $table->string('address', 255)->nullable()->change();
            $table->string('primary_email', 255)->nullable()->change();
            $table->string('order_email', 255)->nullable()->change();
            $table->string('eligibility_email', 255)->nullable()->change();
            $table->string('secondary_email', 255)->nullable()->change();
            $table->string('website', 255)->nullable()->change();
        });

        // woundmed_patient_info table
        Schema::table('woundmed_patient_info', function (Blueprint $table) {
            $table->string('patient_name', 255)->change();
            $table->string('email', 255)->nullable()->change();
        });

        // woundmed_brands table
        Schema::table('woundmed_brands', function (Blueprint $table) {
            $table->string('brand_name', 255)->change();
            $table->string('description', 255)->nullable()->change();
        });

        // woundmed_ivr table
        Schema::table('woundmed_ivr', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
            $table->string('ivr_file', 255)->nullable()->change();
        });

        // woundmed_orders table
        Schema::table('woundmed_orders', function (Blueprint $table) {
            $table->string('notes', 255)->nullable()->change();
            $table->string('order_file', 255)->nullable()->change();
            $table->string('tracking_num', 255)->nullable()->change();
            $table->string('tracking_link', 255)->nullable()->change();
        });

        // woundmed_invoices table
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            $table->string('bill_to', 255)->nullable()->change();
        });

        // woundmed_returns table
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->string('reason', 255)->nullable()->change();
            $table->string('other', 255)->nullable()->change();
            $table->string('ocr_serial_number', 255)->nullable()->change();
            $table->string('ocr_product_code', 255)->nullable()->change();
        });

        // woundmed_usage_log table
        Schema::table('woundmed_usage_log', function (Blueprint $table) {
            $table->string('serial_number', 255)->nullable()->change();
            $table->string('filepath', 255)->nullable()->change();
            $table->string('wound_part', 255)->nullable()->change();
            $table->string('description', 255)->nullable()->change();
        });

        // woundmed_biller_tracking table
        Schema::table('woundmed_biller_tracking', function (Blueprint $table) {
            $table->string('patient_name', 255)->change();
            $table->string('invoice_number', 255)->change();
            $table->string('clinician', 255)->nullable()->change();
            $table->string('notes', 255)->nullable()->change();
        });

        // woundmed_other_products table
        Schema::table('woundmed_other_products', function (Blueprint $table) {
            $table->string('product_name', 255)->change();
            $table->string('description', 255)->nullable()->change();
        });

        // serial_payments table
        Schema::table('serial_payments', function (Blueprint $table) {
            $table->string('serial_number', 255)->change();
            $table->string('payment_method', 255)->nullable()->change();
            $table->string('payment_reference', 255)->nullable()->change();
        });
    }
};

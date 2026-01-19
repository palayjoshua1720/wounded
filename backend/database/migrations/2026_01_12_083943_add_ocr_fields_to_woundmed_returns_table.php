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
        Schema::table('woundmed_returns', function (Blueprint $table) {
            // OCR extracted fields for upload entry type
            $table->string('ocr_serial_number', 255)->nullable()->after('entry_type')->comment('Serial number extracted from uploaded document');
            $table->date('ocr_expiry_date')->nullable()->after('ocr_serial_number')->comment('Expiry date extracted from uploaded document');
            $table->string('ocr_product_code', 100)->nullable()->after('ocr_expiry_date')->comment('Product code extracted from uploaded document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_returns', function (Blueprint $table) {
            $table->dropColumn(['ocr_serial_number', 'ocr_expiry_date', 'ocr_product_code']);
        });
    }
};

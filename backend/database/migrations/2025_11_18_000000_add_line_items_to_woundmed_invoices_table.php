<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLineItemsToWoundmedInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            // Add a new JSON column for line items
            $table->json('line_items')->nullable()->after('serials');
            
            // Add a column to track if line items have been processed
            $table->boolean('has_line_items')->default(false)->after('line_items');
        });
    }

    public function down()
    {
        Schema::table('woundmed_invoices', function (Blueprint $table) {
            $table->dropColumn(['line_items', 'has_line_items']);
        });
    }
}
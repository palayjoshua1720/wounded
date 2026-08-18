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
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('address');
            $table->string('logo_extension')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woundmed_clinics', function (Blueprint $table) {
            $table->dropColumn('logo');
            $table->dropColumn('logo_extension');
        });
    }
};

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
        Schema::create('woundmed_audit_logs', function (Blueprint $table) {
            $table->id('audit_log_id');
            $table->foreignId('user_id')->constrained('woundmed_users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('action');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('FK to affected entity, depends on entity table');
            $table->string('entity')->nullable()->comment('The table affected by the action');
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woundmed_audit_logs');
    }
};

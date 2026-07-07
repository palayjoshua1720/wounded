<?php

// ============================================================================
// PATIENT GRAFT LOG MODULE - ADD GRAFT LOG CODE COLUMN
// ----------------------------------------------------------------------------
// Adds a public-facing unique identifier (GRL-XXXXXXXX) for each graft log
// entry. Existing rows are backfilled with generated codes.
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->string('graft_log_code', 32)->nullable()->after('graft_log_id');
        });

        // Backfill existing rows with unique codes.
        $rows = DB::table('woundmed_patient_graft_log')->select('graft_log_id')->get();
        foreach ($rows as $row) {
            DB::table('woundmed_patient_graft_log')
                ->where('graft_log_id', $row->graft_log_id)
                ->update(['graft_log_code' => $this->generateUniqueCode()]);
        }

        // Enforce uniqueness after backfill.
        Schema::table('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->unique('graft_log_code', 'woundmed_patient_graft_log_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('woundmed_patient_graft_log', function (Blueprint $table) {
            $table->dropUnique('woundmed_patient_graft_log_code_unique');
            $table->dropColumn('graft_log_code');
        });
    }

    private function generateUniqueCode(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = 'GRL-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = DB::table('woundmed_patient_graft_log')
                ->where('graft_log_code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }
};

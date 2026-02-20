<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Manually insert the completed migrations into the migrations table
        $migrations = [
            ['migration' => '2025_12_18_024737_create_woundmed_returns_table', 'batch' => 10],
            ['migration' => '2025_12_22_030348_add_other_column_to_woundmed_returns_table', 'batch' => 10],
            ['migration' => '2026_01_07_051649_add_emails_to_woundmed_manufacturers_table', 'batch' => 10],
            ['migration' => '2026_01_08_051321_add_one_time_email_verification_to_woundmed_users_table', 'batch' => 10],
            ['migration' => '2026_01_08_051421_add_one_time_email_verification_to_woundmed_users_table', 'batch' => 10],
            ['migration' => '2026_01_08_052514_create_email_verification_codes_table', 'batch' => 10],
            ['migration' => '2026_01_08_063459_create_backup_codes_table', 'batch' => 10],
            ['migration' => '2026_01_08_063953_add_backup_codes_enabled_to_woundmed_users_table', 'batch' => 10],
            ['migration' => '2026_01_12_082242_add_entry_type_to_woundmed_returns_table', 'batch' => 10],
            ['migration' => '2026_01_12_083943_add_ocr_fields_to_woundmed_returns_table', 'batch' => 10],
            ['migration' => '2026_01_15_031705_update_wounded_manufacturers_files_columns', 'batch' => 10],
            ['migration' => '2026_01_19_075109_add_item_no_to_woundmed_graft_sizes_table', 'batch' => 10],
            ['migration' => '2025_11_27_062356_add_logo_to_woundmed_clinics_table', 'batch' => 10],
            ['migration' => '2025_11_27_102353_add_tracking_code_to_woundmed_orders_table', 'batch' => 10],
            ['migration' => '2025_11_28_090252_update_woundmed_brands_table', 'batch' => 10],
        ];
        
        foreach ($migrations as $migration) {
            // Check if the migration is already in the table
            $exists = DB::table('migrations')->where('migration', $migration['migration'])->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert($migration);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $migrations = [
            '2025_12_18_024737_create_woundmed_returns_table',
            '2025_12_22_030348_add_other_column_to_woundmed_returns_table',
            '2026_01_07_051649_add_emails_to_woundmed_manufacturers_table',
            '2026_01_08_051321_add_one_time_email_verification_to_woundmed_users_table',
            '2026_01_08_051421_add_one_time_email_verification_to_woundmed_users_table',
            '2026_01_08_052514_create_email_verification_codes_table',
            '2026_01_08_063459_create_backup_codes_table',
            '2026_01_08_063953_add_backup_codes_enabled_to_woundmed_users_table',
            '2026_01_12_082242_add_entry_type_to_woundmed_returns_table',
            '2026_01_12_083943_add_ocr_fields_to_woundmed_returns_table',
            '2026_01_15_031705_update_wounded_manufacturers_files_columns',
            '2026_01_19_075109_add_item_no_to_woundmed_graft_sizes_table',
            '2025_11_27_062356_add_logo_to_woundmed_clinics_table',
            '2025_11_27_102353_add_tracking_code_to_woundmed_orders_table',
            '2025_11_28_090252_update_woundmed_brands_table',
        ];
        
        foreach ($migrations as $migration) {
            DB::table('migrations')->where('migration', $migration)->delete();
        }
    }
};

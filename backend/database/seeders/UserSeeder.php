<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\HmacHashService;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove ALL existing rows for this account to avoid duplicates or corrupted records.
        // We query the raw DB because emails are encrypted and WHERE won't match.
        $emailHash = app(HmacHashService::class)->hash('prospteam@gmail.com');
        DB::table('woundmed_users')->where('email_hash', $emailHash)->delete();

        User::factory()->create([
            'first_name'                  => 'Prosp',
            'middle_name'                 => 'T',
            'last_name'                   => 'Team',
            'email'                       => 'prospteam@gmail.com',
            'password'                    => bcrypt('proweaver'),
            'user_role'                   => 0,
            'user_status'                 => 0,
            'phone'                       => '5551234567',
            'backup_codes_enabled'        => false,
            'one_time_email_verification' => 0,
            'tfa_enabled'                 => 0,
        ]);
    }
}

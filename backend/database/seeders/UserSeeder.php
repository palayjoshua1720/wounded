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
        $users = [
            [
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
            ],
            [
                'first_name'                  => 'Clinic',
                'last_name'                   => 'Admin',
                'email'                       => 'clinicadmin@example.com',
                'password'                    => bcrypt('proweaver'),
                'user_role'                   => 2,
                'user_status'                 => 0,
                'phone'                       => '5555550001',
                'backup_codes_enabled'        => false,
                'one_time_email_verification' => 0,
                'tfa_enabled'                 => 0,
            ],
            [
                'first_name'                  => 'Manufacturer',
                'last_name'                   => 'User',
                'email'                       => 'manufacturer@example.com',
                'password'                    => bcrypt('proweaver'),
                'user_role'                   => 4,
                'user_status'                 => 0,
                'phone'                       => '5555550002',
                'backup_codes_enabled'        => false,
                'one_time_email_verification' => 0,
                'tfa_enabled'                 => 0,
            ],
            [
                'first_name'                  => 'Clinic',
                'last_name'                   => 'Clinician',
                'email'                       => 'clinician@example.com',
                'password'                    => bcrypt('proweaver'),
                'user_role'                   => 3,
                'user_status'                 => 0,
                'phone'                       => '5555550003',
                'backup_codes_enabled'        => false,
                'one_time_email_verification' => 0,
                'tfa_enabled'                 => 0,
            ],
        ];

        foreach ($users as $user) {
            $this->createUser($user);
        }

        // Optional: create additional random users for local testing
        User::factory()->count(5)->create();
    }

    private function createUser(array $attributes): void
    {
        $emailHash = app(HmacHashService::class)->hash($attributes['email']);
        DB::table('woundmed_users')->where('email_hash', $emailHash)->delete();

        User::factory()->create($attributes);
    }
}
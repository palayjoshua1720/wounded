<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPrivilegeEscalationTest extends TestCase
{
    use DatabaseTransactions;

    protected Clinic $clinicA;
    protected Clinic $clinicB;
    protected User $admin;
    protected User $officeStaff;
    protected User $clinicAdminA;
    protected User $clinicianA;

    protected function findOrCreateTestUser(string $email, array $attributes): User
    {
        $hashService = app(HmacHashService::class);
        $emailHash = $hashService->hash($email);

        $user = User::withTrashed()->where('email_hash', $emailHash)->first();
        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
            $user->update(array_merge(['user_status' => 0], $attributes));
            return $user;
        }

        return User::create(array_merge([
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'user_status' => 0,
        ], $attributes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create or get Clinic A
        $this->clinicA = Clinic::firstOrCreate(
            ['clinic_name' => 'Test Clinic A'],
            ['email' => 'clinicA_test@example.com', 'clinic_status' => 0]
        );

        // Create or get Clinic B
        $this->clinicB = Clinic::firstOrCreate(
            ['clinic_name' => 'Test Clinic B'],
            ['email' => 'clinicB_test@example.com', 'clinic_status' => 0]
        );

        // Super Admin (role 0)
        $this->admin = $this->findOrCreateTestUser('admin_test_escalate@woundmed.com', [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'user_role' => 0,
        ]);
        $this->admin->update(['user_role' => 0, 'user_status' => 0]);

        // Office Staff (role 1)
        $this->officeStaff = $this->findOrCreateTestUser('officestaff_test_escalate@woundmed.com', [
            'first_name' => 'Office',
            'last_name' => 'Staff',
            'user_role' => 1,
        ]);
        $this->officeStaff->update(['user_role' => 1, 'user_status' => 0]);

        // Clinic Admin (role 2) for Clinic A
        $this->clinicAdminA = $this->findOrCreateTestUser('clinicadmin_a_escalate@woundmed.com', [
            'first_name' => 'Clinic',
            'last_name' => 'Admin A',
            'user_role' => 2,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);
        $this->clinicAdminA->update(['clinic_id' => $this->clinicA->clinic_id, 'user_role' => 2, 'user_status' => 0]);

        // Clinician (role 3) for Clinic A
        $this->clinicianA = $this->findOrCreateTestUser('clinician_a_escalate@woundmed.com', [
            'first_name' => 'Doctor',
            'last_name' => 'Clinician A',
            'user_role' => 3,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);
        $this->clinicianA->update(['clinic_id' => $this->clinicA->clinic_id, 'user_role' => 3, 'user_status' => 0]);
    }

    public function test_clinic_admin_cannot_escalate_clinician_to_admin(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 0, // Attempt to assign Admin role
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_assignment',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_clinic_admin_cannot_escalate_clinician_to_office_staff(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 1, // Attempt to assign Office Staff role
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_assignment',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_clinic_admin_cannot_reassign_clinician_to_different_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'clinic_id' => $this->clinicB->clinic_id, // Attempt cross-clinic tampering
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_clinic_assignment',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals($this->clinicA->clinic_id, $this->clinicianA->clinic_id);
    }

    public function test_office_staff_cannot_escalate_user_to_admin(): void
    {
        Sanctum::actingAs($this->officeStaff);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 0, // Attempt to assign Admin role
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_assignment',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_office_staff_cannot_escalate_user_to_office_staff(): void
    {
        Sanctum::actingAs($this->officeStaff);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 1, // Attempt to assign Office Staff role
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_assignment',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_office_staff_cannot_create_office_staff_in_user_controller(): void
    {
        Sanctum::actingAs($this->officeStaff);
        $uniqueEmail = 'os_create_os_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'Peer',
            'last_name' => 'Staff',
            'email' => $uniqueEmail,
            'user_role' => 1, // Office Staff attempting to create another Office Staff
            'user_status' => 0,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_creation',
        ]);
    }

    public function test_office_staff_cannot_create_admin_in_user_controller(): void
    {
        Sanctum::actingAs($this->officeStaff);
        $uniqueEmail = 'os_create_admin_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'Backdoor',
            'last_name' => 'Admin',
            'email' => $uniqueEmail,
            'user_role' => 0, // Office Staff attempting to create Admin
            'user_status' => 0,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_creation',
        ]);
    }

    public function test_office_staff_cannot_create_office_staff_in_clinic_controller(): void
    {
        Sanctum::actingAs($this->officeStaff);
        $uniqueEmail = 'os_clinic_os_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/management/users/add/clinician', [
            'first_name' => 'Peer',
            'last_name' => 'Staff',
            'email' => $uniqueEmail,
            'role' => 'Admin', // Maps to role 1 in ClinicController
            'isActive' => true,
            'clinicId' => $this->clinicA->clinic_id,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_assignment',
        ]);
    }

    public function test_office_staff_can_legitimately_create_clinician(): void
    {
        Sanctum::actingAs($this->officeStaff);
        $uniqueEmail = 'os_create_clinician_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'Legit',
            'last_name' => 'Clinician',
            'email' => $uniqueEmail,
            'user_role' => 3, // Clinician
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);

        $response->assertStatus(201);

        $hmac = app(\App\Services\HmacHashService::class)->hash($uniqueEmail);
        $createdUser = User::where('email_hash', $hmac)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals(3, $createdUser->user_role);

        $createdUser->forceDelete();
    }

    public function test_user_cannot_modify_own_user_role(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 0, // Attempt self-escalation
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'self_role_modification_prohibited',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_clinic_admin_can_legitimately_update_clinician_details_within_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'phone' => '123-456-7890',
            'user_role' => 3, // Valid same role
        ]);

        $response->assertStatus(200);

        $this->clinicianA->refresh();
        $this->assertEquals('123-456-7890', $this->clinicianA->phone);
    }

    public function test_super_admin_can_update_roles(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 5, // Admin updates clinician to biller
        ]);

        $response->assertStatus(200);

        $this->clinicianA->refresh();
        $this->assertEquals(5, $this->clinicianA->user_role);

        // Restore back to 3
        $this->clinicianA->update(['user_role' => 3]);
    }

    public function test_clinic_admin_cannot_create_admin_or_office_staff(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->postJson('/api/users', [
            'first_name' => 'Bad',
            'last_name' => 'Actor',
            'email' => 'badactor_' . uniqid() . '@example.com',
            'user_role' => 0,
            'user_status' => 0,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_role_creation',
        ]);
    }

    public function test_clinic_admin_cannot_create_clinician_for_different_clinic_in_user_controller(): void
    {
        Sanctum::actingAs($this->clinicAdminA);
        $uniqueEmail = 'newclinician_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'New',
            'last_name' => 'Clinician',
            'email' => $uniqueEmail,
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicB->clinic_id, // Attempts to assign to clinic B
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_clinic_assignment',
        ]);
    }

    public function test_clinic_admin_can_create_clinician_for_own_clinic_and_pivot_is_synced(): void
    {
        Sanctum::actingAs($this->clinicAdminA);
        $uniqueEmail = 'newclinician_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'New',
            'last_name' => 'Clinician',
            'email' => $uniqueEmail,
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);

        $response->assertStatus(201);
        $createdUser = User::where('id', $response->json('user.id'))->first();
        $this->assertEquals($this->clinicA->clinic_id, $createdUser->clinic_id);

        // Verify pivot table is synchronized
        $pivotExists = DB::table('woundmed_clinic_clinician')
            ->where('clinician_id', $createdUser->id)
            ->where('clinic_id', $this->clinicA->clinic_id)
            ->exists();
        $this->assertTrue($pivotExists);

        DB::table('woundmed_clinic_clinician')->where('clinician_id', $createdUser->id)->delete();
        $createdUser->forceDelete();
    }

    public function test_clinic_admin_cannot_create_clinician_for_different_clinic_in_clinic_controller(): void
    {
        Sanctum::actingAs($this->clinicAdminA);
        $uniqueEmail = 'addclinician_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/management/users/add/clinician', [
            'first_name' => 'Cross',
            'last_name' => 'Clinic',
            'email' => $uniqueEmail,
            'role' => 'Clinician',
            'isActive' => true,
            'clinicId' => $this->clinicB->clinic_id, // Competitor clinic B
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_clinic_assignment',
        ]);
    }

    public function test_clinic_admin_can_add_clinician_to_own_clinic_via_clinic_controller(): void
    {
        Sanctum::actingAs($this->clinicAdminA);
        $uniqueEmail = 'addclinician_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/management/users/add/clinician', [
            'first_name' => 'Valid',
            'last_name' => 'Clinician',
            'email' => $uniqueEmail,
            'role' => 'Clinician',
            'isActive' => true,
            'clinicId' => $this->clinicA->clinic_id,
        ]);

        $response->assertStatus(200);

        $hmac = app(\App\Services\HmacHashService::class)->hash($uniqueEmail);
        $createdUser = User::where('email_hash', $hmac)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($this->clinicA->clinic_id, $createdUser->clinic_id);

        // Verify pivot
        $pivotExists = DB::table('woundmed_clinic_clinician')
            ->where('clinician_id', $createdUser->id)
            ->where('clinic_id', $this->clinicA->clinic_id)
            ->exists();
        $this->assertTrue($pivotExists);

        DB::table('woundmed_clinic_clinician')->where('clinician_id', $createdUser->id)->delete();
        $createdUser->forceDelete();
    }

    public function test_clinic_admin_clinician_listing_is_scoped_to_own_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(200);

        $data = $response->json('user_data');
        $this->assertNotEmpty($data);

        // Every clinician returned must belong to Clinic A
        foreach ($data as $item) {
            $userInDb = User::find($item['id']);
            $this->assertEquals($this->clinicA->clinic_id, $userInDb->clinic_id);
        }
    }

    public function test_clinician_creation_requires_active_clinic(): void
    {
        Sanctum::actingAs($this->admin);
        $uniqueEmail = 'noclinic_' . uniqid() . '@example.com';

        // Creation without clinic_id
        $response = $this->postJson('/api/users', [
            'first_name' => 'No',
            'last_name' => 'Clinic',
            'email' => $uniqueEmail,
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => null,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'clinic_id_required',
        ]);
    }

    public function test_cannot_demote_the_last_active_super_admin(): void
    {
        $otherAdmins = User::where('user_role', 0)->where('id', '!=', $this->admin->id)->get(['id', 'user_status']);
        $tempAdmin = $this->findOrCreateTestUser('temp_admin_guard@woundmed.com', [
            'first_name' => 'Temp',
            'last_name' => 'Admin',
            'user_role' => 0,
            'user_status' => 1, // inactive
        ]);

        try {
            // Deactivate any other super admins so only $this->admin is active
            User::where('user_role', 0)->where('id', '!=', $this->admin->id)->update(['user_status' => 1]);
            $this->admin->update(['user_status' => 0, 'user_role' => 0]);

            $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsActive::class);
            Sanctum::actingAs($tempAdmin);

            $response = $this->putJson("/api/users/{$this->admin->id}", [
                'user_role' => 1, // Attempt to demote the only active super admin to office staff
            ]);

            $response->assertStatus(422);
            $response->assertJson([
                'error' => 'last_admin_protection',
            ]);

            $this->admin->refresh();
            $this->assertEquals(0, $this->admin->user_role);
        } finally {
            foreach ($otherAdmins as $other) {
                User::where('id', $other->id)->update(['user_status' => $other->user_status]);
            }
            $tempAdmin->forceDelete();
        }
    }

    public function test_cannot_deactivate_or_archive_the_last_active_super_admin_via_update(): void
    {
        $otherAdmins = User::where('user_role', 0)->where('id', '!=', $this->admin->id)->get(['id', 'user_status']);
        $tempAdmin = $this->findOrCreateTestUser('temp_admin_guard2@woundmed.com', [
            'first_name' => 'Temp',
            'last_name' => 'Admin2',
            'user_role' => 0,
            'user_status' => 1,
        ]);

        try {
            // Ensure only $this->admin is active
            User::where('user_role', 0)->where('id', '!=', $this->admin->id)->update(['user_status' => 1]);
            $this->admin->update(['user_status' => 0, 'user_role' => 0]);

            $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsActive::class);
            Sanctum::actingAs($tempAdmin);

            // Attempt to set status to inactive (1)
            $response = $this->putJson("/api/users/{$this->admin->id}", [
                'user_status' => 1,
            ]);

            $response->assertStatus(422);
            $response->assertJson([
                'error' => 'last_admin_protection',
            ]);

            // Attempt to set status to archived (2)
            $responseArch = $this->putJson("/api/users/{$this->admin->id}", [
                'user_status' => 2,
            ]);

            $responseArch->assertStatus(422);
            $responseArch->assertJson([
                'error' => 'last_admin_protection',
            ]);

            $this->admin->refresh();
            $this->assertEquals(0, $this->admin->user_status);
        } finally {
            foreach ($otherAdmins as $other) {
                User::where('id', $other->id)->update(['user_status' => $other->user_status]);
            }
            $tempAdmin->forceDelete();
        }
    }

    public function test_cannot_toggle_status_of_last_active_super_admin(): void
    {
        $otherAdmins = User::where('user_role', 0)->where('id', '!=', $this->admin->id)->get(['id', 'user_status']);
        $tempAdmin = $this->findOrCreateTestUser('temp_admin_guard3@woundmed.com', [
            'first_name' => 'Temp',
            'last_name' => 'Admin3',
            'user_role' => 0,
            'user_status' => 1,
        ]);

        try {
            User::where('user_role', 0)->where('id', '!=', $this->admin->id)->update(['user_status' => 1]);
            $this->admin->update(['user_status' => 0, 'user_role' => 0]);

            $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsActive::class);
            Sanctum::actingAs($tempAdmin);

            $response = $this->patchJson("/api/users/{$this->admin->id}/toggle-status");

            $response->assertStatus(422);
            $response->assertJson([
                'error' => 'last_admin_protection',
            ]);

            $this->admin->refresh();
            $this->assertEquals(0, $this->admin->user_status);
        } finally {
            foreach ($otherAdmins as $other) {
                User::where('id', $other->id)->update(['user_status' => $other->user_status]);
            }
            $tempAdmin->forceDelete();
        }
    }

    public function test_cannot_archive_last_active_super_admin(): void
    {
        $otherAdmins = User::where('user_role', 0)->where('id', '!=', $this->admin->id)->get(['id', 'user_status']);
        $tempAdmin = $this->findOrCreateTestUser('temp_admin_guard4@woundmed.com', [
            'first_name' => 'Temp',
            'last_name' => 'Admin4',
            'user_role' => 0,
            'user_status' => 1,
        ]);

        try {
            User::where('user_role', 0)->where('id', '!=', $this->admin->id)->update(['user_status' => 1]);
            $this->admin->update(['user_status' => 0, 'user_role' => 0]);

            $this->withoutMiddleware(\App\Http\Middleware\EnsureUserIsActive::class);
            Sanctum::actingAs($tempAdmin);

            $response = $this->patchJson("/api/users/{$this->admin->id}/archive");

            $response->assertStatus(422);
            $response->assertJson([
                'error' => 'last_admin_protection',
            ]);

            $this->admin->refresh();
            $this->assertEquals(0, $this->admin->user_status);
        } finally {
            foreach ($otherAdmins as $other) {
                User::where('id', $other->id)->update(['user_status' => $other->user_status]);
            }
            $tempAdmin->forceDelete();
        }
    }

    public function test_cannot_soft_delete_last_super_admin(): void
    {
        $this->admin->update(['user_status' => 2, 'user_role' => 0]); // Archived first

        // Office staff cannot delete super admin (403 from policy)
        Sanctum::actingAs($this->officeStaff);
        $response = $this->deleteJson("/api/users/{$this->admin->id}/soft-delete");
        $response->assertStatus(403);

        $this->admin->update(['user_status' => 0]);
    }

    public function test_super_admin_cannot_self_toggle_or_archive_or_delete(): void
    {
        Sanctum::actingAs($this->admin);

        // Self toggle status
        $responseToggle = $this->patchJson("/api/users/{$this->admin->id}/toggle-status");
        $responseToggle->assertStatus(403);

        // Self archive
        $responseArchive = $this->patchJson("/api/users/{$this->admin->id}/archive");
        $responseArchive->assertStatus(403);

        // Self soft delete
        $responseDelete = $this->deleteJson("/api/users/{$this->admin->id}/soft-delete");
        $responseDelete->assertStatus(403);
    }

    public function test_creating_super_admin_requires_current_password(): void
    {
        Sanctum::actingAs($this->admin);
        $uniqueEmail = 'new_admin_sudo_' . uniqid() . '@example.com';

        // Attempt without current_password
        $response = $this->postJson('/api/users', [
            'first_name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => $uniqueEmail,
            'user_role' => 0,
            'user_status' => 0,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'password_confirmation_required',
        ]);

        // Attempt with wrong password
        $responseWrong = $this->postJson('/api/users', [
            'first_name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => $uniqueEmail,
            'user_role' => 0,
            'user_status' => 0,
            'current_password' => 'WrongPassword123!',
        ]);

        $responseWrong->assertStatus(403);
        $responseWrong->assertJson([
            'error' => 'password_confirmation_required',
        ]);
    }

    public function test_creating_super_admin_with_valid_password_succeeds(): void
    {
        Sanctum::actingAs($this->admin);
        $uniqueEmail = 'new_admin_sudo_ok_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/users', [
            'first_name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => $uniqueEmail,
            'user_role' => 0,
            'user_status' => 0,
            'current_password' => 'Secret123!',
        ]);

        $response->assertStatus(201);

        $hmac = app(\App\Services\HmacHashService::class)->hash($uniqueEmail);
        $createdUser = User::where('email_hash', $hmac)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals(0, $createdUser->user_role);

        $createdUser->forceDelete();
    }

    public function test_promoting_user_to_super_admin_requires_current_password(): void
    {
        Sanctum::actingAs($this->admin);

        // Attempt promoting clinician to admin without current_password
        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 0,
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'password_confirmation_required',
        ]);

        $this->clinicianA->refresh();
        $this->assertEquals(3, $this->clinicianA->user_role);
    }

    public function test_promoting_user_to_super_admin_with_valid_password_succeeds(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/users/{$this->clinicianA->id}", [
            'user_role' => 0,
            'current_password' => 'Secret123!',
        ]);

        $response->assertStatus(200);

        $this->clinicianA->refresh();
        $this->assertEquals(0, $this->clinicianA->user_role);

        // Revert back to clinician
        $this->clinicianA->update(['user_role' => 3]);
    }

    public function test_soft_deleting_user_requires_current_password(): void
    {
        Sanctum::actingAs($this->admin);

        // Create a user to delete
        $targetUser = $this->findOrCreateTestUser('target_sudo_del_' . uniqid() . '@woundmed.com', [
            'first_name' => 'Target',
            'last_name' => 'Delete',
            'user_role' => 3,
            'user_status' => 2, // Archived
            'clinic_id' => $this->clinicA->clinic_id,
        ]);

        // Attempt delete without password
        $response = $this->deleteJson("/api/users/{$targetUser->id}/soft-delete");

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'password_confirmation_required',
        ]);

        // Attempt delete with valid password
        $responseSuccess = $this->deleteJson("/api/users/{$targetUser->id}/soft-delete", [
            'current_password' => 'Secret123!',
        ]);

        $responseSuccess->assertStatus(200);
        $this->assertTrue(User::withTrashed()->find($targetUser->id)->trashed());

        $targetUser->forceDelete();
    }

    public function test_manufacturer_cannot_access_clinician_listing(): void
    {
        $manufacturer = $this->findOrCreateTestUser('mfg_test_listing@woundmed.com', [
            'first_name' => 'Manufacturer',
            'last_name' => 'User',
            'user_role' => 4,
            'user_status' => 0,
        ]);

        Sanctum::actingAs($manufacturer);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized',
        ]);
    }

    public function test_biller_cannot_access_clinician_listing(): void
    {
        $biller = $this->findOrCreateTestUser('biller_test_listing@woundmed.com', [
            'first_name' => 'Biller',
            'last_name' => 'User',
            'user_role' => 5,
            'user_status' => 0,
        ]);

        Sanctum::actingAs($biller);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized',
        ]);
    }

    public function test_clinic_admin_without_assigned_clinic_cannot_access_clinician_listing(): void
    {
        $unassignedClinicAdmin = $this->findOrCreateTestUser('unassigned_admin@woundmed.com', [
            'first_name' => 'Unassigned',
            'last_name' => 'Admin',
            'user_role' => 2,
            'user_status' => 0,
            'clinic_id' => null,
        ]);

        Sanctum::actingAs($unassignedClinicAdmin);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unassigned_clinic_facility',
        ]);
    }

    public function test_clinician_listing_excludes_soft_deleted_clinicians(): void
    {
        $softDeletedClinician = $this->findOrCreateTestUser('deleted_clinician@woundmed.com', [
            'first_name' => 'Deleted',
            'last_name' => 'Doctor',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);
        $softDeletedClinician->delete(); // Soft delete

        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(200);

        $userData = $response->json('user_data');
        $found = collect($userData)->firstWhere('id', (string) $softDeletedClinician->id);
        $this->assertNull($found, 'Soft-deleted clinician must not appear in clinician listing');

        $softDeletedClinician->forceDelete();
    }

    public function test_manufacturer_cannot_access_audit_activity(): void
    {
        $manufacturer = $this->findOrCreateTestUser('mfg_test_act@woundmed.com', [
            'first_name' => 'Manufacturer',
            'last_name' => 'User',
            'user_role' => 4,
            'user_status' => 0,
        ]);

        Sanctum::actingAs($manufacturer);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized',
        ]);
    }

    public function test_biller_cannot_access_audit_activity(): void
    {
        $biller = $this->findOrCreateTestUser('biller_test_act@woundmed.com', [
            'first_name' => 'Biller',
            'last_name' => 'User',
            'user_role' => 5,
            'user_status' => 0,
        ]);

        Sanctum::actingAs($biller);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized',
        ]);
    }

    public function test_clinic_admin_without_assigned_clinic_cannot_access_audit_activity(): void
    {
        $unassignedClinicAdmin = $this->findOrCreateTestUser('unassigned_admin_act@woundmed.com', [
            'first_name' => 'Unassigned',
            'last_name' => 'AdminAct',
            'user_role' => 2,
            'user_status' => 0,
            'clinic_id' => null,
        ]);

        Sanctum::actingAs($unassignedClinicAdmin);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unassigned_clinic_facility',
        ]);
    }

    public function test_clinic_admin_audit_activity_is_scoped_to_own_clinic(): void
    {
        // Insert audit log for Clinician A (Clinic A)
        DB::table('woundmed_audit_logs')->insert([
            'user_id' => $this->clinicianA->id,
            'attempted_identifier' => (string) $this->clinicianA->id,
            'ip_address' => '127.0.0.1',
            'action_type' => 'test_action_clinic_a',
            'action_message' => 'Action by Clinician A in Clinic A',
            'entity_id' => $this->clinicianA->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'clinician',
            'status' => 0,
            'prev_hash' => null,
            'row_hash' => 'hash_test_a',
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Insert audit log for Clinician B (Clinic B)
        $clinicianB = $this->findOrCreateTestUser('clinician_b_act@woundmed.com', [
            'first_name' => 'Clinician',
            'last_name' => 'Beta',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicB->clinic_id,
        ]);

        DB::table('woundmed_audit_logs')->insert([
            'user_id' => $clinicianB->id,
            'attempted_identifier' => (string) $clinicianB->id,
            'ip_address' => '127.0.0.1',
            'action_type' => 'test_action_clinic_b',
            'action_message' => 'Action by Clinician B in Clinic B',
            'entity_id' => $clinicianB->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'clinician',
            'status' => 0,
            'prev_hash' => null,
            'row_hash' => 'hash_test_b',
            'timestamp' => now()->toDateTimeString(),
        ]);

        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // All returned logs must only belong to Clinic A users, never Clinician B from Clinic B
        foreach ($data as $log) {
            $this->assertNotEquals($clinicianB->id, $log['user_id']);
        }
    }

    public function test_clinician_audit_activity_is_scoped_to_self(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $log) {
            // Clinician can only see their own logs
            $this->assertEquals($this->clinicianA->id, $log['user_id']);
        }
    }
}


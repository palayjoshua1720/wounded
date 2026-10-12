<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkforceVisibilityAuditTest extends TestCase
{
    protected Clinic $clinicA;
    protected Clinic $clinicB;
    protected User $admin;
    protected User $officeStaff;
    protected User $clinicAdminA;
    protected User $clinicianA;
    protected User $clinicianB;

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
            'password' => Hash::make('Secret123456!'),
            'user_status' => 0,
        ], $attributes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinicA = Clinic::firstOrCreate(
            ['clinic_name' => 'Visibility Audit Clinic Alpha'],
            ['email' => 'audit_alpha@example.com', 'clinic_status' => 0]
        );

        $this->clinicB = Clinic::firstOrCreate(
            ['clinic_name' => 'Visibility Audit Clinic Beta'],
            ['email' => 'audit_beta@example.com', 'clinic_status' => 0]
        );

        $this->admin = $this->findOrCreateTestUser('admin_visibility@woundmed.com', [
            'first_name' => 'Super',
            'last_name' => 'AdminAuditor',
            'user_role' => 0,
            'user_status' => 0,
            'phone' => '1112223333',
        ]);

        $this->officeStaff = $this->findOrCreateTestUser('officestaff_visibility@woundmed.com', [
            'first_name' => 'Office',
            'last_name' => 'StaffAuditor',
            'user_role' => 1,
            'user_status' => 0,
            'phone' => '2223334444',
        ]);

        $this->clinicAdminA = $this->findOrCreateTestUser('clinicadmin_alpha@woundmed.com', [
            'first_name' => 'ClinicA',
            'last_name' => 'Director',
            'user_role' => 2,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
            'phone' => '3334445555',
        ]);

        $this->clinicianA = $this->findOrCreateTestUser('clinician_alpha@woundmed.com', [
            'first_name' => 'Clinician',
            'last_name' => 'Alpha',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
            'phone' => '5551234567',
        ]);

        $this->clinicianB = $this->findOrCreateTestUser('clinician_beta@woundmed.com', [
            'first_name' => 'Clinician',
            'last_name' => 'Beta',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicB->clinic_id,
            'phone' => '5559876543',
        ]);
    }

    public function test_user_listing_masks_phone_numbers_per_minimum_necessary(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users?search=' . urlencode('clinician_alpha@woundmed.com'));
        $response->assertStatus(200);

        $users = $response->json('users');
        $this->assertNotEmpty($users);

        $target = collect($users)->firstWhere('id', $this->clinicianA->id);
        $this->assertNotNull($target);
        // Phone must be masked with last 4 digits visible
        $this->assertEquals('***-***-4567', $target['phone']);
        $this->assertTrue($target['phoneMasked']);
    }

    public function test_inspecting_external_clinic_user_triggers_segregated_audit_log_and_unmasks_phone(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/users/{$this->clinicianA->id}");
        $response->assertStatus(200);

        // Assert unmasked phone number is provided to the inspector
        $this->assertEquals('5551234567', $response->json('user.phone'));
        $this->assertFalse($response->json('user.phoneMasked'));

        // Assert segregated audit log entry was created
        $auditLog = DB::table('woundmed_audit_logs')
            ->where('action_type', 'external_workforce_pii_viewed')
            ->where('entity_id', $this->clinicianA->id)
            ->latest('audit_log_id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->user_id);
        $this->assertStringContainsString('Visibility Audit Clinic Alpha', $auditLog->action_message);
        $this->assertStringContainsString('Clinician', $auditLog->action_message);
        $this->assertStringContainsString($this->clinicianA->full_name, $auditLog->action_message);
    }

    public function test_inspecting_internal_workforce_user_logs_internal_pii_audit(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/users/{$this->officeStaff->id}");
        $response->assertStatus(200);
        $this->assertEquals('2223334444', $response->json('user.phone'));

        $auditLog = DB::table('woundmed_audit_logs')
            ->where('action_type', 'workforce_pii_viewed')
            ->where('entity_id', $this->officeStaff->id)
            ->latest('audit_log_id')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->admin->id, $auditLog->user_id);
        $this->assertStringContainsString('Office Staff', $auditLog->action_message);
    }

    public function test_clinic_admin_cannot_inspect_clinician_from_another_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        // Attempting to inspect Clinician B from Clinic B
        $response = $this->getJson("/api/users/{$this->clinicianB->id}");
        $response->assertStatus(403);
    }

    public function test_clinic_admin_can_inspect_their_own_clinician_with_audit_trigger(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->getJson("/api/users/{$this->clinicianA->id}");
        $response->assertStatus(200);
        $this->assertEquals('5551234567', $response->json('user.phone'));

        $auditLog = DB::table('woundmed_audit_logs')
            ->where('action_type', 'external_workforce_pii_viewed')
            ->where('entity_id', $this->clinicianA->id)
            ->where('user_id', $this->clinicAdminA->id)
            ->latest('audit_log_id')
            ->first();

        $this->assertNotNull($auditLog);
    }

    public function test_office_staff_cannot_inspect_other_office_staff(): void
    {
        $otherOfficeStaff = $this->findOrCreateTestUser('officestaff_beta@woundmed.com', [
            'first_name' => 'Office',
            'last_name' => 'StaffBeta',
            'user_role' => 1,
            'user_status' => 0,
            'phone' => '2223335555',
        ]);

        Sanctum::actingAs($this->officeStaff);

        $response = $this->getJson("/api/users/{$otherOfficeStaff->id}");
        $response->assertStatus(403);
    }

    public function test_office_staff_cannot_inspect_super_admin(): void
    {
        Sanctum::actingAs($this->officeStaff);

        $response = $this->getJson("/api/users/{$this->admin->id}");
        $response->assertStatus(403);
    }

    public function test_office_staff_can_inspect_partner_facility_user_with_audit_trigger(): void
    {
        Sanctum::actingAs($this->officeStaff);

        $response = $this->getJson("/api/users/{$this->clinicianA->id}");
        $response->assertStatus(200);
        $this->assertEquals('5551234567', $response->json('user.phone'));

        $auditLog = DB::table('woundmed_audit_logs')
            ->where('action_type', 'external_workforce_pii_viewed')
            ->where('entity_id', $this->clinicianA->id)
            ->where('user_id', $this->officeStaff->id)
            ->latest('audit_log_id')
            ->first();

        $this->assertNotNull($auditLog);
    }
}

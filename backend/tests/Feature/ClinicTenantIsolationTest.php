<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicTenantIsolationTest extends TestCase
{
    protected Clinic $clinicA;
    protected Clinic $clinicB;
    protected User $admin;
    protected User $officeStaff;
    protected User $clinicAdminA;
    protected User $clinicianA;
    protected User $clinicianB;
    protected User $manufacturer;
    protected User $biller;

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
            ['clinic_name' => 'Isolation Clinic Alpha'],
            ['email' => 'alpha_tenant@example.com', 'clinic_status' => 0]
        );

        $this->clinicB = Clinic::firstOrCreate(
            ['clinic_name' => 'Isolation Clinic Beta'],
            ['email' => 'beta_tenant@example.com', 'clinic_status' => 0]
        );

        $this->admin = $this->findOrCreateTestUser('admin_iso@woundmed.com', [
            'first_name' => 'Super',
            'last_name' => 'AdminIso',
            'user_role' => 0,
            'user_status' => 0,
        ]);

        $this->officeStaff = $this->findOrCreateTestUser('officestaff_iso@woundmed.com', [
            'first_name' => 'Office',
            'last_name' => 'StaffIso',
            'user_role' => 1,
            'user_status' => 0,
        ]);

        $this->clinicAdminA = $this->findOrCreateTestUser('clinicadmin_iso_a@woundmed.com', [
            'first_name' => 'Admin',
            'last_name' => 'ClinicA',
            'user_role' => 2,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
        ]);

        $this->clinicianA = $this->findOrCreateTestUser('clinician_iso_a@woundmed.com', [
            'first_name' => 'Clinician',
            'last_name' => 'Alpha',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
            'phone' => '1234567890',
        ]);

        $this->clinicianB = $this->findOrCreateTestUser('clinician_iso_b@woundmed.com', [
            'first_name' => 'Clinician',
            'last_name' => 'Beta',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicB->clinic_id,
            'phone' => '9876543210',
        ]);

        $this->manufacturer = $this->findOrCreateTestUser('mfg_iso@woundmed.com', [
            'first_name' => 'Mfg',
            'last_name' => 'Partner',
            'user_role' => 4,
            'user_status' => 0,
        ]);

        $this->biller = $this->findOrCreateTestUser('biller_iso@woundmed.com', [
            'first_name' => 'Biller',
            'last_name' => 'Partner',
            'user_role' => 5,
            'user_status' => 0,
        ]);
    }

    public function test_clinic_admin_cannot_modify_different_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->postJson("/api/management/facilities/clinics/{$this->clinicB->clinic_id}/update", [
            'name' => 'Tampered Clinic Name',
            'address' => 'Hacked Street',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'cross_tenant_access_denied',
        ]);

        $this->clinicB->refresh();
        $this->assertEquals('Isolation Clinic Beta', $this->clinicB->clinic_name);
    }

    public function test_clinic_admin_can_update_own_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Isolation Clinic Alpha Updated',
            'phone' => '8889990000',
        ]);

        $response->assertStatus(200);

        $this->clinicA->refresh();
        $this->assertEquals('Isolation Clinic Alpha Updated', $this->clinicA->clinic_name);
        $this->assertEquals('8889990000', $this->clinicA->phone);

        // Revert
        $this->clinicA->update(['clinic_name' => 'Isolation Clinic Alpha']);
    }

    public function test_clinic_admin_cannot_assign_clinicians_from_another_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Isolation Clinic Alpha',
            'assigned_clinicians_id' => [$this->clinicianB->id], // Clinician from Clinic B
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'cross_tenant_clinician_assignment',
        ]);
    }

    public function test_clinic_admin_cannot_toggle_clinic_status(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/updatestatus");
        $response->assertStatus(403);
    }

    public function test_clinic_admin_cannot_delete_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/deleteclinic");
        $response->assertStatus(403);

        $this->assertFalse($this->clinicA->fresh()->trashed());
    }

    public function test_clinic_admin_cannot_archive_or_unarchive_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $responseArch = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/archiveclinic");
        $responseArch->assertStatus(403);

        $responseUnarch = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/unarchiveclinic");
        $responseUnarch->assertStatus(403);
    }

    public function test_clinic_admin_cannot_create_new_clinic(): void
    {
        Sanctum::actingAs($this->clinicAdminA);

        $response = $this->postJson('/api/management/facilities/clinics', [
            'name' => 'Rogue Clinic',
            'email' => 'rogue_' . uniqid() . '@example.com',
            'contactPerson' => 'Hacker',
        ]);

        $response->assertStatus(403);
    }

    public function test_clinician_cannot_modify_or_delete_or_create_clinics(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // Attempt update
        $resUpdate = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Doctor Modified',
        ]);
        $resUpdate->assertStatus(403);

        // Attempt create
        $resCreate = $this->postJson('/api/management/facilities/clinics', [
            'name' => 'Doctor Clinic',
            'email' => 'doc_' . uniqid() . '@example.com',
            'contactPerson' => 'Doctor',
        ]);
        $resCreate->assertStatus(403);

        // Attempt status update
        $resStatus = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/updatestatus");
        $resStatus->assertStatus(403);

        // Attempt delete
        $resDelete = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/deleteclinic");
        $resDelete->assertStatus(403);
    }

    public function test_clinician_cannot_access_administrative_clinic_directory(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // Clinicians are strictly barred from administrative clinic directories
        $response = $this->getJson('/api/management/users/clinics');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized',
        ]);

        // Clinicians may only access simplified clinician references for clinical dropdowns, scoped to own clinic
        $simpleClinicianRes = $this->getJson('/api/management/users/clinician?simple=true');
        $simpleClinicianRes->assertStatus(200);
        $clinicians = $simpleClinicianRes->json();
        $this->assertNotEmpty($clinicians);
        foreach ($clinicians as $clinician) {
            $this->assertEquals((string) $this->clinicianA->id, $clinician['id']);
            $this->assertNotEquals((string) $this->clinicianB->id, $clinician['id']);
        }
    }

    public function test_manufacturer_and_biller_cannot_access_clinic_directory_or_mutations(): void
    {
        // Manufacturer
        Sanctum::actingAs($this->manufacturer);
        $resMfg = $this->getJson('/api/management/users/clinics');
        $resMfg->assertStatus(403);

        $resMfgMod = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Mfg Tampering',
        ]);
        $resMfgMod->assertStatus(403);

        // Biller
        Sanctum::actingAs($this->biller);
        $resBiller = $this->getJson('/api/management/users/clinics');
        $resBiller->assertStatus(403);

        $resBillerMod = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Biller Tampering',
        ]);
        $resBillerMod->assertStatus(403);
    }

    public function test_super_admin_and_office_staff_can_manage_clinics(): void
    {
        // Office Staff updates clinic
        Sanctum::actingAs($this->officeStaff);

        $response = $this->postJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/update", [
            'name' => 'Isolation Clinic Alpha Managed',
        ]);
        $response->assertStatus(200);

        // Office Staff toggles clinic status
        $resStatus = $this->putJson("/api/management/facilities/clinics/{$this->clinicA->clinic_id}/updatestatus");
        $resStatus->assertStatus(200);

        // Revert status
        $this->clinicA->update(['clinic_name' => 'Isolation Clinic Alpha', 'clinic_status' => 0]);
    }
}

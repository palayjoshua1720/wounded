<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Clinic;
use App\Models\GraftSize;
use App\Models\IVR;
use App\Models\Manufacturer;
use App\Models\Orders;
use App\Models\OtherProduct;
use App\Models\PatientInfo;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClinicianSecurityAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected Clinic $clinicA;
    protected Clinic $clinicB;
    protected User $clinicianA;
    protected User $clinicianB;
    protected PatientInfo $patientA;
    protected PatientInfo $patientB;
    protected IVR $ivrA;
    protected IVR $ivrB;
    protected Orders $orderA;
    protected Orders $orderB;

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
            ['clinic_name' => 'Security Audit Clinic Alpha'],
            ['email' => 'audit_alpha@example.com', 'clinic_status' => 0]
        );

        $this->clinicB = Clinic::firstOrCreate(
            ['clinic_name' => 'Security Audit Clinic Beta'],
            ['email' => 'audit_beta@example.com', 'clinic_status' => 0]
        );

        $this->clinicianA = $this->findOrCreateTestUser('clinician_sec_a@woundmed.com', [
            'first_name' => 'Doctor',
            'last_name' => 'Alpha',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicA->clinic_id,
            'phone' => '5551112222',
        ]);

        $this->clinicianB = $this->findOrCreateTestUser('clinician_sec_b@woundmed.com', [
            'first_name' => 'Doctor',
            'last_name' => 'Beta',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinicB->clinic_id,
            'phone' => '5553334444',
        ]);

        $this->patientA = PatientInfo::firstOrCreate(
            ['email' => 'patient_sec_a@example.com'],
            [
                'patient_name' => 'Alice PatientAlpha',
                'clinic_id' => $this->clinicA->clinic_id,
                'assigned_clinician_id' => $this->clinicianA->id,
                'user_id' => $this->clinicianA->id,
            ]
        );

        $this->patientB = PatientInfo::firstOrCreate(
            ['email' => 'patient_sec_b@example.com'],
            [
                'patient_name' => 'Bob PatientBeta',
                'clinic_id' => $this->clinicB->clinic_id,
                'assigned_clinician_id' => $this->clinicianB->id,
                'user_id' => $this->clinicianB->id,
            ]
        );

        $manufacturer = Manufacturer::firstOrCreate(
            ['manufacturer_name' => 'Audit Manufacturer Corp'],
            [
                'primary_email' => 'mfg_corp@example.com',
                'order_email' => 'orders@mfgcorp.com',
                'eligibility_email' => 'eligibility@mfgcorp.com',
                'contact_person' => 'Rep John',
                'contact_number' => '5558889999',
                'manufacturer_status' => 0,
            ]
        );

        $this->ivrA = IVR::create([
            'ivr_number' => '#IVR-SECA-' . strtoupper(uniqid()),
            'clinic_id' => $this->clinicA->clinic_id,
            'patient_id' => $this->patientA->patient_id,
            'manufacturer_id' => $manufacturer->manufacturer_id,
            'eligibility_status' => 0,
            'submitted_at' => now(),
            'timestamp' => now(),
        ]);

        $this->ivrB = IVR::create([
            'ivr_number' => '#IVR-SECB-' . strtoupper(uniqid()),
            'clinic_id' => $this->clinicB->clinic_id,
            'patient_id' => $this->patientB->patient_id,
            'manufacturer_id' => $manufacturer->manufacturer_id,
            'eligibility_status' => 0,
            'submitted_at' => now(),
            'timestamp' => now(),
        ]);

        $this->orderA = Orders::create([
            'order_code' => 'ORD-SECA-' . strtoupper(uniqid()),
            'clinic_id' => $this->clinicA->clinic_id,
            'user_id' => $this->clinicianA->id,
            'patient_id' => $this->patientA->patient_id,
            'ivr_id' => $this->ivrA->ivr_id,
            'manufacturer_id' => $manufacturer->manufacturer_id,
            'tracking_num' => 'TRK-SECA-' . strtoupper(uniqid()),
            'order_status' => 0,
            'items' => [],
        ]);

        $this->orderB = Orders::create([
            'order_code' => 'ORD-SECB-' . strtoupper(uniqid()),
            'clinic_id' => $this->clinicB->clinic_id,
            'user_id' => $this->clinicianB->id,
            'patient_id' => $this->patientB->patient_id,
            'ivr_id' => $this->ivrB->ivr_id,
            'manufacturer_id' => $manufacturer->manufacturer_id,
            'tracking_num' => 'TRK-SECB-' . strtoupper(uniqid()),
            'order_status' => 0,
            'items' => [],
        ]);
    }

    public function test_clinician_cannot_view_administrative_clinician_directory(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->getJson('/api/management/users/clinician');
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'unauthorized_administrative_view',
        ]);
    }

    public function test_clinician_can_view_simple_clinician_directory_isolated_to_own_clinic(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->getJson('/api/management/users/clinician?simple=true');
        $response->assertStatus(200);

        $clinicians = $response->json();
        $this->assertNotEmpty($clinicians);

        $ids = array_column($clinicians, 'id');
        $this->assertContains((string) $this->clinicianA->id, $ids);
        $this->assertNotContains((string) $this->clinicianB->id, $ids);

        // Assert no sensitive PII fields are exposed in simple reference
        foreach ($clinicians as $clinician) {
            $this->assertArrayNotHasKey('email', $clinician);
            $this->assertArrayNotHasKey('phone', $clinician);
            $this->assertArrayNotHasKey('lastLogin', $clinician);
            $this->assertArrayNotHasKey('isActive', $clinician);
        }
    }

    public function test_clinician_cannot_create_clinician_account(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->postJson('/api/management/users/add/clinician', [
            'first_name' => 'Malicious',
            'last_name' => 'Account',
            'email' => 'malicious_' . uniqid() . '@example.com',
            'role' => 'Clinician',
        ]);

        $response->assertStatus(403);
    }

    public function test_clinician_cannot_access_user_management_module(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $this->getJson('/api/users')->assertStatus(403);
        $this->getJson('/api/users/stats')->assertStatus(403);
        $this->postJson('/api/users', [
            'email' => 'hacked_' . uniqid() . '@example.com',
        ])->assertStatus(403);
    }

    public function test_clinician_cannot_access_global_orders_directory(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->getJson('/api/management/order/getorders');
        $response->assertStatus(403);
    }

    public function test_clinician_cannot_execute_global_order_mutations(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $this->postJson('/api/management/order/add/neworder', [])->assertStatus(403);
        $this->putJson("/api/management/order/update/{$this->orderA->order_id}/updateorder", [])->assertStatus(403);
        $this->putJson("/api/management/order/delete/{$this->orderA->order_id}/deleteorder")->assertStatus(403);
        $this->putJson("/api/management/order/update/{$this->orderA->order_id}/updateorderstatus", [
            'order_status' => 2,
        ])->assertStatus(403);
    }

    public function test_clinician_cannot_inspect_cross_clinic_orders(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // Attempting to inspect Clinic B's order
        $resB = $this->getJson("/api/management/order/{$this->orderB->order_id}/getorderbyid");
        $resB->assertStatus(403);
        $resB->assertJson(['error' => 'cross_tenant_access_denied']);

        // Inspecting own clinic order succeeds
        $resA = $this->getJson("/api/management/order/{$this->orderA->order_id}/getorderbyid");
        $resA->assertStatus(200);
    }

    public function test_clinician_cannot_mutate_platform_catalogs(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // Manufacturers
        $this->postJson('/api/management/manufacturers', [])->assertStatus(403);
        $this->deleteJson('/api/management/manufacturers/1')->assertStatus(403);

        // Brands
        $this->postJson('/api/management/brands', [])->assertStatus(403);
        $this->deleteJson('/api/management/brands/1')->assertStatus(403);

        // Graft Sizes
        $this->postJson('/api/management/graft-sizes', [])->assertStatus(403);
        $this->putJson('/api/management/delete/1/deletegraftsize')->assertStatus(403);

        // Other Products
        $this->postJson('/api/management/other-products', [])->assertStatus(403);
        $this->deleteJson('/api/management/other-products/1')->assertStatus(403);
    }

    public function test_clinician_patient_phi_discovery_is_strictly_scoped(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // 1. /api/management/patients/patientinfo
        $ivrPatientRes = $this->getJson('/api/management/patients/patientinfo');
        $ivrPatientRes->assertStatus(200);
        $ivrPatients = $ivrPatientRes->json('patient_data');
        $patientIds = array_column($ivrPatients, 'patient_id');
        $this->assertContains($this->patientA->patient_id, $patientIds);
        $this->assertNotContains($this->patientB->patient_id, $patientIds);

        // 2. /api/management/order/users/getpatients
        $orderPatientRes = $this->getJson('/api/management/order/users/getpatients');
        $orderPatientRes->assertStatus(200);
        $orderPatients = $orderPatientRes->json('patient_data');
        $orderPatientIds = array_column($orderPatients, 'patient_id');
        $this->assertContains($this->patientA->patient_id, $orderPatientIds);
        $this->assertNotContains($this->patientB->patient_id, $orderPatientIds);

        // 3. /api/management/order/getclinics
        $clinicsRes = $this->getJson('/api/management/order/getclinics');
        $clinicsRes->assertStatus(200);
        $clinics = $clinicsRes->json('clinic_data');
        $clinicIds = array_column($clinics, 'clinic_id');
        $this->assertContains($this->clinicA->clinic_id, $clinicIds);
        $this->assertNotContains($this->clinicB->clinic_id, $clinicIds);
    }

    public function test_clinician_cannot_override_ivr_eligibility_status(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->putJson("/api/management/update/{$this->ivrA->ivr_id}/updateivreligiblity", [
            'eligibility_status' => 1,
        ]);

        $response->assertStatus(403);
        $response->assertJson(['error' => 'unauthorized']);
    }

    public function test_clinician_cannot_mutate_or_archive_cross_clinic_ivr(): void
    {
        Sanctum::actingAs($this->clinicianA);

        // Attempt update of Clinic B IVR
        $resUpdate = $this->postJson("/api/management/update/{$this->ivrB->ivr_id}/updateivr", [
            'patient_id' => $this->patientB->patient_id,
            'manufacturer_id' => 1,
        ]);
        $resUpdate->assertStatus(403);
        $resUpdate->assertJson(['error' => 'cross_tenant_access_denied']);

        // Attempt archive of Clinic B IVR
        $resArchive = $this->putJson("/api/management/archive/{$this->ivrB->ivr_id}/archiveivrrequest");
        $resArchive->assertStatus(403);
        $resArchive->assertJson(['error' => 'cross_tenant_access_denied']);

        // Attempt delete IVR
        $resDelete = $this->putJson("/api/management/delete/{$this->ivrA->ivr_id}/deleteivrrequest");
        $resDelete->assertStatus(403);
    }

    public function test_clinician_can_view_own_audit_activity(): void
    {
        Sanctum::actingAs($this->clinicianA);

        $response = $this->getJson('/api/utility/activity/clinician');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data',
        ]);
    }
}

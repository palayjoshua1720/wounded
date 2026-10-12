<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InactiveArchivedUserAuthTest extends TestCase
{
    protected Clinic $clinic;
    protected User $admin;
    protected User $testUser;

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

        $this->clinic = Clinic::firstOrCreate(
            ['clinic_name' => 'Security Auth Test Clinic'],
            ['email' => 'auth_test_clinic@example.com', 'clinic_status' => 0]
        );

        $this->admin = $this->findOrCreateTestUser('admin_authtest@woundmed.com', [
            'first_name' => 'Admin',
            'last_name' => 'Tester',
            'user_role' => 0,
            'user_status' => 0,
        ]);

        $this->testUser = $this->findOrCreateTestUser('clinician_authtest@woundmed.com', [
            'first_name' => 'Target',
            'last_name' => 'User',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinic->clinic_id,
        ]);
    }

    public function test_active_user_can_authenticate(): void
    {
        $this->testUser->update(['user_status' => 0]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'clinician_authtest@woundmed.com',
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $this->testUser->update(['user_status' => 1]); // Inactive

        $response = $this->postJson('/api/auth/login', [
            'email' => 'clinician_authtest@woundmed.com',
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_inactive',
        ]);
    }

    public function test_archived_user_cannot_authenticate(): void
    {
        $this->testUser->update(['user_status' => 2]); // Archived

        $response = $this->postJson('/api/auth/login', [
            'email' => 'clinician_authtest@woundmed.com',
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_inactive',
        ]);
    }

    public function test_soft_deleted_user_cannot_authenticate(): void
    {
        $this->testUser->delete();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'clinician_authtest@woundmed.com',
            'password' => 'Secret123!',
        ]);

        // When soft-deleted, User::where('email_hash', ...) does not return trashed models unless withTrashed()
        // so it fails with 401, or if resolved, 403
        $this->assertTrue(in_array($response->status(), [401, 403], true));

        $this->testUser->restore();
    }

    public function test_existing_tokens_are_revoked_on_toggle_status_to_inactive(): void
    {
        $this->testUser->update(['user_status' => 0]);
        $token = $this->testUser->createToken('test_bearer')->plainTextToken;

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
        ]);

        // Admin deactivates target user
        Sanctum::actingAs($this->admin);
        $toggleResponse = $this->patchJson("/api/users/{$this->testUser->id}/toggle-status");
        $toggleResponse->assertStatus(200);

        $this->testUser->refresh();
        $this->assertEquals(1, $this->testUser->user_status);

        // Verify tokens are deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
        ]);

        // Clear actingAs state so Bearer token authentication takes effect
        $this->app['auth']->forgetGuards();

        // Request with the old token must fail
        $reqResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');
        $this->assertTrue(in_array($reqResponse->status(), [401, 403], true));
    }

    public function test_existing_tokens_are_revoked_on_archive(): void
    {
        $this->testUser->update(['user_status' => 0]);
        $token = $this->testUser->createToken('test_bearer_arch')->plainTextToken;

        Sanctum::actingAs($this->admin);
        $archiveResponse = $this->patchJson("/api/users/{$this->testUser->id}/archive");
        $archiveResponse->assertStatus(200);

        $this->testUser->refresh();
        $this->assertEquals(2, $this->testUser->user_status);

        // Tokens revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
        ]);

        // Clear actingAs state so Bearer token authentication takes effect
        $this->app['auth']->forgetGuards();

        // Request with old token must fail
        $reqResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');
        $this->assertTrue(in_array($reqResponse->status(), [401, 403], true));
    }

    public function test_ensure_user_is_active_middleware_blocks_inactive_user_with_token(): void
    {
        $this->testUser->update(['user_status' => 0]);
        $token = $this->testUser->createToken('test_bearer_manual')->plainTextToken;

        // Manually set status to inactive directly in DB (simulating an external or concurrent update)
        \Illuminate\Support\Facades\DB::table('woundmed_users')
            ->where('id', $this->testUser->id)
            ->update(['user_status' => 1]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_inactive',
        ]);

        // Tokens should have been deleted by the middleware
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
        ]);
    }

    public function test_inactive_user_cannot_verify_login_code(): void
    {
        $this->testUser->update(['user_status' => 1]);

        $response = $this->postJson('/api/auth/login/verify-code', [
            'user_id' => $this->testUser->id,
            'code' => '123456',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_inactive',
        ]);
    }

    public function test_inactive_user_cannot_validate_2fa(): void
    {
        $this->testUser->update([
            'user_status' => 1,
            'tfa_enabled' => 1,
            'tfa_secret' => '1234',
        ]);

        $response = $this->postJson('/api/auth/validation/validate-tfauth', [
            'email' => 'clinician_authtest@woundmed.com',
            'pinBoxes' => '1234',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'account_inactive',
        ]);
    }

    public function test_existing_tokens_are_revoked_on_soft_delete(): void
    {
        $this->testUser->update(['user_status' => 2]); // Must be archived first to soft delete
        $token = $this->testUser->createToken('test_bearer_del')->plainTextToken;

        Sanctum::actingAs($this->admin);
        $delResponse = $this->deleteJson("/api/users/{$this->testUser->id}/soft-delete", [
            'current_password' => 'Secret123!',
        ]);
        $delResponse->assertStatus(200);

        // Tokens revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->testUser->id,
        ]);

        $this->app['auth']->forgetGuards();

        $reqResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');
        $this->assertTrue(in_array($reqResponse->status(), [401, 403], true));

        $this->testUser->restore();
    }

    public function test_inactive_user_cannot_execute_password_reset(): void
    {
        $this->testUser->update(['user_status' => 1]);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'dummy_token',
            'email' => 'clinician_authtest@woundmed.com',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}

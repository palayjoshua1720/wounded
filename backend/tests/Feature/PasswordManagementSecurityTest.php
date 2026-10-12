<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\HmacHashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PasswordManagementSecurityTest extends TestCase
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
            'password' => Hash::make('Secret123456!'),
            'user_status' => 0,
        ], $attributes));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = Clinic::firstOrCreate(
            ['clinic_name' => 'Password Security Test Clinic'],
            ['email' => 'pwd_sec_clinic@example.com', 'clinic_status' => 0]
        );

        $this->admin = $this->findOrCreateTestUser('admin_pwdtest@woundmed.com', [
            'first_name' => 'Admin',
            'last_name' => 'SecurityTester',
            'user_role' => 0,
            'user_status' => 0,
        ]);

        $this->testUser = $this->findOrCreateTestUser('user_pwdtest@woundmed.com', [
            'first_name' => 'Target',
            'last_name' => 'SecurityUser',
            'user_role' => 3,
            'user_status' => 0,
            'clinic_id' => $this->clinic->clinic_id,
        ]);
    }

    public function test_admin_cannot_directly_overwrite_another_users_password(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/users/{$this->testUser->id}", [
            'password' => 'NewCompPwd123!',
            'password_confirmation' => 'NewCompPwd123!',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'direct_password_modification_prohibited',
        ]);
    }

    public function test_self_password_update_requires_valid_current_password(): void
    {
        Sanctum::actingAs($this->admin);

        // Without current_password
        $response1 = $this->putJson("/api/users/{$this->admin->id}", [
            'password' => 'NewAdminPass123!',
            'password_confirmation' => 'NewAdminPass123!',
        ]);

        $response1->assertStatus(403);
        $response1->assertJson([
            'error' => 'password_confirmation_required',
        ]);

        // With wrong current_password
        $response2 = $this->putJson("/api/users/{$this->admin->id}", [
            'current_password' => 'WrongPassword!',
            'password' => 'NewAdminPass123!',
            'password_confirmation' => 'NewAdminPass123!',
        ]);

        $response2->assertStatus(403);
        $response2->assertJson([
            'error' => 'password_confirmation_required',
        ]);

        // With correct current_password
        $response3 = $this->putJson("/api/users/{$this->admin->id}", [
            'current_password' => 'Secret123456!',
            'password' => 'NewAdminPass123!',
            'password_confirmation' => 'NewAdminPass123!',
        ]);

        $response3->assertStatus(200);
        $this->assertTrue(Hash::check('NewAdminPass123!', $this->admin->fresh()->password));

        // Restore admin password
        $this->admin->update(['password' => Hash::make('Secret123456!')]);
    }

    public function test_admin_can_dispatch_password_reset_link_to_active_user(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/users/{$this->testUser->id}/send-reset-link");

        $response->assertStatus(200);
        $response->assertJsonStructure(['message']);

        // Assert token was stored in password_reset_tokens table
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->testUser->email,
        ]);
    }

    public function test_admin_cannot_dispatch_password_reset_link_to_inactive_or_archived_user(): void
    {
        Sanctum::actingAs($this->admin);

        // Inactive user
        $this->testUser->update(['user_status' => 1]);

        $responseInactive = $this->postJson("/api/users/{$this->testUser->id}/send-reset-link");
        $responseInactive->assertStatus(422);
        $responseInactive->assertJson([
            'error' => 'account_inactive',
        ]);

        // Archived user
        $this->testUser->update(['user_status' => 2]);

        $responseArchived = $this->postJson("/api/users/{$this->testUser->id}/send-reset-link");
        // UserPolicy blocks updating archived users with 403
        $this->assertTrue(in_array($responseArchived->status(), [403, 422], true));

        $this->testUser->update(['user_status' => 0]);
    }

    public function test_password_reset_enforces_complexity_and_length(): void
    {
        // Weak password: less than 12 characters
        $response = $this->postJson('/api/reset-password', [
            'token' => 'dummy_token',
            'email' => $this->testUser->email,
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        // Weak password: 12 chars but no symbols
        $responseNoSymbol = $this->postJson('/api/reset-password', [
            'token' => 'dummy_token',
            'email' => $this->testUser->email,
            'password' => 'NoSymbols123456',
            'password_confirmation' => 'NoSymbols123456',
        ]);

        $responseNoSymbol->assertStatus(422);
        $responseNoSymbol->assertJsonValidationErrors(['password']);
    }

    public function test_successful_password_reset_revokes_all_active_tokens(): void
    {
        $this->testUser->update(['user_status' => 0]);

        // Issue tokens to testUser
        $token1 = $this->testUser->createToken('session_1')->plainTextToken;
        $token2 = $this->testUser->createToken('session_2')->plainTextToken;

        $this->assertEquals(2, $this->testUser->tokens()->count());

        // Generate valid reset token
        $resetToken = Password::createToken($this->testUser);

        // Execute reset
        $response = $this->postJson('/api/reset-password', [
            'token' => $resetToken,
            'email' => $this->testUser->email,
            'password' => 'ValidComplexPwd123!',
            'password_confirmation' => 'ValidComplexPwd123!',
        ]);

        $response->assertStatus(200);

        // Assert all user tokens revoked
        $this->assertEquals(0, $this->testUser->tokens()->count());

        // Assert reset token cleared from table
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $this->testUser->email,
        ]);

        // Assert password was actually updated
        $this->assertTrue(Hash::check('ValidComplexPwd123!', $this->testUser->fresh()->password));
    }
}

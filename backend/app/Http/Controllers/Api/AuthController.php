<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash as LaravelHash;
use App\Services\VerificationCodeService;
use App\Traits\AuditLogger;

class AuthController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_users';
    }

    protected function getEntityType()
    {
        return 'user';
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $prevHash = $this->getLastRowHash();

        if (! $user) {
            $log = [
                'user_id' => null,
                'attempted_identifier' => $request->email,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'login failed',
                'entity_id' => null,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            // Return response indicating invalid credentials
            return response()->json([
                'message' => 'Invalid credentials.',
                'requires_verification' => false,
            ], 401);
        }
        
        if (! Hash::check($request->password, $user->password)) {
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => $request->email,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'login failed',
                'entity_id' => $user?->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            return response()->json([
                'message' => 'Invalid password.',
            ], 401);
        }

        // Check if user has enabled one-time email verification
        if ($user->one_time_email_verification) {
            // Generate and send verification code
            $verificationCodeService = new VerificationCodeService();
            $verificationCode = $verificationCodeService->sendVerificationCode($user);

            // Log that verification code was sent
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'verification code sent for one-time email verification',
                'entity_id' => $user->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 0,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            // Return response indicating verification code is required
            return response()->json([
                'message' => 'Verification code sent to your email. Please check your inbox.',
                'requires_verification' => true,
                'user_id' => $user->id,
                'email' => $user->email,
            ], 200);
        }
        
        // Check if user has enabled TFA
        if ($user->tfa_enabled) {
            // Return response indicating TFA code is required
            return response()->json([
                'message' => 'Two-factor authentication code required for login. Please enter your TFA code.',
                'requires_tfa' => true,
                'user_id' => $user->id,
            ], 200);
        }
        
        // Check if user has enabled backup codes but not other verification methods
        if ($user->backup_codes_enabled && !$user->one_time_email_verification && !$user->tfa_enabled) {
            // Return response indicating backup code is required
            return response()->json([
                'message' => 'Backup code required for login. Please enter one of your backup codes.',
                'requires_backup_code' => true,
                'user_id' => $user->id,
            ], 200);
        }

        // If no verification required, proceed with normal login
        $user->last_logged_in = now();
        $user->save();

        // $token = $user->createToken('auth_token')->plainTextToken;
        $token = $user->createToken(
            'auth_token',
            ['*'],
            now()->addHours(4)
        )->plainTextToken;

        $log = [
            'user_id' => $user->id,
            'attempted_identifier' => null,
            'ip_address'=> $ip,
            'action_type' => 'login',
            'action_message' => 'login success',
            'entity_id' => $user->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'authentication',
            'status' => 0,
            'timestamp' => now(),
        ];
        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Verify the one-time email verification code and complete login
     */
    public function verifyLoginCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:woundmed_users,id',
            'code' => 'required|string|size:6',
        ]);

        $user = User::find($request->user_id);
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $prevHash = $this->getLastRowHash();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Verify the code using the service
        $verificationCodeService = new VerificationCodeService();
        $isValid = $verificationCodeService->verifyCode($user, $request->code);

        if (!$isValid) {
            // Log failed verification attempt
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'verification code failed',
                'entity_id' => $user->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 400);
        }

        // Code is valid, proceed with login
        $user->last_logged_in = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        $log = [
            'user_id' => $user->id,
            'attempted_identifier' => null,
            'ip_address'=> $ip,
            'action_type' => 'login',
            'action_message' => 'login success after code verification',
            'entity_id' => $user->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'authentication',
            'status' => 0,
            'timestamp' => now(),
        ];
        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Enable Two Factor Authentation
     * --
     * handles the logic for enabling the tfa in the db
     */
    public function enable_tfauth(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        $request->validate([
            'pin' => 'required|string|size:4'
        ]);

        $user->tfa_enabled = 1;
        $user->tfa_secret = $request->pin;
        $user->save();

        $this->logAudit($request, 'security', "2FA enabled", $user->id);

        return response()->json([
            'message' => 'Two-Factor Authentication is now enabled.',
            'tfa_enabled' => $user->tfa_enabled
        ]);
    }

    /**
     * Disable Two Factor Authentation
     * --
     * handles the logic for disabling the tfa in the db
     */
    public function disable_tfauth(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        $user->tfa_enabled = 0;
        $user->tfa_secret = null;
        $user->save();

        $this->logAudit($request, 'security', "2FA disabled", $user->id);

        return response()->json([
            'message' => 'Two-Factor Authentication has been disabled.'
        ]);
    }

    public function update_tfauth(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $user = $request->user();
        if (!$user || !$user->tfa_enabled) {
            return response()->json([
                'message' => '2FA is not enabled for this user.'
            ], 400);
        }

        $user->tfa_secret = $request->pin;
        $user->save();

        $this->logAudit($request, 'security', "2FA updated", $user->id);

        return response()->json([
            'message' => 'Your 2FA PIN has been successfully updated.'
        ]);
    }

    /**
     * Validate Two Factor Authentication During Login
     * --
     * handles the logic for tfa validation and checking during login process
     * when tfa is enabled on the account
     */
    public function validate_tfauth(Request $request)
    {
        $request->validate([
            'pinBoxes' => ['required', 'digits:4'],
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (!$user->tfa_enabled) {
            return response()->json([
                'message' => '2FA is not enabled for this user.'
            ], 403);
        }

        if ($user->tfa_secret !== $request->pinBoxes) {

            $this->logAudit($request, 'authentication-2fa', "Invalid 2FA code", $user->id, 1);

            return response()->json([
                'message' => 'Invalid 2FA code.'
            ], 401);
        }

        $this->logAudit($request, 'authentication-2fa', "2FA verification successful", $user->id);

        # Passed 2FA
        return response()->json([
            'message' => '2FA verification successful.',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ]);
    }


    public function user(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'first_name'                  => $user->first_name,
            'middle_name'                 => $user->middle_name,
            'last_name'                   => $user->last_name,
            'name'                        => $user->full_name,
            'email'                       => $user->email,
            'phone'                       => $user->phone,
            'user_role'                   => $user->user_role,
            'user_status'                 => $user->user_status,
            'clinic_id'                   => $user->clinic_id,
            'manufacturer_id'             => $user->manufacturer_id,
            'tfa_enabled'                 => $user->tfa_enabled,
            'one_time_email_verification' => $user->one_time_email_verification,
            'backup_codes_enabled'        => $user->backup_codes_enabled,
            'created_at'                  => $user->created_at, 
            'updated_at'                  => $user->updated_at,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $prevHash = $this->getLastRowHash();

        $log = [
            'user_id'               => $user->id,
            'attempted_identifier'  => null,
            'ip_address'            => $ip,
            'action_type'           => 'logout',
            'action_message'        => 'logout',
            'entity_id'             => $user->id,
            'entity'                => 'woundmed_users',
            'entity_type'           => 'authentication',
            'status'                => 0,
            'timestamp'             => now(),
        ];

        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);
        
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Enable One-Time Email Verification
     * --
     * handles the logic for enabling the one-time email verification in the db
     */
    public function enableOneTimeEmailVerification(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        $user->one_time_email_verification = 1;
        $user->save();

        return response()->json([
            'message' => 'One-Time Email Verification is now enabled.',
            'one_time_email_verification' => $user->one_time_email_verification
        ]);
    }

    /**
     * Disable One-Time Email Verification
     * --
     * handles the logic for disabling the one-time email verification in the db
     */
    public function disableOneTimeEmailVerification(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        $user->one_time_email_verification = 0;
        $user->save();

        return response()->json([
            'message' => 'One-Time Email Verification has been disabled.',
            'one_time_email_verification' => $user->one_time_email_verification
        ]);
    }

    /**
     * Enable Backup Codes
     * --
     * handles the logic for enabling backup codes in the db
     */
    public function enableBackupCodes(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Generate 6 backup codes
        $backupCodes = [];
        for ($i = 0; $i < 6; $i++) {
            $backupCodes[] = [
                'user_id' => $user->id,
                'code' => strtoupper(bin2hex(random_bytes(4))), // 8 character hex code
                'used' => false,
                'expires_at' => null, // No expiration for backup codes
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Delete existing backup codes for this user
        \App\Models\BackupCode::where('user_id', $user->id)->delete();

        // Insert new backup codes
        \App\Models\BackupCode::insert($backupCodes);

        // Enable backup codes for the user
        $user->backup_codes_enabled = true;
        $user->save();

        // Return the generated codes
        $codes = array_column($backupCodes, 'code');

        return response()->json([
            'message' => 'Backup codes have been generated and enabled.',
            'backup_codes_enabled' => $user->backup_codes_enabled,
            'backup_codes' => $codes
        ]);
    }

    /**
     * Disable Backup Codes
     * --
     * handles the logic for disabling backup codes in the db
     */
    public function disableBackupCodes(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Delete all backup codes for this user
        \App\Models\BackupCode::where('user_id', $user->id)->delete();

        // Disable backup codes for the user
        $user->backup_codes_enabled = false;
        $user->save();

        return response()->json([
            'message' => 'Backup codes have been disabled and all codes have been deleted.',
            'backup_codes_enabled' => $user->backup_codes_enabled
        ]);
    }

    /**
     * Verify Backup Code
     * --
     * handles the logic for verifying backup codes during login
     */
    public function verifyBackupCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:woundmed_users,id',
            'code' => 'required|string|min:8|max:8', // 8 character hex code
        ]);

        $user = \App\Models\User::find($request->user_id);
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $prevHash = $this->getLastRowHash();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Find the backup code for this user
        $backupCode = \App\Models\BackupCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->valid()
            ->first();

        if (!$backupCode) {
            // Log failed verification attempt
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'backup code verification failed',
                'entity_id' => $user->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            return response()->json([
                'message' => 'Invalid backup code.',
            ], 400);
        }

        // Mark the backup code as used
        $backupCode->update(['used' => true]);

        // Code is valid, proceed with login
        $user->last_logged_in = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        $log = [
            'user_id' => $user->id,
            'attempted_identifier' => null,
            'ip_address'=> $ip,
            'action_type' => 'login',
            'action_message' => 'login success after backup code verification',
            'entity_id' => $user->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'authentication',
            'status' => 0,
            'timestamp' => now(),
        ];
        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Get User's Backup Codes
     * --
     * returns the user's backup codes if they have them enabled
     */
    public function getUserBackupCodes(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // Only return backup codes if backup codes are enabled for the user
        if (!$user->backup_codes_enabled) {
            return response()->json([
                'message' => 'Backup codes are not enabled for this user.',
                'backup_codes' => []
            ]);
        }

        // Fetch the user's backup codes
        $backupCodes = $user->backupCodes()->get();

        return response()->json([
            'backup_codes' => $backupCodes->pluck('code')->toArray(),
            'count' => $backupCodes->count()
        ]);
    }

    private function generateRowHash(array $data, $prevHash = null)
    {
        $string = json_encode($data) . $prevHash;
        return hash('sha256', $string);
    }

    private function getLastRowHash()
    {
        $last = DB::table('woundmed_audit_logs')
            ->select('row_hash')
            ->latest('audit_log_id')
            ->first();
        return $last?->row_hash ?? null;
    }

    # future use
    public function verifyAuditChain()
    {
        $logs = DB::table('woundmed_audit_logs')
            ->orderBy('timestamp', 'asc')
            ->get();

        $previousHash = null;
        $issues = [];

        foreach ($logs as $log) {
            $data = $log->user_id . $log->attempted_identifier . $log->ip_address .
                    $log->action_type . $log->action_message . $log->entity_id .
                    $log->entity . $log->entity_type . $log->status .
                    $log->prev_hash . $log->timestamp;

            $calculatedHash = hash('sha256', $data);

            if ($calculatedHash !== $log->row_hash) {
                $issues[] = "Row {$log->audit_log_id} has been modified";
            }

            if ($previousHash !== null && $log->prev_hash !== $previousHash) {
                $issues[] = "Chain broken at row {$log->audit_log_id}";
            }

            $previousHash = $log->row_hash;
        }

        return $issues ?: ['status' => 'Chain verified successfully'];
    }

}

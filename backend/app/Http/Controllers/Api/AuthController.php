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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $user = User::where('email', $request->email)->first();
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

            return response()->json([
                'message' => 'The provided credentials are incorrect.',
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
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $user->last_logged_in = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

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

        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $user = User::where('email', $request->email)->first();
        $prevHash = $this->getLastRowHash();

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
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address'=> $ip,
                'action_type' => 'login',
                'action_message' => 'Invalid 2FA code',
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
                'message' => 'Invalid 2FA code.'
            ], 401);
        }

        $log = [
            'user_id' => $user->id,
            'attempted_identifier' => null,
            'ip_address'=> $ip,
            'action_type' => 'login',
            'action_message' => '2FA verification successful',
            'entity_id' => $user->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'authentication',
            'status' => 0,
            'timestamp' => now(),
        ];
        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);

        # Passed 2FA
        return response()->json([
            'message' => '2FA verification successful.',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ]);
    }


    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $user = $request->user();
        $prevHash = $this->getLastRowHash();

        $log = [
            'user_id' => $user->id,
            'attempted_identifier' => null,
            'ip_address'=> $ip,
            'action_type' => 'logout',
            'action_message' => 'logout',
            'entity_id' => $user->id,
            'entity' => 'woundmed_users',
            'entity_type' => 'authentication',
            'status' => 0,
            'timestamp' => now(),
        ];
        $log['prev_hash'] = $prevHash;
        $log['row_hash'] = $this->generateRowHash($log, $prevHash);

        DB::table('woundmed_audit_logs')->insert($log);

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
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

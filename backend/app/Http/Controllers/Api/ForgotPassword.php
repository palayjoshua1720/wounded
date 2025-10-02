<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Services\EmailService;
use App\Template\ForgotPasswordEmail;
use Illuminate\Support\Facades\DB;

class ForgotPassword extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // $user = User::where('email', $request->email)->first();
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $user = User::where('email', $request->email)->first();
        $prevHash = $this->getLastRowHash();


        if (!$user) {
            $log = [
                'user_id' => null, 
                'attempted_identifier' => $request->email,
                'ip_address' => $ip,
                'action_type' => 'forgot_password_request',
                'action_message' => 'password reset requested failed',
                'entity_id' => null,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1, // failed
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);

            return response()->json(['message' => 'If an account exists, you’ll get an email'], 200);
        }

        $token = Password::createToken($user);

        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        $emailBody = ForgotPasswordEmail::getTemplate($resetUrl);

        $emailService = new EmailService();
        $params = [
            'to'        => $user->email,
            'from'      => 'noreply@woundmed.com',
            'from_name' => 'WoundMed Support',
            'subject'   => 'Password Reset Request',
            'body'      => $emailBody,
        ];

        $response = $emailService->send_email($params, 'If an account exists, you’ll get an email', 'If an account exists, you’ll get an email');

        // AUDTI LOGS ///
        // === AUDIT LOG ===
        if ($response) {
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address' => $ip,
                'action_type' => 'forgot_password_request',
                'action_message' => 'password reset email sent',
                'entity_id' => $user->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 0, // success
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);
        }

        return response()->json([
            'message' => 'If an account exists, you’ll get an email',
            'status'  => $response,
        ], 200);
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
}

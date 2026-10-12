<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\HmacHashService;

class ResetPassword extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $email = $request->input('email');
        $token = $request->input('token');

        $emailHash = app(HmacHashService::class)->hash($email);
        $user = \App\Models\User::where('email_hash', $emailHash)->first();

        if (!$user) {
            $user = \App\Models\User::whereNull('email_hash')->get()
                ->first(fn($u) => $u->email === $email);
        }

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We can\'t find a user with that email address.'],
            ]);
        }

        if ((int) $user->user_status !== 0 || $user->trashed()) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive or archived. Password reset is not permitted.'],
            ]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => ['This password reset token is invalid.'],
            ]);
        }

        if (!Hash::check($token, $record->token)) {
            throw ValidationException::withMessages([
                'email' => ['This password reset token is invalid.'],
            ]);
        }

        $createdAt = strtotime($record->created_at);
        $expireMinutes = config('auth.passwords.users.expire', 60);
        if ($createdAt && (time() - $createdAt) > ($expireMinutes * 60)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            throw ValidationException::withMessages([
                'email' => ['This password reset token has expired.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
            'force_password_change' => false,
        ])->save();

        // Enforce HIPAA § 164.312(a)(2)(i): Invalidate all active tokens on password change
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }
}

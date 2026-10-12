<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * Enforce HIPAA § 164.308(a)(3)(ii)(C) (Termination Procedures) and § 164.312(a)(1) (Access Control):
     * Deactivated, archived, or soft-deleted users must have all access immediately revoked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ((int) $user->user_status !== 0 || $user->trashed())) {
            // Revoke all existing tokens for the inactive/archived/deleted user
            $user->tokens()->delete();

            $statusLabel = match ((int) $user->user_status) {
                1 => 'inactive',
                2 => 'archived',
                default => 'suspended',
            };
            if ($user->trashed()) {
                $statusLabel = 'deleted';
            }

            return response()->json([
                'message' => "Your account is {$statusLabel}. Access revoked.",
                'error' => 'account_inactive',
            ], 403);
        }

        return $next($request);
    }
}

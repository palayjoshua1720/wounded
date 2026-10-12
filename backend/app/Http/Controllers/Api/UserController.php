<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use App\Policies\UserPolicy;
use App\Services\EmailService;
use App\Services\HmacHashService;
use App\Template\WelcomeEmail;
use App\Template\ForgotPasswordEmail;
use App\Traits\AuditLogger;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
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
    /**
     * Display a listing of users
     * 
     * Note: first_name, last_name, and email are encrypted fields.
     * Search must be performed in-memory after decryption.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $currentUser = $request->user();

        // Enforce role isolation: only Admin (0), Office Staff (1), and Clinic Admin (2) may view user listings
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view user management data.',
                'error' => 'unauthorized'
            ], 403);
        }

        $search = $request->search ?? '';
        $hasSearch = !empty($search);
        
        // Build base query with non-encrypted filters
        $query = User::with(['clinic', 'manufacturer'])
            ->select([
                'id',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'user_role',
                'user_status',
                'clinic_id',
                'manufacturer_id',
                'created_at',
                'updated_at',
                'phone'
            ]);

        // Hide internal workforce accounts (Super Admin 0 and Office Staff 1) from office staff
        if ($currentUser && $currentUser->user_role === 1) {
            $query->whereNotIn('user_role', [0, 1]);
        }
        
        // Clinic users can only see clinicians from their own clinic
        if ($currentUser && $currentUser->user_role === 2) {
            $query->where('user_role', 3) // Only clinicians
                  ->where('clinic_id', $currentUser->clinic_id); // Only from their clinic
        }
        
        // Hide the current user from the list (regardless of role)
        if ($currentUser) {
            $query->where('id', '!=', $currentUser->id);
        }

        // Apply role filter (non-encrypted field)
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('user_role', $request->role);
        }

        // Apply status filter (non-encrypted field)
        if ($request->has('status') && $request->status !== 'all') {
            $statusMap = [
                'active' => 0,
                'inactive' => 1,
                'archived' => 2,
            ];
            $query->where('user_status', $statusMap[$request->status] ?? 1);
        }

        // Multi-Tier HIPAA-Compliant Search Strategy:
        // 1. HMAC Blind Index: Fast O(1) SQL index search for exact email queries.
        // 2. Direct SQL Query: Non-encrypted fields (ID, Phone).
        // 3. Memory-Safe In-Memory Decryption: Capped at 500 records for partial name matches.
        if ($hasSearch) {
            $searchTrimmed = trim($search);

            // Tier 1: Check if search term is an email address via HMAC blind index (email_hash)
            if (filter_var($searchTrimmed, FILTER_VALIDATE_EMAIL) || str_contains($searchTrimmed, '@')) {
                $emailHash = app(HmacHashService::class)->hash($searchTrimmed);
                $exactEmailUsers = (clone $query)->where('email_hash', $emailHash)->get();

                if ($exactEmailUsers->isNotEmpty()) {
                    $this->logAudit($request, 'user_list_view', 'User listing viewed');
                    return response()->json([
                        'users' => $exactEmailUsers->map(fn($u) => $this->formatUserResponse($u)),
                        'total' => $exactEmailUsers->count(),
                        'current_page' => 1,
                        'per_page' => 10,
                    ]);
                }
            }

            // Tier 2: Search non-encrypted fields directly in SQL (ID or Phone)
            if (is_numeric($searchTrimmed)) {
                $idMatches = (clone $query)
                    ->where(function ($q) use ($searchTrimmed) {
                        $q->where('id', $searchTrimmed)
                          ->orWhere('phone', 'like', "%{$searchTrimmed}%");
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($idMatches->isNotEmpty()) {
                    $this->logAudit($request, 'user_list_view', 'User listing viewed');
                    return response()->json([
                        'users' => $idMatches->map(fn($u) => $this->formatUserResponse($u)),
                        'total' => $idMatches->count(),
                        'current_page' => 1,
                        'per_page' => 10,
                    ]);
                }
            }

            // Tier 3: In-memory decrypted partial search capped at 500 records to prevent RAM exhaustion
            $searchLower = mb_strtolower($searchTrimmed);
            $allMatchingUsers = (clone $query)->orderBy('created_at', 'desc')->limit(500)->get();

            $filteredUsers = $allMatchingUsers->filter(function ($user) use ($searchLower) {
                $firstNameLower  = mb_strtolower($user->first_name ?? '');
                $lastNameLower   = mb_strtolower($user->last_name ?? '');
                $middleNameLower = mb_strtolower($user->middle_name ?? '');
                $emailLower      = mb_strtolower($user->email ?? '');

                return str_contains($firstNameLower, $searchLower) ||
                       str_contains($lastNameLower, $searchLower) ||
                       str_contains($middleNameLower, $searchLower) ||
                       str_contains($emailLower, $searchLower);
            });

            // Manual pagination
            $page = max(1, (int) ($request->page ?? 1));
            $perPage = 10;
            $total = $filteredUsers->count();
            $paginatedUsers = $filteredUsers->slice(($page - 1) * $perPage, $perPage)->values();

            $this->logAudit($request, 'user_list_view', 'User listing viewed');

            return response()->json([
                'users' => $paginatedUsers->map(fn($user) => $this->formatUserResponse($user)),
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
            ]);
        }

        // No search - use standard pagination
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        $this->logAudit($request, 'user_list_view', 'User listing viewed');

        return response()->json([
            'users' => $users->getCollection()->map(function ($user) {
                return $this->formatUserResponse($user);
            }),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
        ]);
    }

    /**
     * Global user statistics (not limited by pagination)
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $currentUser = $request->user();

        // Enforce role isolation: only Admin (0), Office Staff (1), and Clinic Admin (2) may view user statistics
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view user management statistics.',
                'error' => 'unauthorized'
            ], 403);
        }

        $baseQuery = User::query();
        
        // Hide internal workforce accounts (Super Admin 0 and Office Staff 1) from office staff statistics
        if ($currentUser && $currentUser->user_role === 1) {
            $baseQuery->whereNotIn('user_role', [0, 1]);
        }
        
        // Clinic users can only see clinicians from their own clinic
        if ($currentUser && $currentUser->user_role === 2) {
            $baseQuery->where('user_role', 3) // Only clinicians
                      ->where('clinic_id', $currentUser->clinic_id); // Only from their clinic
        }
        
        // Hide the current user from the statistics
        if ($currentUser) {
            $baseQuery->where('id', '!=', $currentUser->id);
        }

        $total = (clone $baseQuery)->count();
        $active = (clone $baseQuery)->where('user_status', 0)->count();
        $inactive = (clone $baseQuery)->where('user_status', 1)->count();
        $archived = (clone $baseQuery)->where('user_status', 2)->count();

        $byRole = (clone $baseQuery)
            ->selectRaw('user_role, COUNT(*) as cnt')
            ->groupBy('user_role')
            ->pluck('cnt', 'user_role');

        $roles = [
            'admin' => (int)($byRole[0] ?? 0),
            'office-staff' => (int)($byRole[1] ?? 0),
            'clinic' => (int)($byRole[2] ?? 0),
            'clinician' => (int)($byRole[3] ?? 0),
            'manufacturer' => (int)($byRole[4] ?? 0),
            'biller' => (int)($byRole[5] ?? 0),
        ];

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'archived' => $archived,
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $currentUser = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:woundmed_users',
            'user_role' => 'required|integer|between:0,5',
            'user_status' => 'required|integer|between:0,2',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'manufacturer_id' => 'nullable|exists:woundmed_manufacturers,manufacturer_id',
            'phone' => 'nullable|string|max:20',
        ]);

        // Policy authorization check for target role creation
        if (!in_array((int) $validated['user_role'], (new UserPolicy)->getAllowedRolesForCreation($currentUser), true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to create accounts with this role.',
                'error' => 'unauthorized_role_creation'
            ], 403);
        }

        // Sudo Mode: Require password confirmation when creating a Super Admin (role 0)
        if ((int) $validated['user_role'] === 0) {
            $currentPassword = $request->input('current_password');
            if (empty($currentPassword) || !Hash::check($currentPassword, $currentUser->password)) {
                return response()->json([
                    'message' => 'Password confirmation is required to create a Super Admin account.',
                    'error' => 'password_confirmation_required'
                ], 403);
            }
        }

        // Enforce clinic tenant boundaries for clinic admin users during creation
        if ($currentUser && $currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }
            if (isset($validated['clinic_id']) && (int) $validated['clinic_id'] !== (int) $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot create accounts for a different clinic facility.',
                    'error' => 'unauthorized_clinic_assignment'
                ], 403);
            }
            $validated['clinic_id'] = $currentUser->clinic_id;
            $validated['manufacturer_id'] = null;
        }

        // Clinic facility is mandatory and must be active for Clinic Admin (2) and Clinician (3) roles
        if (in_array((int) $validated['user_role'], [2, 3], true)) {
            if (empty($validated['clinic_id'])) {
                return response()->json([
                    'message' => 'A clinic facility must be assigned to this user.',
                    'error' => 'clinic_id_required'
                ], 422);
            }
            $targetClinic = Clinic::where('clinic_id', $validated['clinic_id'])->first();
            if (!$targetClinic || $targetClinic->clinic_status !== 0) {
                return response()->json([
                    'message' => 'The selected clinic facility is inactive or invalid.',
                    'error' => 'inactive_clinic_facility'
                ], 422);
            }
        }

        // Restriction: Only one manufacturer account per manufacturer facility
        if ($validated['user_role'] === 4 && !empty($validated['manufacturer_id'])) {
            $existingManufacturerUser = User::where('user_role', 4)
                ->where('manufacturer_id', $validated['manufacturer_id'])
                ->first();
            
            if ($existingManufacturerUser) {
                return response()->json([
                    'message' => 'This manufacturer facility already has an associated account. Only one manufacturer account is allowed per facility.',
                    'error' => 'manufacturer_account_exists'
                ], 422);
            }
        }

        // Restriction: Only one clinic admin account per clinic facility
        if ($validated['user_role'] === 2 && !empty($validated['clinic_id'])) {
            $existingClinicAdmin = User::where('user_role', 2)
                ->where('clinic_id', $validated['clinic_id'])
                ->first();
            
            if ($existingClinicAdmin) {
                return response()->json([
                    'message' => 'This clinic facility already has an admin account. Only one clinic admin is allowed per facility. You can create clinician accounts instead.',
                    'error' => 'clinic_admin_exists'
                ], 422);
            }
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'user_role' => $validated['user_role'],
            'user_status' => $validated['user_status'],
            'clinic_id' => $validated['clinic_id'] ?? null,
            'manufacturer_id' => $validated['manufacturer_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make(Str::random(64)),
            'force_password_change' => true,
        ]);

        // Synchronize clinic-clinician relationship in woundmed_clinic_clinician pivot
        if ((int) $user->user_role === 3 && $user->clinic_id) {
            DB::table('woundmed_clinic_clinician')->updateOrInsert(
                ['clinician_id' => $user->id],
                ['clinic_id' => $user->clinic_id, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Send welcome email with password reset link (no plaintext password)
        try {
            $userName = trim($user->first_name . ' ' . $user->last_name);
            $token = Password::createToken($user);
            $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            $emailBody = WelcomeEmail::getTemplate($userName, $user->email, $resetUrl);

            $emailService = new EmailService();
            $emailParams = [
                'to'        => $user->email,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WOUNDMED INC. Support',
                'subject'   => 'Welcome to WOUNDMED INC. - Your Account Details',
                'body'      => $emailBody,
            ];

            $emailService->send_email(
                $emailParams,
                'Welcome email sent successfully',
                'Failed to send welcome email'
            );
        } catch (\Exception $e) {
            // Log error but don't fail user creation
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        $this->logAudit($request, 'user_create', "User created: {$user->full_name} (ID: {$user->id}, role: {$user->user_role})", $user->id);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $this->formatUserResponse($user, true)
        ], 201);
    }

    /**
     * Display the specified user with segregated audit logging.
     * 
     * Enforces HIPAA § 164.312(b) & § 164.502(b):
     * Generates a segregated audit trail entry whenever administrative or office staff
     * inspects the detailed contact information / PII of external clinic or manufacturer workforce members.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $currentUser = $request->user();
        $isExternal = in_array((int) $user->user_role, [2, 3, 4, 5], true);
        $facilityName = $user->clinic?->clinic_name ?? $user->manufacturer?->manufacturer_name ?? 'Corporate/Independent';
        $facilityId = $user->clinic_id ?? $user->manufacturer_id ?? 'None';

        $actionType = $isExternal ? 'external_workforce_pii_viewed' : 'workforce_pii_viewed';
        $targetRole = $this->getRoleName((int) $user->user_role);
        $actorRole = $this->getRoleName((int) ($currentUser?->user_role ?? 99));

        $actionMessage = sprintf(
            'Workforce contact details (PII) inspected for user: %s (ID: %d, Role: %s, Facility: %s [ID: %s]) by %s (ID: %d, Role: %s)',
            $user->full_name,
            $user->id,
            $targetRole,
            $facilityName,
            $facilityId,
            $currentUser?->full_name ?? 'System',
            $currentUser?->id ?? 0,
            $actorRole
        );

        $this->logAudit(
            $request,
            $actionType,
            $actionMessage,
            $user->id
        );

        return response()->json([
            'user' => $this->formatUserResponse($user->load(['clinic', 'manufacturer']), true)
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);
        $currentUser = $request->user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:woundmed_users,email,' . $user->id,
            'user_role' => 'sometimes|required|integer|between:0,5',
            'user_status' => 'sometimes|required|integer|between:0,2',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'manufacturer_id' => 'nullable|exists:woundmed_manufacturers,manufacturer_id',
            'phone' => 'nullable|string|max:20',
            'password' => ['sometimes', 'confirmed', Rules\Password::defaults()],
        ]);

        // Security: Prevent users from modifying their own role or status
        if ($currentUser && (int) $currentUser->id === (int) $user->id) {
            if (isset($validated['user_role']) && (int) $validated['user_role'] !== (int) $user->user_role) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot modify your own user role.',
                    'error' => 'self_role_modification_prohibited'
                ], 403);
            }
            if (isset($validated['user_status']) && (int) $validated['user_status'] !== (int) $user->user_status) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot modify your own user status.',
                    'error' => 'self_status_modification_prohibited'
                ], 403);
            }
        }

        // Security: Prevent direct setting/overwriting of another user's password.
        // Under HIPAA § 164.312(a)(2)(i) and § 164.312(c)(2), direct password setting on another user's account
        // creates an impersonation / non-repudiation risk. Administrators must use the sendPasswordResetLink flow.
        if (isset($validated['password'])) {
            if ($currentUser && (int) $currentUser->id !== (int) $user->id) {
                return response()->json([
                    'message' => 'Direct password modification on another user account is prohibited under HIPAA compliance to prevent impersonation. Please use the "Send Password Reset Link" feature.',
                    'error' => 'direct_password_modification_prohibited'
                ], 403);
            }

            // Self-password modification requires current_password verification
            $currentPassword = $request->input('current_password');
            if (empty($currentPassword) || !Hash::check($currentPassword, $currentUser->password)) {
                return response()->json([
                    'message' => 'Your current account password is required to change your password.',
                    'error' => 'password_confirmation_required'
                ], 403);
            }
        }

        // Security: Guard against demoting the last active Super Admin to a non-admin role
        if ((int) $user->user_role === 0 && isset($validated['user_role']) && (int) $validated['user_role'] !== 0) {
            if ($this->getActiveSuperAdminCount($user->id) < 1) {
                return response()->json([
                    'message' => 'Action prohibited. Cannot demote the last active Super Admin.',
                    'error' => 'last_admin_protection'
                ], 422);
            }
        }

        // Security: Guard against deactivating or archiving the last active Super Admin
        if ((int) $user->user_role === 0 && (int) $user->user_status === 0 && isset($validated['user_status']) && (int) $validated['user_status'] !== 0) {
            if ($this->getActiveSuperAdminCount($user->id) < 1) {
                return response()->json([
                    'message' => 'Action prohibited. Cannot deactivate or archive the last active Super Admin.',
                    'error' => 'last_admin_protection'
                ], 422);
            }
        }

        // Security: Enforce role assignment hierarchy
        // Clinic and Clinician cannot assign any role higher than their current role access
        if (isset($validated['user_role']) && (int) $validated['user_role'] !== (int) $user->user_role) {
            $allowedRoles = (new UserPolicy)->getAllowedRolesForUpdate($currentUser, $user);
            if (!in_array((int) $validated['user_role'], $allowedRoles, true)) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to assign this role.',
                    'error' => 'unauthorized_role_assignment'
                ], 403);
            }

            // Sudo Mode: Require password confirmation when promoting a user to Super Admin (role 0)
            if ((int) $validated['user_role'] === 0 && (int) $user->user_role !== 0) {
                $currentPassword = $request->input('current_password');
                if (empty($currentPassword) || !Hash::check($currentPassword, $currentUser->password)) {
                    return response()->json([
                        'message' => 'Password confirmation is required to promote a user to Super Admin.',
                        'error' => 'password_confirmation_required'
                    ], 403);
                }
            }
        }

        // Security: Enforce clinic tenant boundaries for clinic admin users
        if ($currentUser && $currentUser->user_role === 2) {
            // Cannot reassign user to another clinic
            if (isset($validated['clinic_id']) && (int) $validated['clinic_id'] !== (int) $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot assign users to a different clinic facility.',
                    'error' => 'unauthorized_clinic_assignment'
                ], 403);
            }
            $validated['clinic_id'] = $currentUser->clinic_id;
            $validated['manufacturer_id'] = null;
        }

        // Restriction: Only one manufacturer account per manufacturer facility
        if (isset($validated['user_role']) && $validated['user_role'] === 4 && isset($validated['manufacturer_id'])) {
            $existingManufacturerUser = User::where('user_role', 4)
                ->where('manufacturer_id', $validated['manufacturer_id'])
                ->where('id', '!=', $user->id) // Exclude current user
                ->first();
            
            if ($existingManufacturerUser) {
                return response()->json([
                    'message' => 'This manufacturer facility already has an associated account. Only one manufacturer account is allowed per facility.',
                    'error' => 'manufacturer_account_exists'
                ], 422);
            }
        }

        // Restriction: Only one clinic admin account per clinic facility
        if (isset($validated['user_role']) && $validated['user_role'] === 2 && isset($validated['clinic_id'])) {
            $existingClinicAdmin = User::where('user_role', 2)
                ->where('clinic_id', $validated['clinic_id'])
                ->where('id', '!=', $user->id) // Exclude current user
                ->first();
            
            if ($existingClinicAdmin) {
                return response()->json([
                    'message' => 'This clinic facility already has an admin account. Only one clinic admin is allowed per facility. You can create clinician accounts instead.',
                    'error' => 'clinic_admin_exists'
                ], 422);
            }
        }

        $updateData = [
            'first_name' => isset($validated['first_name']) ? ($validated['first_name'] ?: $user->first_name) : $user->first_name,
            'middle_name' => array_key_exists('middle_name', $validated) ? ($validated['middle_name'] ?: null) : $user->middle_name,
            'last_name' => isset($validated['last_name']) ? ($validated['last_name'] ?: $user->last_name) : $user->last_name,
            'email' => $validated['email'] ?? $user->email,
            'user_role' => $validated['user_role'] ?? $user->user_role,
            'user_status' => $validated['user_status'] ?? $user->user_status,
            'clinic_id' => $validated['clinic_id'] ?? $user->clinic_id,
            'manufacturer_id' => $validated['manufacturer_id'] ?? $user->manufacturer_id,
            'phone' => array_key_exists('phone', $validated) ? ($validated['phone'] ?: null) : $user->phone,
        ];

        if (isset($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['force_password_change'] = false;

            // Invalidate other active tokens on password change
            $user->tokens()->where('id', '!=', $request->user()?->currentAccessToken()?->id)->delete();
        }

        $user->update($updateData);

        // Enforce HIPAA § 164.308(a)(3)(ii)(C): Revoke tokens immediately if account is deactivated or archived
        if ((int) $user->user_status !== 0) {
            $user->tokens()->delete();
        }

        // Synchronize clinic-clinician relationship in woundmed_clinic_clinician pivot on update
        $effectiveRole = (int) ($validated['user_role'] ?? $user->user_role);
        $effectiveClinic = $validated['clinic_id'] ?? $user->clinic_id;
        if ($effectiveRole === 3 && $effectiveClinic) {
            DB::table('woundmed_clinic_clinician')->updateOrInsert(
                ['clinician_id' => $user->id],
                ['clinic_id' => $effectiveClinic, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $this->logAudit($request, 'user_update', "User updated: {$user->full_name} (ID: {$user->id})", $user->id);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $this->formatUserResponse($user->fresh(['clinic', 'manufacturer']), true)
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('toggleStatus', $user);

        // Security: Prevent deactivating the last active Super Admin
        if ((int) $user->user_role === 0 && (int) $user->user_status === 0) {
            if ($this->getActiveSuperAdminCount($user->id) < 1) {
                return response()->json([
                    'message' => 'Action prohibited. Cannot deactivate the last active Super Admin.',
                    'error' => 'last_admin_protection'
                ], 422);
            }
        }

        $newStatus = $user->user_status === 0 ? 1 : 0;
        $user->update(['user_status' => $newStatus]);

        // Enforce HIPAA § 164.308(a)(3)(ii)(C): Revoke tokens if deactivated
        if ($newStatus !== 0) {
            $user->tokens()->delete();
        }

        $this->logAudit($request, 'user_toggle_status', "User status toggled: {$user->full_name} (ID: {$user->id}) to status={$newStatus}", $user->id);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $this->formatUserResponse($user, true)
        ]);
    }

    /**
     * Remove the specified user.
     * 
     * Enforces HIPAA § 164.312(a)(1) Access Control, § 164.312(b) Audit Controls,
     * and § 164.308(a)(3)(ii)(C) Termination Procedures by routing deletion through
     * softDelete() with policy authorization, sudo re-authentication, last-admin protection,
     * immediate token revocation, and immutable audit logging.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        return $this->softDelete($request, $user);
    }

    /**
     * Archive user (soft archive)
     */
    public function archive(Request $request, User $user): JsonResponse
    {
        $this->authorize('archive', $user);

        // Security: Prevent archiving the last active Super Admin
        if ((int) $user->user_role === 0 && (int) $user->user_status === 0) {
            if ($this->getActiveSuperAdminCount($user->id) < 1) {
                return response()->json([
                    'message' => 'Action prohibited. Cannot archive the last active Super Admin.',
                    'error' => 'last_admin_protection'
                ], 422);
            }
        }

        $user->update(['user_status' => 2]);

        // Enforce HIPAA § 164.308(a)(3)(ii)(C): Revoke tokens on archive
        $user->tokens()->delete();

        $this->logAudit($request, 'user_archive', "User archived: {$user->full_name} (ID: {$user->id})", $user->id);

        return response()->json([
            'message' => 'User archived successfully',
            'user' => $this->formatUserResponse($user, true)
        ]);
    }

    /**
     * Soft delete user
     */
    public function softDelete(Request $request, User $user): JsonResponse
    {
        $this->authorize('softDelete', $user);

        $currentUser = $request->user();

        // Sudo Mode: Require password confirmation for deleting a user account
        $currentPassword = $request->input('current_password');
        if (empty($currentPassword) || !Hash::check($currentPassword, $currentUser->password)) {
            return response()->json([
                'message' => 'Password confirmation is required to delete a user account.',
                'error' => 'password_confirmation_required'
            ], 403);
        }

        // Security: Prevent soft-deleting the last remaining Super Admin
        if ((int) $user->user_role === 0) {
            $remainingSuperAdminCount = User::where('user_role', 0)->where('id', '!=', $user->id)->count();
            if ($remainingSuperAdminCount < 1) {
                return response()->json([
                    'message' => 'Action prohibited. Cannot delete the last Super Admin.',
                    'error' => 'last_admin_protection'
                ], 422);
            }
        }

        // Enforce HIPAA § 164.308(a)(3)(ii)(C): Revoke tokens on deletion
        $user->tokens()->delete();

        $user->delete();

        $this->logAudit($request, 'user_delete', "User soft deleted with sudo authorization: {$user->full_name} (ID: {$user->id})", $user->id);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Restore user
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->authorize('restore', $user);
        $user->restore();
        $user->update(['user_status' => 0]);

        $this->logAudit($request, 'user_restore', "User restored: {$user->full_name} (ID: {$user->id})", $user->id);

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $this->formatUserResponse($user->fresh(['clinic', 'manufacturer']), true)
        ]);
    }

    /**
     * Get the count of active, non-deleted Super Admins
     */
    private function getActiveSuperAdminCount(?int $excludeUserId = null): int
    {
        $query = User::where('user_role', 0)
            ->where('user_status', 0);

        if ($excludeUserId !== null) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->count();
    }

    /**
     * Format user response for frontend
     * 
     * Enforces HIPAA § 164.502(b) Minimum Necessary standard:
     * When listing users in bulk ($includeFullPii = false), direct workforce contact numbers
     * are masked. Full unmasked contact PII is only provided when explicitly inspecting
     * a user record via show(), which triggers a segregated audit trail entry.
     */
    private function formatUserResponse(User $user, bool $includeFullPii = false): array
    {
        $department = null;
        $clinicCode = null;
        
        // If user is associated with a clinic, get the clinic name as department
        if ($user->clinic_id && $user->clinic) {
            $department = $user->clinic->clinic_name ?? null;
            $clinicCode = $user->clinic->clinic_code ?? null;
        }
        
        // If user is associated with a manufacturer, get the manufacturer name as department
        if ($user->manufacturer_id && $user->manufacturer) {
            $department = $user->manufacturer->manufacturer_name ?? null;
        }

        $phone = $user->phone;
        if (!$includeFullPii && !empty($phone)) {
            $phone = $this->maskPhone($phone);
        }

        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->user_role,
            'clinicId' => $user->clinic_id,
            'clinicCode' => $clinicCode,
            'manufacturerId' => $user->manufacturer_id,
            'department' => $department,
            'isActive' => $user->user_status === 0,
            'isArchived' => $user->user_status === 2,
            'lastLogin' => $user->last_login_at?->toISOString(),
            'createdAt' => $user->created_at->toISOString(),
            'phone' => $phone,
            'phoneMasked' => !$includeFullPii && !empty($user->phone),
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'middleName' => $user->middle_name,
            'one_time_email_verification' => $user->one_time_email_verification,
        ];
    }

    /**
     * Mask phone number for bulk listing views (HIPAA Minimum Necessary standard)
     */
    private function maskPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) >= 4) {
            $lastFour = substr($digits, -4);
            return '***-***-' . $lastFour;
        }

        return '***-***-****';
    }

    /**
     * Map numeric role to human-readable name for audit trails
     */
    private function getRoleName(int $role): string
    {
        return match ($role) {
            0 => 'Super Admin',
            1 => 'Office Staff',
            2 => 'Clinic Admin',
            3 => 'Clinician',
            4 => 'Manufacturer',
            5 => 'Biller',
            default => 'Unknown Role',
        };
    }

    /**
     * Dispatch an administrative password reset link to a user.
     * 
     * Enforces HIPAA § 164.312(a)(2)(i): Administrative password resets dispatch a secure,
     * single-use reset token directly to the user rather than allowing an administrator
     * to set plaintext credentials, preventing impersonation and preserving non-repudiation.
     */
    public function sendPasswordResetLink(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        // Target account must be active
        if ((int) $user->user_status !== 0 || $user->trashed()) {
            return response()->json([
                'message' => 'Cannot send password reset link to an inactive, archived, or deleted user account.',
                'error' => 'account_inactive'
            ], 422);
        }

        // Generate signed password reset token
        $token = Password::createToken($user);
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        $emailBody = ForgotPasswordEmail::getTemplate($resetUrl, $userName);

        $emailService = new EmailService();
        $emailParams = [
            'to'        => $user->email,
            'from'      => 'noreply@woundmed.com',
            'from_name' => 'WOUNDMED INC. Security',
            'subject'   => 'Password Reset Request',
            'body'      => $emailBody,
        ];

        try {
            $emailService->send_email(
                $emailParams,
                'Password reset link sent successfully',
                'Failed to send password reset link'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send administrative password reset link: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to dispatch password reset email. Please verify mail configuration.',
                'error' => 'mail_delivery_failed'
            ], 500);
        }

        $this->logAudit(
            $request,
            'administrative_password_reset_dispatch',
            "Administrative password reset link dispatched to user: {$user->full_name} (ID: {$user->id}, email: {$user->email})",
            $user->id
        );

        return response()->json([
            'message' => "A password reset link has been dispatched to {$user->email}.",
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use App\Services\EmailService;
use App\Template\WelcomeEmail;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
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

        // Get the current authenticated user
        $currentUser = $request->user();
        
        // Hide admin users from office staff
        if ($currentUser && $currentUser->user_role === 1) {
            $query->where('user_role', '!=', 0);
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

        // Apply filters
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('user_role', $request->role);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('user_status', $request->status === 'active' ? 0 : 1);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

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
        $baseQuery = User::query();

        // Get the current authenticated user
        $currentUser = $request->user();
        
        // Hide admin users from office staff statistics
        if ($currentUser && $currentUser->user_role === 1) {
            $baseQuery->where('user_role', '!=', 0);
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
        $inactive = $total - $active;

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
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
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
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Restriction: Office staff cannot create admin or office staff accounts
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_role === 1) {
            // Office staff can only create roles 2-5 (clinic, clinician, manufacturer, biller)
            if ($validated['user_role'] === 0 || $validated['user_role'] === 1) {
                return response()->json([
                    'message' => 'Unauthorized. Office staff cannot create admin or office staff accounts. You can only create clinic, clinician, manufacturer, and biller accounts.',
                    'error' => 'unauthorized_role_creation'
                ], 403);
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

        // Store the plain password before hashing (for email)
        $plainPassword = $validated['password'];

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'user_role' => $validated['user_role'],
            'user_status' => $validated['user_status'],
            'clinic_id' => $validated['clinic_id'],
            'manufacturer_id' => $validated['manufacturer_id'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        // Send welcome email with login credentials
        try {
            $userName = trim($user->first_name . ' ' . $user->last_name);
            $loginUrl = config('app.frontend_url') . '/login';
            $emailBody = WelcomeEmail::getTemplate($userName, $user->email, $plainPassword, $loginUrl);

            $emailService = new EmailService();
            $emailParams = [
                'to'        => $user->email,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WoundMed Support',
                'subject'   => 'Welcome to WoundMed - Your Account Details',
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

        return response()->json([
            'message' => 'User created successfully',
            'user' => $this->formatUserResponse($user)
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUserResponse($user->load(['clinic', 'manufacturer']))
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Prevent office staff from updating admin users
        $currentUser = $request->user();
        if ($currentUser->user_role === 1 && $user->user_role === 0) {
            return response()->json([
                'message' => 'Unauthorized. You cannot update admin users.'
            ], 403);
        }

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
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $this->formatUserResponse($user->fresh(['clinic', 'manufacturer']))
        ]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        // Prevent office staff from toggling admin user status
        $currentUser = $request->user();
        if ($currentUser->user_role === 1 && $user->user_role === 0) {
            return response()->json([
                'message' => 'Unauthorized. You cannot activate/deactivate admin users.'
            ], 403);
        }

        $newStatus = $user->user_status === 0 ? 1 : 0;
        $user->update(['user_status' => $newStatus]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $this->formatUserResponse($user)
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Archive user (soft archive)
     */
    public function archive(Request $request, User $user): JsonResponse
    {
        // Prevent office staff from archiving admin users
        $currentUser = $request->user();
        if ($currentUser->user_role === 1 && $user->user_role === 0) {
            return response()->json([
                'message' => 'Unauthorized. You cannot archive admin users.'
            ], 403);
        }

        $user->update(['user_status' => 2]);

        return response()->json([
            'message' => 'User archived successfully',
            'user' => $this->formatUserResponse($user)
        ]);
    }

    /**
     * Soft delete user
     */
    public function softDelete(Request $request, User $user): JsonResponse
    {
        // Prevent office staff from deleting admin users
        $currentUser = $request->user();
        if ($currentUser->user_role === 1 && $user->user_role === 0) {
            return response()->json([
                'message' => 'Unauthorized. You cannot delete admin users.'
            ], 403);
        }

        $user->delete();

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
        $user->restore();
        $user->update(['user_status' => 0]);

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $this->formatUserResponse($user->fresh(['clinic', 'manufacturer']))
        ]);
    }

    /**
     * Format user response for frontend
     */
    private function formatUserResponse(User $user): array
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
            'phone' => $user->phone,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'middleName' => $user->middle_name,
            'one_time_email_verification' => $user->one_time_email_verification,
        ];
    }
}
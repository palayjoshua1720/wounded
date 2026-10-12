<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Clinic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogger;
use App\Services\EmailService;
use App\Template\WelcomeEmail;
use Illuminate\Support\Facades\Password;

class ClinicController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_clinics';
    }

    protected function getEntityType()
    {
        return 'clinic';
    }
    
    public function getAllActivity(Request $request)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(b) Audit Controls & § 164.502(b) Minimum Necessary:
        // Only Super Admin (0), Office Staff (1), Clinic Admin (2), and Clinician (3) may view activity logs.
        // External partner roles (Manufacturer 4, Biller 5) are strictly prohibited from inspecting audit trails.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view audit activity logs.',
                'error' => 'unauthorized'
            ], 403);
        }

        $query = DB::table('woundmed_audit_logs')
            ->select(
                'audit_log_id',
                'user_id',
                'attempted_identifier',
                'ip_address',
                'action_type',
                'action_message',
                'entity_id',
                'entity',
                'entity_type',
                'status',
                'timestamp'
            );

        // Facility tenant isolation for Clinic Admin (role 2):
        // Only view activities for users belonging to their assigned clinic facility
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }
            $clinicUserIds = DB::table('woundmed_users')
                ->where('clinic_id', $currentUser->clinic_id)
                ->pluck('id');
            $query->whereIn('user_id', $clinicUserIds);
        }

        // Minimum necessary scoping for Clinician (role 3):
        // Clinicians may only view their own activity trail
        if ($currentUser->user_role === 3) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }
            $query->where('user_id', $currentUser->id);
        }

        // Internal workforce isolation for Office Staff (role 1):
        // Office staff cannot view internal Super Admin audit entries
        if ($currentUser->user_role === 1) {
            $superAdminIds = DB::table('woundmed_users')
                ->where('user_role', 0)
                ->pluck('id');
            $query->whereNotIn('user_id', $superAdminIds);
        }

        $logs = $query->orderBy('timestamp', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'message' => 'Activity fetched successfully',
            'data' => $logs
        ]);
    }

    public function getAllClinicians(Request $request)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control & § 164.502(b) Minimum Necessary:
        // Only Super Admin (0), Office Staff (1), Clinic Admin (2), and Clinician (3) may view clinician listings.
        // External partner roles (Manufacturer 4, Biller 5) are strictly prohibited.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view clinician listings.',
                'error' => 'unauthorized'
            ], 403);
        }

        $perPage = $request->query('per_page', 10);
        $simple  = $request->query('simple', false);

        // Enforce HIPAA § 164.502(b) Minimum Necessary:
        // Clinicians (role 3) may only access simplified clinician references (simple=true) for clinical assignment/dropdowns.
        // Full administrative management views with last logins and account statuses are restricted to Admin (0), Office Staff (1), and Clinic Admin (2).
        if ($currentUser->user_role === 3 && !$simple) {
            return response()->json([
                'message' => 'Unauthorized. Clinicians may only access simplified clinician directory for clinical workflows.',
                'error' => 'unauthorized_administrative_view'
            ], 403);
        }

        $query = DB::table('woundmed_users as u')
            ->where('u.user_role', 3)
            ->whereNull('u.deleted_at');

        // Facility tenant isolation: Clinic Admins and Clinicians only see clinicians from their own clinic facility
        if (in_array($currentUser->user_role, [2, 3], true)) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }
            $query->where('u.clinic_id', $currentUser->clinic_id);
        }

        $this->logAudit($request, 'clinician_list_view', 'Clinician listing viewed');

        if ($simple) {
            // Load User models to get decrypted values
            $userIds = $query->pluck('u.id');
            $users = User::whereIn('id', $userIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $clinicians = $users->map(function (User $user) {
                $fullname = $user->first_name .
                    ($user->middle_name ? ' ' . $user->middle_name . '. ' : ' ') .
                    $user->last_name;

                return [
                    'id' => (string) $user->id,
                    'name' => $fullname,
                    'user_role' => $user->user_role,
                ];
            });

            return response()->json($clinicians);
        }

        $users = $query
            ->leftJoin('woundmed_clinic_clinician as cc', 'u.id', '=', 'cc.clinician_id')
            ->select(
                'u.id',
                'u.email',
                'u.first_name',
                'u.middle_name',
                'u.last_name',
                'u.phone',
                'u.user_status',
                'u.last_logged_in',
                'u.created_at',
                DB::raw('GROUP_CONCAT(cc.clinic_id) as clinic_ids')
            )
            ->groupBy(
                'u.id',
                'u.email',
                'u.first_name',
                'u.middle_name',
                'u.last_name',
                'u.phone',
                'u.user_status',
                'u.last_logged_in',
                'u.created_at',
            )
            ->orderBy('u.created_at', 'desc')
            ->paginate($perPage);

        // Collect user IDs and load User models to get decrypted values
        $userIds = $users->pluck('id')->toArray();
        $userModels = User::whereIn('id', $userIds)->get()->keyBy('id');

        $formattedUsers = $users->map(function ($user) use ($userModels) {
            // Get decrypted values from User model if available
            $userModel = $userModels->get($user->id);

            $firstName = $userModel ? $userModel->first_name : $user->first_name;
            $middleName = $userModel ? $userModel->middle_name : $user->middle_name;
            $lastName = $userModel ? $userModel->last_name : $user->last_name;
            $email = $userModel ? $userModel->email : $user->email;
            $phone = $userModel ? $userModel->phone : $user->phone;

            $fullname = $firstName .
                ($middleName ? ' ' . $middleName . '. ' : ' ') .
                $lastName;

            return [
                'id'        => (string) $user->id,
                'email'     => $email,
                'name'      => $fullname,
                'phone'     => $this->maskPhone($phone),
                'role'      => 'Clinician',
                // 'clinicPubId' => $user->clinic_public_id,
                'clinicIds' => $user->clinic_ids ? explode(',', $user->clinic_ids) : [],
                'isActive'  => $user->user_status,
                'lastLogin' => $user->last_logged_in,
                'createdAt' => $user->created_at,
            ];
        });

        return response()->json([
            'user_data' => $formattedUsers,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ]
        ]);
    }

    public function addClinician(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to create clinician accounts.',
                'error' => 'unauthorized'
            ], 403);
        }

        $roleMap = [
            'Admin' => 1,
            'Manager' => 2,
            'Clinician' => 3,
        ];

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:woundmed_users,email',
                'role' => 'required|string',
                'isActive' => 'boolean',
                'clinicId' => 'nullable|integer',
                'manufacturerId' => 'nullable|integer',
                'phone' => 'nullable|string|max:20',
            ]);

            $targetRole = $roleMap[$validated['role']] ?? (is_numeric($validated['role']) ? (int) $validated['role'] : 3);

            // Enforce clinic tenant boundaries for clinic admin users
            if ($currentUser->user_role === 2) {
                if (empty($currentUser->clinic_id)) {
                    return response()->json([
                        'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                        'error' => 'unassigned_clinic_facility'
                    ], 403);
                }
                if (isset($validated['clinicId']) && (int) $validated['clinicId'] !== (int) $currentUser->clinic_id) {
                    return response()->json([
                        'message' => 'Unauthorized. You cannot create accounts for a different clinic facility.',
                        'error' => 'unauthorized_clinic_assignment'
                    ], 403);
                }
                $validated['clinicId'] = $currentUser->clinic_id;
                $validated['manufacturerId'] = null;
                $targetRole = 3; // Clinic Admin can only create Clinicians (role 3)
            }

            // Office Staff cannot create Admin (0) or Office Staff (1) accounts
            if ($currentUser->user_role === 1 && in_array($targetRole, [0, 1], true)) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to create Admin or Office Staff accounts.',
                    'error' => 'unauthorized_role_assignment'
                ], 403);
            }

            // Validate clinicId is mandatory and points to an active clinic
            if (empty($validated['clinicId'])) {
                return response()->json([
                    'message' => 'A clinic facility must be assigned to this clinician.',
                    'error' => 'clinic_id_required'
                ], 422);
            }

            $targetClinic = Clinic::where('clinic_id', $validated['clinicId'])->first();
            if (!$targetClinic || $targetClinic->clinic_status !== 0) {
                return response()->json([
                    'message' => 'The selected clinic facility is inactive or invalid.',
                    'error' => 'inactive_clinic_facility'
                ], 422);
            }

            $newClinician = User::create([
                'clinic_id' => $validated['clinicId'],
                'manufacturer_id' => $validated['manufacturerId'] ?? null,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(64)),
                'force_password_change' => true,
                'user_role' => $targetRole,
                'user_status' => $validated['isActive'] ?? 0,
                'phone' => $validated['phone'] ?? null,
            ]);

            if ($newClinician) {
                // Synchronize clinic-clinician relationship in woundmed_clinic_clinician pivot
                if ((int) $newClinician->user_role === 3 && $newClinician->clinic_id) {
                    DB::table('woundmed_clinic_clinician')->updateOrInsert(
                        ['clinician_id' => $newClinician->id],
                        ['clinic_id' => $newClinician->clinic_id, 'updated_at' => now(), 'created_at' => now()]
                    );
                }
                // Send welcome email with password reset link
                try {
                    $userName = trim($newClinician->first_name . ' ' . $newClinician->last_name);
                    $token = Password::createToken($newClinician);
                    $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($newClinician->email);
                    $emailBody = WelcomeEmail::getTemplate($userName, $newClinician->email, $resetUrl);

                    $emailService = new EmailService();
                    $emailParams = [
                        'to'        => $newClinician->email,
                        'from'      => 'noreply@woundmed.com',
                        'from_name' => 'WOUNDMED INC. Support',
                        'subject'   => 'Welcome to WOUNDMED INC. - Set Up Your Account',
                        'body'      => $emailBody,
                    ];

                    $emailService->send_email(
                        $emailParams,
                        'Welcome email sent successfully',
                        'Failed to send welcome email'
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send welcome email to clinician: ' . $e->getMessage());
                }

                $this->logAudit(
                    $request,
                    'clinician_create',
                    "Clinician created: {$newClinician->first_name} {$newClinician->last_name} (ID: {$newClinician->id})",
                    $newClinician->id,
                    0,
                    null,
                    'woundmed_users',
                    'clinician'
                );

                return response()->json([
                    'message' => 'Clinician Added Successfully'
                ]);
            } else {
                $this->logAudit(
                    $request,
                    'clinician_create',
                    'Failed adding clinician',
                    null,
                    1,
                    null,
                    'woundmed_users',
                    'clinician'
                );

                return response()->json([
                    'message' => 'Error on adding Clinician'
                ], 401);
            }
        } catch (ValidationException $e) {
            $this->logAudit(
                $request,
                'clinician_create',
                'Failed adding clinician: email already taken',
                null,
                1,
                $request->input('email'),
                'woundmed_users',
                'clinician'
            );

            return response()->json([
                'message' => 'Error on adding Clinician',
                'errors'  => $e->errors()
            ], 422);
        }
    }

    public function getAllClinic(Request $request)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control & § 164.502(b) Minimum Necessary:
        // Only Super Admin (0), Office Staff (1), and Clinic Admin (2) may view clinic facilities.
        // Clinicians (3), Manufacturers (4), and Billers (5) are strictly prohibited from viewing clinic facility records.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        $perPage = $request->query('per_page', 9);

        $this->logAudit($request, 'clinic_list_view', 'Clinic listing viewed');

        $query = Clinic::whereNull('deleted_at');

        // Tenant isolation: Clinic Admins (role 2) can only see their own clinic facility
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }
            $query->where('clinic_id', $currentUser->clinic_id);
        }

        $clinics = $query->paginate($perPage);

        $formattedClinics = $clinics->map(function (Clinic $clinic) {
            $clinicians = DB::table('woundmed_clinic_clinician as cc')
                ->join('woundmed_users as u', 'cc.clinician_id', '=', 'u.id')
                ->where('cc.clinic_id', $clinic->clinic_id)
                ->where('u.user_role', 3)
                ->whereNull('u.deleted_at')
                ->select('u.id', 'u.first_name', 'u.middle_name', 'u.last_name', 'u.email', 'u.phone')
                ->get()
                ->map(function ($u) {
                    $fullname = $u->first_name .
                        ($u->middle_name ? ' ' . $u->middle_name . '. ' : ' ') .
                        $u->last_name;

                    return [
                        'id'    => (string) $u->id,
                        'name'  => $fullname,
                        'email' => $u->email,
                        'phone' => $this->maskPhone($u->phone),
                    ];
                });

            $formatted = $this->formatClinicResponse($clinic);
            $formatted['clinicians'] = $clinicians;
            $formatted['assigned_clinician_ids'] = $clinicians;

            return $formatted;
        });

        return response()->json([
            'user_data' => $formattedClinics,
            'meta' => [
                'current_page' => $clinics->currentPage(),
                'last_page'    => $clinics->lastPage(),
                'per_page'     => $clinics->perPage(),
                'total'        => $clinics->total(),
            ]
        ]);
    }

    public function addClinic(Request $request)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Only Super Admin (0) and Office Staff (1) may create clinic facilities.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to create clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        $isActive = filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN);
        $isActive = $isActive ? 1 : 0;
        
        $tempPassword = Str::random(12);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:woundmed_users,email',
            'contactPerson' => 'required|string',
            'publicId' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:20',
        ]);

        // Handle logo upload (public storage for easy access)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinics/logos', 'public');
        }

        $clinicCode = 'CL-' . strtoupper(uniqid());

        $clinic = Clinic::create([
            'clinic_name' => $validated['name'],
            'clinic_code' => $clinicCode,
            'email' => $validated['email'],
            'clinic_public_id' => $validated['publicId'],
            'contact_person' => $validated['contactPerson'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'clinic_status' => $isActive,
            'logo' => $logoPath,
            'created_at' => now(),
        ]);

        if (!empty($validated['assigned_clinicians_id'])) {
            $clinic->clinicians()->attach($validated['assigned_clinicians_id']);
        }

        $this->logAudit($request, 'clinic_create', "Clinic created: {$clinic->clinic_name} (ID: {$clinic->clinic_id})", $clinic->clinic_id);

        return response()->json([
            'message' => 'Clinic created successfully',
            'data' => $this->formatClinicResponse($clinic),
        ]);
    }

    public function updateClinic(Request $request, $clinicId)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Super Admin (0), Office Staff (1), and Clinic Admin (2) for their own clinic only.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to update clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        // Cross-Tenant Boundary Enforcement for Clinic Admin (role 2):
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id)) {
                return response()->json([
                    'message' => 'Unauthorized. Your account is not associated with any clinic facility.',
                    'error' => 'unassigned_clinic_facility'
                ], 403);
            }

            if ((int) $clinicId !== (int) $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You do not have permission to modify a different clinic facility.',
                    'error' => 'cross_tenant_access_denied'
                ], 403);
            }
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:woundmed_users,email,' . $clinicId . ',clinic_id',
            'contactPerson' => 'nullable|string',
            'publicId' => 'nullable|string|max:20',
            // 'isActive' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:20',
            'assigned_clinicians_id' => 'nullable|array',
            'assigned_clinicians_id.*' => 'integer|exists:woundmed_users,id',
        ]);

        // Prevent cross-tenant clinician hijacking by Clinic Admins:
        if ($currentUser->user_role === 2 && !empty($validated['assigned_clinicians_id'])) {
            $foreignClinicians = DB::table('woundmed_users')
                ->whereIn('id', $validated['assigned_clinicians_id'])
                ->where('clinic_id', '!=', $currentUser->clinic_id)
                ->exists();

            if ($foreignClinicians) {
                return response()->json([
                    'message' => 'Unauthorized. You cannot assign clinicians from another clinic facility.',
                    'error' => 'cross_tenant_clinician_assignment'
                ], 403);
            }
        }

        $clinic = Clinic::findOrFail($clinicId);

        $logoPath = $clinic->logo;

        if ($request->hasFile('logo')) {
            if ($clinic->logo && Storage::disk('public')->exists($clinic->logo)) {
                Storage::disk('public')->delete($clinic->logo);
            }

            $logoPath = $request->file('logo')->store('clinics/logos', 'public');
        }

        // Clinic Admin (role 2) cannot alter clinic facility operational status:
        $isActive = $currentUser->user_role === 2
            ? $clinic->clinic_status
            : (filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);

        $clinic->update([
            'clinic_name' => $validated['name'] ?? $clinic->clinic_name,
            'email' => $validated['email'] ?? $clinic->email,
            'clinic_public_id' => $validated['publicId'] ?? $clinic->clinic_public_id,
            'contact_person' => $validated['contactPerson'] ?? $clinic->contact_person,
            'phone' => $validated['phone'] ?? $clinic->phone,
            'address' => $validated['address'] ?? $clinic->address,
            'clinic_status' => $isActive,
            'logo' => $logoPath,
        ]);

        if (!empty($validated['assigned_clinicians_id'])) {
            $clinic->clinicians()->sync($validated['assigned_clinicians_id']);
        }

        $this->logAudit($request, 'clinic_update', "Clinic updated: {$clinic->clinic_name} (ID: {$clinicId})", (int) $clinicId);

        return response()->json([
            'message' => 'Clinic updated successfully',
            'data' => $this->formatClinicResponse($clinic),
        ]);
    }

    public function updateClinicStatus(Request $request, $clinicId)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Only Super Admin (0) and Office Staff (1) may alter clinic facility operational status.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to update clinic facility status.',
                'error' => 'unauthorized'
            ], 403);
        }

        $clinic = Clinic::findOrFail($clinicId);

        if ($clinic->clinic_status == 0) {
            $clinic->clinic_status = 1;
        } elseif ($clinic->clinic_status == 1) {
            $clinic->clinic_status = 0;
        }

        $clinic->save();

        $this->logAudit($request, 'clinic_status_update', "Clinic status toggled: {$clinic->clinic_name} (ID: {$clinicId}) to status={$clinic->clinic_status}", (int) $clinicId);

        return response()->json([
            'success' => true,
            'status' => $clinic->clinic_status,
            'isActive' => $clinic->clinic_status == 0,
            'label'   => $clinic->clinic_status == 0 ? 'Active' : ($clinic->clinic_status == 1 ? 'Inactive' : 'Archived'),
        ]);

    }

    public function deleteClinic(Request $request, $clinicId)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Only Super Admin (0) and Office Staff (1) may delete clinic facilities.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to delete clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        $clinic = Clinic::findOrFail($clinicId);
        $clinicName = $clinic->clinic_name;
        $clinic->delete();

        $this->logAudit($request, 'clinic_delete', "Clinic deleted: {$clinicName} (ID: {$clinicId})", (int) $clinicId);

        return response()->json([
            'success' => true,
            'message' => 'Clinic deleted successfully',
        ]);
    }

    public function archiveClinic(Request $request, $clinicId)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Only Super Admin (0) and Office Staff (1) may archive clinic facilities.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to archive clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        try {
            $clinic = Clinic::findOrFail($clinicId);
            $clinic->clinic_status = 2;
            if (!$clinic->save()) {
                \Log::error('Clinic save failed', $clinic->toArray());
            }

            $this->logAudit($request, 'clinic_archive', "Clinic archived: {$clinic->clinic_name} (ID: {$clinicId})", (int) $clinicId);

            return response()->json([
                'success' => true,
                'message' => 'Clinic archived successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive clinic: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function unarchiveClinic(Request $request, $clinicId)
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(a)(1) Access Control:
        // Only Super Admin (0) and Office Staff (1) may unarchive clinic facilities.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1], true)) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to unarchive clinic facilities.',
                'error' => 'unauthorized'
            ], 403);
        }

        $clinic = Clinic::findOrFail($clinicId);
        $clinic->clinic_status = 0;
        $clinic->save();

        $this->logAudit($request, 'clinic_unarchive', "Clinic unarchived: {$clinic->clinic_name} (ID: {$clinicId})", (int) $clinicId);

        return response()->json([
            'success' => true,
            'message' => 'Clinic unarchived successfully',
        ]);
    }

    private function formatClinicResponse(Clinic $clinic): array
    {
        return [
            'id'            => (string) $clinic->clinic_id,
            'clinicId'      => (string) $clinic->clinic_code,
            'clinicPubId'   => $clinic->clinic_public_id,
            'name'          => $clinic->clinic_name,
            'email'         => $clinic->email,
            'contactPerson' => $clinic->contact_person ?? null,
            'phone'         => $clinic->phone ?? null,
            'address'       => $clinic->address ?? null,
            'isActive'      => $clinic->clinic_status,
            'createdAt'     => $clinic->created_at,
            'updatedAt'     => $clinic->updated_at,
            'logo'          => $clinic->logo ?? null,
        ];
    }

    /**
     * Mask phone number for bulk listing views (HIPAA § 164.502(b) Minimum Necessary standard)
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
}

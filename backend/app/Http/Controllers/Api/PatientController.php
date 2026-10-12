<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientInfo;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\HmacHashService;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogger;

class PatientController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_patient_info';
    }

    protected function getEntityType()
    {
        return 'patient';
    }
    /**
     * Display a listing of patients
     */
    public function index(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.502(b) Minimum Necessary & § 164.312(a)(1) Access Control:
        // Only internal staff (0, 1), clinic admins (2), and clinicians (3) may view patient records.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            $this->logAudit($request, 'patient_unauthorized_access', 'Unauthorized attempt to list patient records', null, 1);
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view patient records.'
            ], 403);
        }

        // Clinic users (role 2) and clinicians (role 3) must be associated with a valid clinic facility
        if (in_array($currentUser->user_role, [2, 3], true) && empty($currentUser->clinic_id)) {
            $this->logAudit($request, 'patient_unauthorized_access', 'User not associated with any clinic facility', null, 1);
            return response()->json([
                'message' => 'Unauthorized. Your account is not associated with any clinic facility.'
            ], 403);
        }

        $query = PatientInfo::with(['user', 'clinic', 'updatedBy', 'assignedClinician:id,first_name,middle_name,last_name']);

        // Clinic users (role 2) can only see patients from their own clinic
        if ($currentUser->user_role === 2) {
            $query->where('clinic_id', $currentUser->clinic_id);
        }

        // Clinicians (role 3) can see patients assigned to them or unassigned in their clinic facility (acute care / intake)
        if ($currentUser->user_role === 3) {
            $query->where('clinic_id', $currentUser->clinic_id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('assigned_clinician_id', $currentUser->id)
                      ->orWhereNull('assigned_clinician_id');
                });
        }

        // Apply clinic filter (only allowed for Admin 0 and Office Staff 1)
        if (in_array($currentUser->user_role, [0, 1], true) && $request->has('clinic_id') && !empty($request->clinic_id)) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $search = $request->search ?? '';
        $hasSearch = !empty($search);

        // Multi-Tier HIPAA-Compliant Search Strategy:
        // 1. HMAC Blind Index: Fast O(1) SQL index search for exact email queries.
        // 2. Direct SQL Query: Non-encrypted fields (patient_id).
        // 3. Memory-Safe In-Memory Decryption: Capped at 500 records for partial name matches.
        if ($hasSearch) {
            $searchTrimmed = trim($search);

            // Tier 1: Check if search term is an email address via HMAC blind index (email_hash)
            if (filter_var($searchTrimmed, FILTER_VALIDATE_EMAIL) || str_contains($searchTrimmed, '@')) {
                $emailHash = app(HmacHashService::class)->hash($searchTrimmed);
                $exactEmailPatients = (clone $query)->where('email_hash', $emailHash)->get();

                if ($exactEmailPatients->isNotEmpty()) {
                    $this->logAudit($request, 'patient_list_view', 'Patient listing viewed (email search match)');
                    return response()->json([
                        'patients' => $exactEmailPatients->map(fn($p) => $this->formatPatientResponse($p)),
                        'total' => $exactEmailPatients->count(),
                        'current_page' => 1,
                        'per_page' => 10,
                    ]);
                }
            }

            // Tier 2: Non-encrypted attribute matching (Patient ID)
            if (is_numeric($searchTrimmed)) {
                $idMatches = (clone $query)->where('patient_id', $searchTrimmed)->get();

                if ($idMatches->isNotEmpty()) {
                    $this->logAudit($request, 'patient_list_view', 'Patient listing viewed (ID match)');
                    return response()->json([
                        'patients' => $idMatches->map(fn($p) => $this->formatPatientResponse($p)),
                        'total' => $idMatches->count(),
                        'current_page' => 1,
                        'per_page' => 10,
                    ]);
                }
            }

            // Tier 3: In-memory decrypted search for encrypted patient names/emails (capped at 500)
            $searchLower = mb_strtolower($searchTrimmed);
            $allMatchingPatients = (clone $query)->orderBy('created_at', 'desc')->limit(500)->get();

            $filteredPatients = $allMatchingPatients->filter(function ($patient) use ($searchLower) {
                $patientNameLower = mb_strtolower($patient->patient_name ?? '');
                $emailLower       = mb_strtolower($patient->email ?? '');

                return str_contains($patientNameLower, $searchLower) ||
                       str_contains($emailLower, $searchLower);
            });

            // Manual pagination
            $page = max(1, (int) ($request->page ?? 1));
            $perPage = 10;
            $total = $filteredPatients->count();
            $paginatedPatients = $filteredPatients->slice(($page - 1) * $perPage, $perPage)->values();

            $this->logAudit($request, 'patient_list_view', "Patient listing viewed (decrypted name search, count: {$total})");

            return response()->json([
                'patients' => $paginatedPatients->map(fn($p) => $this->formatPatientResponse($p)),
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
            ]);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(10);

        $this->logAudit($request, 'patient_list_view', "Patient listing viewed (page: {$patients->currentPage()}, total: {$patients->total()})");

        return response()->json([
            'patients' => $patients->getCollection()->map(function ($patient) {
                return $this->formatPatientResponse($patient);
            }),
            'total' => $patients->total(),
            'current_page' => $patients->currentPage(),
            'per_page' => $patients->perPage(),
        ]);
    }

    /**
     * Get patient statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            $this->logAudit($request, 'patient_unauthorized_access', 'Unauthorized attempt to view patient stats', null, 1);
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view patient statistics.'
            ], 403);
        }

        if (in_array($currentUser->user_role, [2, 3], true) && empty($currentUser->clinic_id)) {
            return response()->json([
                'message' => 'Unauthorized. Your account is not associated with any clinic facility.'
            ], 403);
        }

        $baseQuery = PatientInfo::query();

        // Clinic users (role 2) can only see stats for their own clinic
        if ($currentUser->user_role === 2) {
            $baseQuery->where('clinic_id', $currentUser->clinic_id);
        }

        // Clinicians (role 3) see stats for patients assigned to them or unassigned in their clinic
        if ($currentUser->user_role === 3) {
            $baseQuery->where('clinic_id', $currentUser->clinic_id)
                ->where(function ($q) use ($currentUser) {
                    $q->where('assigned_clinician_id', $currentUser->id)
                      ->orWhereNull('assigned_clinician_id');
                });
        }

        $total = (clone $baseQuery)->count();

        // Patients created this month
        $newThisMonth = (clone $baseQuery)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Patients created in the last 7 days
        $recentlyAdded = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Patients with IVR records
        $withIvrRecords = (clone $baseQuery)
            ->whereHas('ivrs')
            ->count();

        $this->logAudit($request, 'patient_stats_view', 'Patient statistics viewed');

        return response()->json([
            'total' => $total,
            'new_this_month' => $newThisMonth,
            'recently_added' => $recentlyAdded,
            'with_ivr_records' => $withIvrRecords,
        ]);
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            $this->logAudit($request, 'patient_unauthorized_create', 'Unauthorized attempt to create patient', null, 1);
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to create patient records.'
            ], 403);
        }

        if (in_array($currentUser->user_role, [2, 3], true) && empty($currentUser->clinic_id)) {
            return response()->json([
                'message' => 'Unauthorized. Your account is not associated with any clinic facility.'
            ], 403);
        }

        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:woundmed_patient_info,email',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'user_id' => 'nullable|exists:woundmed_users,id',
            'assigned_clinician_id' => 'nullable|integer|exists:woundmed_users,id',
        ]);

        // If clinic user (role 2) or clinician (role 3), force clinic_id to their clinic
        if (in_array($currentUser->user_role, [2, 3], true)) {
            $validated['clinic_id'] = $currentUser->clinic_id;
        }

        // Set user_id to current user if not provided
        if (empty($validated['user_id'])) {
            $validated['user_id'] = $currentUser->id ?? null;
        }

        // Assigned clinician handling:
        //  - Clinic users (role 2): may pick a clinician from their clinic.
        //  - Clinicians (role 3): auto-assign the patient to themselves.
        //  - Admin (role 0) & Office Staff (role 1): may pick a clinician for the assigned clinic.
        $assignedClinicianId = null;
        if ($currentUser->user_role === 2 && !empty($validated['assigned_clinician_id'])) {
            $clinician = User::where('id', $validated['assigned_clinician_id'])
                ->where('user_role', 3)
                ->where('clinic_id', $currentUser->clinic_id)
                ->first();
            if (!$clinician) {
                throw ValidationException::withMessages([
                    'assigned_clinician_id' => ['Selected clinician does not belong to your clinic.'],
                ]);
            }
            $assignedClinicianId = (int) $clinician->id;
        } elseif ($currentUser->user_role === 3) {
            $assignedClinicianId = (int) $currentUser->id;
        } elseif (in_array($currentUser->user_role, [0, 1], true) && !empty($validated['assigned_clinician_id'])) {
            $clinicianQuery = User::where('id', $validated['assigned_clinician_id'])->where('user_role', 3);
            if (!empty($validated['clinic_id'])) {
                $clinicianQuery->where('clinic_id', $validated['clinic_id']);
            }
            $clinician = $clinicianQuery->first();
            if (!$clinician) {
                throw ValidationException::withMessages([
                    'assigned_clinician_id' => ['Selected clinician does not belong to the selected clinic.'],
                ]);
            }
            $assignedClinicianId = (int) $clinician->id;
        }

        $patient = PatientInfo::create([
            'patient_name' => $validated['patient_name'],
            'email' => $validated['email'],
            'clinic_id' => $validated['clinic_id'] ?? null,
            'assigned_clinician_id' => $assignedClinicianId,
            'user_id' => $validated['user_id'],
        ]);

        $this->logAudit($request, 'patient_create', "Patient profile created (ID: {$patient->patient_id})", $patient->patient_id);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $this->formatPatientResponse($patient->load(['user', 'clinic', 'assignedClinician']))
        ], 201);
    }

    /**
     * Display the specified patient (HIPAA compliant with audit logging)
     */
    public function show(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            $this->logAudit($request, 'patient_unauthorized_view', "Unauthorized attempt to view patient ID {$id}", (int) $id, 1);
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view patient records.'
            ], 403);
        }

        $patient = PatientInfo::with(['user', 'clinic', 'ivrs', 'updatedBy', 'assignedClinician:id,first_name,middle_name,last_name'])->findOrFail($id);

        // Check if clinic user / clinician can access this patient
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_view', "Cross-clinic patient access attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only view patients from your clinic.'
                ], 403);
            }
        }
        if ($currentUser->user_role === 3) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_view', "Cross-clinic patient access attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only view patients from your clinic facility.'
                ], 403);
            }

            // Clinicians can access patients assigned to them or unassigned in their clinic (for acute care / intake).
            // Cross-clinician access to another clinician's assigned patients is prohibited.
            if (!empty($patient->assigned_clinician_id) && (int) $patient->assigned_clinician_id !== (int) $currentUser->id) {
                $this->logAudit($request, 'patient_unauthorized_view', "Cross-clinician patient access attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only view patients assigned to you or unassigned in your clinic facility.'
                ], 403);
            }
        }

        // Log audit trail for HIPAA compliance - record who viewed what and when (sanitized, no plaintext PHI)
        $this->logAudit(
            $request,
            'patient_view',
            "Patient details viewed (ID: {$id})",
            (int) $id
        );

        return response()->json([
            'patient' => $this->formatPatientResponse($patient)
        ]);
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            $this->logAudit($request, 'patient_unauthorized_update', "Unauthorized attempt to update patient ID {$id}", (int) $id, 1);
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to update patient records.'
            ], 403);
        }

        $patient = PatientInfo::findOrFail($id);

        // Check if clinic user / clinician can update this patient
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_update', "Cross-clinic patient update attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only update patients from your clinic.'
                ], 403);
            }
        }
        if ($currentUser->user_role === 3) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_update', "Cross-clinic patient update attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only update patients from your clinic facility.'
                ], 403);
            }

            // Clinician can update if assigned to them or unassigned (for acute care / intake).
            // Updating another clinician's assigned patients is prohibited.
            if (!empty($patient->assigned_clinician_id) && (int) $patient->assigned_clinician_id !== (int) $currentUser->id) {
                $this->logAudit($request, 'patient_unauthorized_update', "Cross-clinician patient update attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only update patients assigned to you or unassigned in your clinic facility.'
                ], 403);
            }
        }

        $validated = $request->validate([
            'patient_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:woundmed_patient_info,email,' . $patient->patient_id . ',patient_id',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'user_id' => 'nullable|exists:woundmed_users,id',
            'assigned_clinician_id' => 'nullable|integer|exists:woundmed_users,id',
        ]);

        // Clinic users and clinicians cannot change the clinic_id
        if (in_array($currentUser->user_role, [2, 3], true)) {
            unset($validated['clinic_id']);
        }

        // Assigned clinician:
        $assignedClinicianId = $patient->assigned_clinician_id;
        if ($currentUser->user_role === 3) {
            // When a clinician renders acute care / updates an unassigned patient, auto-claim care assignment
            if (empty($assignedClinicianId)) {
                $assignedClinicianId = (int) $currentUser->id;
            }
        } elseif ($currentUser->user_role === 2 && array_key_exists('assigned_clinician_id', $validated)) {
            if (empty($validated['assigned_clinician_id'])) {
                $assignedClinicianId = null;
            } else {
                $clinician = User::where('id', $validated['assigned_clinician_id'])
                    ->where('user_role', 3)
                    ->where('clinic_id', $currentUser->clinic_id)
                    ->first();
                if (!$clinician) {
                    throw ValidationException::withMessages([
                        'assigned_clinician_id' => ['Selected clinician does not belong to your clinic.'],
                    ]);
                }
                $assignedClinicianId = (int) $clinician->id;
            }
        } elseif (in_array($currentUser->user_role, [0, 1], true) && array_key_exists('assigned_clinician_id', $validated)) {
            if (empty($validated['assigned_clinician_id'])) {
                $assignedClinicianId = null;
            } else {
                $targetClinicId = $validated['clinic_id'] ?? $patient->clinic_id;
                $clinicianQuery = User::where('id', $validated['assigned_clinician_id'])->where('user_role', 3);
                if ($targetClinicId) {
                    $clinicianQuery->where('clinic_id', $targetClinicId);
                }
                $clinician = $clinicianQuery->first();
                if (!$clinician) {
                    throw ValidationException::withMessages([
                        'assigned_clinician_id' => ['Selected clinician does not belong to the patient\'s clinic.'],
                    ]);
                }
                $assignedClinicianId = (int) $clinician->id;
            }
        }

        $updateData = [
            'patient_name' => $validated['patient_name'] ?? $patient->patient_name,
            'email' => $validated['email'] ?? $patient->email,
            'clinic_id' => isset($validated['clinic_id']) ? $validated['clinic_id'] : $patient->clinic_id,
            'assigned_clinician_id' => $assignedClinicianId,
            'user_id' => $validated['user_id'] ?? $patient->user_id,
            'updated_by' => $currentUser->id ?? null,
        ];

        $patient->update($updateData);

        $this->logAudit($request, 'patient_update', "Patient profile updated (ID: {$patient->patient_id})", $patient->patient_id);

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $this->formatPatientResponse($patient->fresh(['user', 'clinic', 'updatedBy', 'assignedClinician']))
        ]);
    }

    /**
     * Remove the specified patient (soft delete)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(c)(1) Data Integrity Safeguards:
        // Medical records/patient profiles may only be deleted by Admin (0) or Clinic Administrator (2).
        // Individual clinicians (3) and office staff (1) cannot unilaterally delete patient records.
        if (!$currentUser || !in_array($currentUser->user_role, [0, 2], true)) {
            $this->logAudit($request, 'patient_unauthorized_delete', "Unauthorized attempt to delete patient ID {$id} by user role " . ($currentUser->user_role ?? 'unknown'), (int) $id, 1);
            return response()->json([
                'message' => 'Unauthorized. Only Administrators and Clinic Admins have permission to delete patient records.'
            ], 403);
        }

        $patient = PatientInfo::findOrFail($id);

        // Check if clinic user can delete this patient
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_delete', "Cross-clinic patient delete attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only delete patients from your clinic.'
                ], 403);
            }
        }

        $patientId = $patient->patient_id;

        $patient->delete();

        $this->logAudit($request, 'patient_delete', "Patient soft deleted (ID: {$patientId})", $patientId);

        return response()->json([
            'message' => 'Patient deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted patient
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $currentUser = $request->user();

        // Enforce HIPAA § 164.312(c)(1) Data Integrity Safeguards:
        // Medical records/patient profiles may only be restored by Admin (0) or Clinic Administrator (2).
        if (!$currentUser || !in_array($currentUser->user_role, [0, 2], true)) {
            $this->logAudit($request, 'patient_unauthorized_restore', "Unauthorized attempt to restore patient ID {$id} by user role " . ($currentUser->user_role ?? 'unknown'), (int) $id, 1);
            return response()->json([
                'message' => 'Unauthorized. Only Administrators and Clinic Admins have permission to restore patient records.'
            ], 403);
        }

        $patient = PatientInfo::withTrashed()->findOrFail($id);

        // Check if clinic user can restore this patient
        if ($currentUser->user_role === 2) {
            if (empty($currentUser->clinic_id) || (int) $patient->clinic_id !== (int) $currentUser->clinic_id) {
                $this->logAudit($request, 'patient_unauthorized_restore', "Cross-clinic patient restore attempt for patient ID {$id}", (int) $id, 1);
                return response()->json([
                    'message' => 'Unauthorized. You can only restore patients from your clinic.'
                ], 403);
            }
        }

        $patient->restore();

        $this->logAudit($request, 'patient_restore', "Patient restored (ID: {$patient->patient_id})", $patient->patient_id);

        return response()->json([
            'message' => 'Patient restored successfully',
            'patient' => $this->formatPatientResponse($patient->fresh(['user', 'clinic']))
        ]);
    }

    /**
     * Get all clinics for dropdown
     */
    public function getClinics(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2, 3], true)) {
            return response()->json(['message' => 'Unauthorized.', 'clinics' => []], 403);
        }

        // Clinic users and clinicians only see their own clinic
        if (in_array($currentUser->user_role, [2, 3], true)) {
            if (empty($currentUser->clinic_id)) {
                return response()->json(['clinics' => []]);
            }

            $clinic = Clinic::where('clinic_id', $currentUser->clinic_id)
                ->where('clinic_status', 0)
                ->first();

            if ($clinic) {
                return response()->json([
                    'clinics' => [
                        [
                            'id' => $clinic->clinic_id,
                            'name' => $clinic->clinic_name,
                        ]
                    ]
                ]);
            }

            return response()->json(['clinics' => []]);
        }

        // Admin and office staff see all active clinics
        $clinics = Clinic::where('clinic_status', 0)
            ->orderBy('clinic_name')
            ->get(['clinic_id', 'clinic_name']);

        return response()->json([
            'clinics' => $clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->clinic_id,
                    'name' => $clinic->clinic_name,
                ];
            })
        ]);
    }

    /**
     * Get clinicians (user_role = 3) within the target clinic,
     * used by the "Assigned To" dropdown on the Patient form.
     */
    public function getAssignableClinicians(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser || !in_array($currentUser->user_role, [0, 1, 2], true)) {
            return response()->json([
                'message' => 'Unauthorized. This list is only available to clinical and administrative staff.',
                'clinicians' => [],
            ], 403);
        }

        $clinicId = null;
        if ($currentUser->user_role === 2) {
            $clinicId = $currentUser->clinic_id;
        } elseif (in_array($currentUser->user_role, [0, 1], true) && $request->filled('clinic_id')) {
            $clinicId = (int) $request->input('clinic_id');
        }

        if (!$clinicId) {
            return response()->json(['clinicians' => []]);
        }

        $clinicians = User::where('user_role', 3)
            ->where('user_status', 0)
            ->whereNull('deleted_at')
            ->where('clinic_id', $clinicId)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name']);

        return response()->json([
            'clinicians' => $clinicians->map(function ($u) {
                $name = trim(($u->first_name ?? '') . ' ' . ($u->middle_name ?? '') . ' ' . ($u->last_name ?? ''));
                $name = preg_replace('/\s+/', ' ', $name);
                return [
                    'id' => (int) $u->id,
                    'full_name' => $name !== '' ? $name : ('User #' . $u->id),
                ];
            })->values(),
        ]);
    }

    /**
     * Format patient response for frontend
     */
    private function formatPatientResponse(PatientInfo $patient): array
    {
        $assignedName = null;
        if ($patient->assignedClinician) {
            $ac = $patient->assignedClinician;
            $assignedName = trim(($ac->first_name ?? '') . ' ' . ($ac->middle_name ?? '') . ' ' . ($ac->last_name ?? ''));
            $assignedName = preg_replace('/\s+/', ' ', $assignedName);
            if ($assignedName === '') {
                $assignedName = null;
            }
        }

        return [
            'patient_id' => $patient->patient_id,
            'patient_name' => $patient->patient_name,
            'email' => $patient->email,
            'clinic_id' => $patient->clinic_id,
            'clinic_name' => $patient->clinic ? $patient->clinic->clinic_name : null,
            'user_id' => $patient->user_id,
            'user_name' => $patient->user ? $patient->user->full_name : null,
            'user_role' => $patient->user ? $patient->user->user_role : null,
            'updated_by_user_id' => $patient->updated_by,
            'updated_by_user_name' => $patient->updatedBy ? $patient->updatedBy->full_name : null,
            'updated_by_user_role' => $patient->updatedBy ? $patient->updatedBy->user_role : null,
            'assigned_clinician_id' => $patient->assigned_clinician_id,
            'assigned_clinician_name' => $assignedName,
            'created_at' => $patient->created_at ? $patient->created_at->toISOString() : null,
            'updated_at' => $patient->updated_at ? $patient->updated_at->toISOString() : null,
            'deleted_at' => $patient->deleted_at ? $patient->deleted_at->toISOString() : null,
            'ivr_count' => $patient->ivrs ? $patient->ivrs->count() : 0,
        ];
    }
}

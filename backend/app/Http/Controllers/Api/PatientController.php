<?php

namespace App\Http\Controllers\Api;

use App\Models\PatientInfo;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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
        $query = PatientInfo::with(['user', 'clinic', 'updatedBy']);

        // Get the current authenticated user
        $currentUser = $request->user();

        // Clinic users (role 2) can only see patients from their own clinic
        if ($currentUser && $currentUser->user_role === 2) {
            $query->where('clinic_id', $currentUser->clinic_id);
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply clinic filter
        if ($request->has('clinic_id') && !empty($request->clinic_id)) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(10);

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
        $baseQuery = PatientInfo::query();

        // Get the current authenticated user
        $currentUser = $request->user();

        // Clinic users (role 2) can only see stats for their own clinic
        if ($currentUser && $currentUser->user_role === 2) {
            $baseQuery->where('clinic_id', $currentUser->clinic_id);
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
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:woundmed_patient_info,email',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'user_id' => 'nullable|exists:woundmed_users,id',
        ]);

        // Get the current authenticated user
        $currentUser = $request->user();

        // If clinic user (role 2), force clinic_id to their clinic
        if ($currentUser && $currentUser->user_role === 2) {
            $validated['clinic_id'] = $currentUser->clinic_id;
        }

        // Set user_id to current user if not provided
        if (empty($validated['user_id'])) {
            $validated['user_id'] = $currentUser->id ?? null;
        }

        $patient = PatientInfo::create([
            'patient_name' => $validated['patient_name'],
            'email' => $validated['email'],
            'clinic_id' => $validated['clinic_id'] ?? null,
            'user_id' => $validated['user_id'],
        ]);

        $this->logAudit($request, 'patient_create', "Patient created: {$patient->patient_name} (ID: {$patient->patient_id})", $patient->patient_id);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $this->formatPatientResponse($patient->load(['user', 'clinic']))
        ], 201);
    }

    /**
     * Display the specified patient (HIPAA compliant with audit logging)
     */
    public function show(Request $request, $id): JsonResponse
    {
        $patient = PatientInfo::with(['user', 'clinic', 'ivrs', 'updatedBy'])->findOrFail($id);

        // Check if clinic user can access this patient
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_role === 2) {
            if ($patient->clinic_id !== $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only view patients from your clinic.'
                ], 403);
            }
        }

        // Log audit trail for HIPAA compliance - record who viewed what and when
        $this->logAudit(
            $request,
            'patient_view',
            "Patient details viewed for: {$patient->patient_name} (ID: {$id})",
            $id
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
        $patient = PatientInfo::findOrFail($id);

        // Check if clinic user can update this patient
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_role === 2) {
            if ($patient->clinic_id !== $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only update patients from your clinic.'
                ], 403);
            }
        }

        $validated = $request->validate([
            'patient_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:woundmed_patient_info,email,' . $patient->patient_id . ',patient_id',
            'clinic_id' => 'nullable|exists:woundmed_clinics,clinic_id',
            'user_id' => 'nullable|exists:woundmed_users,id',
        ]);

        // Clinic users cannot change the clinic_id
        if ($currentUser && $currentUser->user_role === 2) {
            unset($validated['clinic_id']);
        }

        $updateData = [
            'patient_name' => $validated['patient_name'] ?? $patient->patient_name,
            'email' => $validated['email'] ?? $patient->email,
            'clinic_id' => isset($validated['clinic_id']) ? $validated['clinic_id'] : $patient->clinic_id,
            'user_id' => $validated['user_id'] ?? $patient->user_id,
            'updated_by' => $currentUser->id ?? null,
        ];

        $patient->update($updateData);

        $this->logAudit($request, 'patient_update', "Patient updated: {$patient->patient_name} (ID: {$patient->patient_id})", $patient->patient_id);

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $this->formatPatientResponse($patient->fresh(['user', 'clinic', 'updatedBy']))
        ]);
    }

    /**
     * Remove the specified patient (soft delete)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $patient = PatientInfo::findOrFail($id);

        // Check if clinic user can delete this patient
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_role === 2) {
            if ($patient->clinic_id !== $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only delete patients from your clinic.'
                ], 403);
            }
        }

        $patientName = $patient->patient_name;
        $patientId = $patient->patient_id;

        $patient->delete();

        $this->logAudit($request, 'patient_delete', "Patient soft deleted: {$patientName} (ID: {$patientId})", $patientId);

        return response()->json([
            'message' => 'Patient deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted patient
     */
    public function restore(Request $request, $id): JsonResponse
    {
        $patient = PatientInfo::withTrashed()->findOrFail($id);

        // Check if clinic user can restore this patient
        $currentUser = $request->user();
        if ($currentUser && $currentUser->user_role === 2) {
            if ($patient->clinic_id !== $currentUser->clinic_id) {
                return response()->json([
                    'message' => 'Unauthorized. You can only restore patients from your clinic.'
                ], 403);
            }
        }

        $patient->restore();

        $this->logAudit($request, 'patient_restore', "Patient restored: {$patient->patient_name} (ID: {$patient->patient_id})", $patient->patient_id);

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

        // Clinic users only see their own clinic
        if ($currentUser && $currentUser->user_role === 2) {
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
     * Format patient response for frontend
     */
    private function formatPatientResponse(PatientInfo $patient): array
    {
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
            'created_at' => $patient->created_at ? $patient->created_at->toISOString() : null,
            'updated_at' => $patient->updated_at ? $patient->updated_at->toISOString() : null,
            'deleted_at' => $patient->deleted_at ? $patient->deleted_at->toISOString() : null,
            'ivr_count' => $patient->ivrs ? $patient->ivrs->count() : 0,
        ];
    }
}

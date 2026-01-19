<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsageLog;
use App\Models\Brand;
use App\Models\GraftSize;
use App\Models\PatientInfo;
use App\Models\User;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Get all inventory items from usage logs with related data
     */
    public function getInventory()
    {
        $usageLogs = UsageLog::with([
            'patient.clinic',
            'clinician',
            'graftSize.brand.manufacturer'
        ])->get();

        $inventory = $usageLogs->map(function ($log) {
            return [
                'id' => 'inv-' . $log->graft_log_id,
                'serialNumber' => $log->serial_number,
                'patientId' => $log->patient_id,
                'patientName' => $log->patient?->patient_name ?? 'Unknown Patient',
                'patientClinicId' => $log->patient?->clinic_id,
                'patientClinicName' => $log->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                'graftSizeId' => $log->graft_size_id,
                'graftSize' => $log->graftSize?->size ?? 'N/A',
                'graftArea' => $log->graftSize?->area ?? null,
                'graftPrice' => $log->graftSize?->price ?? null,
                'brandId' => $log->graftSize?->brand_id,
                'brandName' => $log->graftSize?->brand?->brand_name ?? 'N/A',
                // Use patient's clinic as the primary clinic for the table display
                'clinicId' => $log->patient?->clinic_id,
                'clinicName' => $log->patient?->clinic?->clinic_name ?? 'Unknown Clinic',
                'dateOfService' => $log->date_of_service ? $log->date_of_service->format('Y-m-d') : null,
                'woundPart' => $log->wound_part,
                'logStatus' => $log->log_status,
                'quantity' => $log->quantity_used,
                'description' => $log->description,
                'expiredAt' => $log->expired_at ? $log->expired_at->format('Y-m-d') : null,
                'filepath' => $log->filepath,
                'loggedBy' => $log->logged_by,
                'clinicianName' => $log->clinician?->first_name . ' ' . $log->clinician?->last_name ?? 'Unknown Clinician',
                'loggedAt' => $log->logged_at ? $log->logged_at->format('Y-m-d H:i:s') : null,
                'status' => $this->getStatusFromLogStatus($log->log_status),
                'expiryDate' => $log->expired_at ? $log->expired_at->format('Y-m-d') : null,
                'usageLogs' => []
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $inventory,
            'total' => $inventory->count()
        ]);
    }

    /**
     * Get inventory by serial number (single item)
     */
    public function getInventoryBySerial($serialNumber)
    {
        $logs = UsageLog::where('serial_number', $serialNumber)
            ->with(['patient.clinic', 'clinician', 'graftSize.brand'])
            ->get();

        if ($logs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No inventory found with this serial number'
            ], 404);
        }

        $inventoryItem = [
            'serialNumber' => $serialNumber,
            'usageLogs' => $logs->map(function ($log) {
                return [
                    'id' => $log->graft_log_id,
                    'patientName' => $log->patient?->patient_name ?? 'Unknown Patient',
                    'dateOfService' => $log->date_of_service ? $log->date_of_service->format('Y-m-d') : null,
                    'woundSite' => $log->wound_part,
                    'clinicianId' => $log->logged_by,
                    'clinicianName' => $log->clinician?->first_name . ' ' . $log->clinician?->last_name ?? 'Unknown Clinician',
                    'notes' => $log->description,
                    'logStatus' => $log->log_status,
                    'quantity' => $log->quantity_used
                ];
            })->toArray(),
            'totalUsageCount' => $logs->count(),
            'totalQuantityUsed' => $logs->sum('quantity_used')
        ];

        return response()->json([
            'success' => true,
            'data' => $inventoryItem
        ]);
    }

    /**
     * Get inventory filtered by status
     */
    public function getInventoryByStatus($statusParam)
    {
        $logStatus = $this->getLogStatusFromStatus($statusParam);

        $usageLogs = UsageLog::where('log_status', $logStatus)
            ->with(['patient.clinic', 'clinician', 'graftSize.brand'])
            ->get();

        $inventory = $usageLogs->map(function ($log) use ($statusParam) {
            return [
                'id' => 'inv-' . $log->graft_log_id,
                'serialNumber' => $log->serial_number,
                'patientName' => $log->patient?->patient_name ?? 'Unknown Patient',
                'clinicId' => $log->patient?->clinic_id,
                'dateOfService' => $log->date_of_service ? $log->date_of_service->format('Y-m-d') : null,
                'woundPart' => $log->wound_part,
                'status' => $statusParam,
                'expiryDate' => $log->expired_at ? $log->expired_at->format('Y-m-d') : null,
                'loggedAt' => $log->logged_at ? $log->logged_at->format('Y-m-d H:i:s') : null
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $inventory,
            'total' => $inventory->count(),
            'status' => $statusParam
        ]);
    }

    /**
     * Create a new usage log entry
     */
    public function storeUsageLog(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|unique:woundmed_usage_log',
            'patient_id' => 'required|integer',
            'logged_by' => 'required|integer',
            'graft_size_id' => 'nullable|integer|exists:woundmed_graft_sizes,graft_size_id',
            'date_of_service' => 'required|date',
            'wound_part' => 'required|string',
            'log_status' => 'required|integer|between:0,6',
            'quantity_used' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'expired_at' => 'nullable|date',
            'filepath' => 'nullable|string'
        ]);

        $usageLog = UsageLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usage log created successfully',
            'data' => $usageLog
        ], 201);
    }

    /**
     * Update a usage log entry
     */
    public function updateUsageLog($id, Request $request)
    {
        $usageLog = UsageLog::findOrFail($id);

        $validated = $request->validate([
            'serial_number' => 'sometimes|string',
            'patient_id' => 'sometimes|integer',
            'logged_by' => 'sometimes|integer',
            'graft_size_id' => 'sometimes|integer|exists:woundmed_graft_sizes,graft_size_id',
            'date_of_service' => 'sometimes|date',
            'wound_part' => 'sometimes|string',
            'log_status' => 'sometimes|integer|between:0,6',
            'quantity_used' => 'sometimes|integer|min:1',
            'description' => 'sometimes|string|nullable',
            'expired_at' => 'sometimes|date|nullable',
            'filepath' => 'sometimes|string|nullable'
        ]);

        $usageLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usage log updated successfully',
            'data' => $usageLog
        ]);
    }

    /**
     * Delete a usage log entry
     */
    public function deleteUsageLog($id)
    {
        $usageLog = UsageLog::findOrFail($id);
        $usageLog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usage log deleted successfully'
        ]);
    }

    /**
     * Convert log_status (0-6) to human-readable status
     * 0 - Expected, 1 - Delivered, 2 - Used, 3 - Partially Used
     * 4 - Reassigned, 5 - Unused, 6 - Expired
     */
    private function getStatusFromLogStatus($logStatus)
    {
        $statusMap = [
            0 => 'expected',
            1 => 'delivered',
            2 => 'used',
            3 => 'partially_used',
            4 => 'reassigned',
            5 => 'unused',
            6 => 'expired'
        ];

        return $statusMap[$logStatus] ?? 'unknown';
    }

    /**
     * Convert human-readable status to log_status (0-6)
     */
    private function getLogStatusFromStatus($status)
    {
        $statusMap = [
            'expected' => 0,
            'delivered' => 1,
            'used' => 2,
            'partially_used' => 3,
            'reassigned' => 4,
            'unused' => 5,
            'expired' => 6
        ];

        return $statusMap[strtolower($status)] ?? null;
    }

    /**
     * Search for patients by name
     */
    public function searchPatients(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 1) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $patients = PatientInfo::with('clinic')
            ->where('patient_name', 'like', '%' . $query . '%')
            ->limit(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->patient_id,
                    'name' => $patient->patient_name,
                    'clinic_id' => $patient->clinic_id,
                    'clinic_name' => $patient->clinic?->clinic_name ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
    }

    /**
     * Get graft size details by ID
     */
    public function getGraftSize($graftSizeId)
    {
        $graftSize = GraftSize::with(['brand.manufacturer'])
            ->where('graft_size_id', $graftSizeId)
            ->first();

        if (!$graftSize) {
            return response()->json([
                'success' => false,
                'message' => 'Graft size not found'
            ], 404);
        }

        $brand = $graftSize->brand;
        $manufacturer = $brand?->manufacturer;

        return response()->json([
            'success' => true,
            'data' => [
                'graft_size_id' => $graftSize->graft_size_id,
                'size' => $graftSize->size,
                'area' => $graftSize->area,
                'price' => $graftSize->price,
                'brand_id' => $graftSize->brand_id,
                'brand_name' => $brand?->brand_name ?? 'N/A',
                'manufacturer_id' => $manufacturer?->manufacturer_id ?? null,
                'manufacturer_name' => $manufacturer?->manufacturer_name ?? null,
            ]
        ]);
    }

    /**
     * Update inventory item status by ID
     */
    public function updateInventoryStatus($id, Request $request)
    {
        $usageLog = UsageLog::findOrFail($id);

        $validated = $request->validate([
            'log_status' => 'required|integer|between:0,6'
        ]);

        $usageLog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventory status updated successfully',
            'data' => [
                'id' => 'inv-' . $usageLog->graft_log_id,
                'status' => $this->getStatusFromLogStatus($usageLog->log_status),
                'logStatus' => $usageLog->log_status
            ]
        ]);
    }

    /**
     * Get clinicians with their clinic information for inventory usage
     */
    public function getCliniciansForInventory()
    {
        $clinicians = User::where('user_role', 3) // Clinician role
            ->with(['clinic']) // Load clinic relationship
            ->select('id', 'first_name', 'middle_name', 'last_name', 'clinic_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($clinician) {
                $fullname = $clinician->first_name . 
                    ($clinician->middle_name ? ' ' . $clinician->middle_name . ' ' : ' ') . 
                    $clinician->last_name;

                return [
                    'id' => (string) $clinician->id,
                    'name' => $fullname,
                    'clinic_id' => $clinician->clinic_id,
                    'clinic_name' => $clinician->clinic?->clinic_name ?? null,
                ];
            });

        return response()->json($clinicians);
    }
}
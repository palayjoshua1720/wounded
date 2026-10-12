<?php

// ============================================================================
// PATIENT GRAFT LOG MODULE - CONTROLLER
// ----------------------------------------------------------------------------
// Standalone controller for the Patient Graft Log feature. Handles all CRUD
// for `woundmed_patient_graft_log` and keeps the linked `woundmed_inventory_ledger`
// row in sync (is_used / status / graft_usage_id) so removing this module
// does not leave dangling state.
//
// To remove this module:
//   1. Delete this file
//   2. Delete App\Models\PatientGraftLog
//   3. Rollback the create_woundmed_patient_graft_log_table migration
//   4. Remove the routes block in routes/api.php
//   5. Remove the router/sidebar block in frontend/src/router/index.ts
//   6. Delete frontend/src/views/PatientGraftLogView.vue
// ============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientGraftLog;
use App\Models\PatientInfo;
use App\Models\InventoryLedger;
use App\Models\GraftSize;
use App\Models\Invoice;
use App\Models\User;
use App\Traits\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientGraftLogController extends Controller
{
    use AuditLogger;

    /**
     * HIPAA audit log — entity name for woundmed_audit_logs.
     */
    protected function getEntityName()
    {
        return 'woundmed_patient_graft_log';
    }

    /**
     * HIPAA audit log — entity type classifier.
     */
    protected function getEntityType()
    {
        return 'patient_graft_log';
    }
    /**
     * Initial payload for the page: patients (left list), serials (drawer
     * dropdown) and clinicians (drawer dropdown). Single round-trip.
     */
    public function init(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'patients'     => $this->buildPatientList(),
                'all_patients' => $this->buildAllPatientList(),
                'serials'      => $this->buildSerialList(),
                'clinicians'   => $this->buildClinicianList(),
            ],
        ]);
    }

    /**
     * Patients with a graft application count (for the left list panel).
     */
    public function getPatients(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $patients = $this->buildPatientList($search);

        return response()->json([
            'success' => true,
            'data'    => $patients,
        ]);
    }

    /**
     * Patient header + full timeline of graft applications (newest first).
     */
    public function getPatientHistory(int $id): JsonResponse
    {
        $patient = PatientInfo::with('clinic:clinic_id,clinic_name')->findOrFail($id);

        $logs = PatientGraftLog::with([
            'brand:brand_id,brand_name',
            'graftSize:graft_size_id,size',
            'clinic:clinic_id,clinic_name',
            'invoice:id,invoice_number',
            'clinician:id,first_name,middle_name,last_name,clinic_id',
            'clinician.clinic:clinic_id,clinic_name',
            'ledger:ledger_id,serial_number,status,is_used',
        ])
            ->where('patient_id', $id)
            ->orderBy('date_of_service', 'desc')
            ->orderBy('graft_log_id', 'desc')
            ->get();

        // Application N is computed against chronological order
        // (oldest = Application 1, newest = Application N).
        $total = $logs->count();
        $transformed = $logs->values()->map(function ($log, $index) use ($total) {
            $applicationNumber = $total - $index; // newest first list
            return $this->transformLog($log, $applicationNumber);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => [
                    'patient_id'   => (int) $patient->patient_id,
                    'patient_name' => $patient->patient_name,
                    'clinic_id'    => $patient->clinic_id ? (int) $patient->clinic_id : null,
                    'clinic_name'  => $patient->clinic?->clinic_name ?? 'No clinic linked',
                    'graft_count'  => $total,
                ],
                'logs' => $transformed,
            ],
        ]);
    }

    /**
     * Searchable serials sourced from the graft catalog
     * (`woundmed_graft_sizes`). The `item_no` column is treated as the
     * canonical serial identifier. Only active (graft_status = 0) rows
     * are returned. All matching serials are shown regardless of whether
     * they already have a patient graft log entry.
     */
    public function searchSerials(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $limit  = (int) $request->query('limit', 25);

        $query = GraftSize::with('brand:brand_id,brand_name')
            ->where('graft_status', 0)
            ->whereNull('deleted_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('item_no', 'like', "%{$search}%")
                  ->orWhere('size', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($bq) use ($search) {
                      $bq->where('brand_name', 'like', "%{$search}%");
                  });
            });
        }

        $sizes = $query->orderBy('item_no')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data'    => $sizes->map(fn($g) => $this->transformSerial($g))->values(),
        ]);
    }

    /**
     * Active clinician users for the drawer dropdown.
     */
    public function getClinicians(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->buildClinicianList(),
        ]);
    }

    /**
     * Create a new graft log entry and mark the linked ledger row as used.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'      => 'required|integer|exists:woundmed_patient_info,patient_id',
            'graft_size_id'   => 'required|integer|exists:woundmed_graft_sizes,graft_size_id',
            'date_of_service' => 'required|date',
            'wound_site'      => 'required|string|max:255',
            'clinician_id'    => 'required|integer|exists:woundmed_users,id',
            'location'        => 'nullable|string|max:255',
            'wound_number'    => 'nullable|integer|min:1',
            'week_number'     => 'nullable|integer|min:1',
            'notes'           => 'nullable|string',
            'invoice_number'  => 'nullable|string|max:100',
        ]);

        // Resolve optional invoice link by number -> id (strict lookup).
        $invoiceId = null;
        if (!empty($validated['invoice_number'])) {
            $invoice = Invoice::where('invoice_number', $validated['invoice_number'])->first();
            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice number not found.',
                    'errors'  => ['invoice_number' => ['Invoice number not found.']],
                ], 422);
            }
            $invoiceId = (int) $invoice->id;
        }
        unset($validated['invoice_number']);

        $log = DB::transaction(function () use ($validated, $request, $invoiceId) {
            $graft     = GraftSize::findOrFail($validated['graft_size_id']);
            $clinician = User::find($validated['clinician_id']);

            $payload = array_merge($validated, [
                'serial_number' => $graft->item_no,
                'brand_id'      => $graft->brand_id,
                'clinic_id'     => $clinician?->clinic_id,
                'invoice_id'    => $invoiceId,
                'ledger_id'     => null,
                'logged_by'     => $request->user()?->id,
            ]);

            return PatientGraftLog::create($payload);
        });

        $log->load([
            'brand:brand_id,brand_name',
            'graftSize:graft_size_id,size',
            'clinic:clinic_id,clinic_name',
            'invoice:id,invoice_number',
            'clinician:id,first_name,middle_name,last_name,clinic_id',
            'clinician.clinic:clinic_id,clinic_name',
        ]);

        // Recompute application number for response
        $applicationNumber = PatientGraftLog::where('patient_id', $log->patient_id)
            ->where(function ($q) use ($log) {
                $q->where('date_of_service', '<', $log->date_of_service)
                  ->orWhere(function ($qq) use ($log) {
                      $qq->where('date_of_service', $log->date_of_service)
                         ->where('graft_log_id', '<=', $log->graft_log_id);
                  });
            })
            ->count();

        // HIPAA audit trail — record graft log creation
        $this->logAudit(
            $request,
            'graft_log_create',
            "Patient graft log created: {$log->graft_log_code} (patient_id={$log->patient_id})",
            $log->graft_log_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Graft log entry saved successfully.',
            'data'    => $this->transformLog($log, $applicationNumber),
        ], 201);
    }

    /**
     * Update an existing graft log entry. If the serial changed, release the
     * previous ledger row and consume the new one.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $log = PatientGraftLog::findOrFail($id);

        $validated = $request->validate([
            'patient_id'      => 'sometimes|integer|exists:woundmed_patient_info,patient_id',
            'graft_size_id'   => 'sometimes|integer|exists:woundmed_graft_sizes,graft_size_id',
            'date_of_service' => 'sometimes|date',
            'wound_site'      => 'sometimes|string|max:255',
            'clinician_id'    => 'sometimes|integer|exists:woundmed_users,id',
            'location'        => 'nullable|string|max:255',
            'wound_number'    => 'nullable|integer|min:1',
            'week_number'     => 'nullable|integer|min:1',
            'notes'           => 'nullable|string',
            'invoice_number'  => 'nullable|string|max:100',
        ]);

        // When invoice_number is present in the request, resolve it (or clear
        // the link entirely when the field is blank).
        if ($request->has('invoice_number')) {
            $num = trim((string) ($validated['invoice_number'] ?? ''));
            if ($num === '') {
                $validated['invoice_id'] = null;
            } else {
                $invoice = Invoice::where('invoice_number', $num)->first();
                if (!$invoice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice number not found.',
                        'errors'  => ['invoice_number' => ['Invoice number not found.']],
                    ], 422);
                }
                $validated['invoice_id'] = (int) $invoice->id;
            }
            unset($validated['invoice_number']);
        }

        DB::transaction(function () use ($log, $validated) {
            $newGraftId = $validated['graft_size_id'] ?? $log->graft_size_id;

            if ($newGraftId && $newGraftId != $log->graft_size_id) {
                // If the log was previously tied to a ledger row (legacy
                // entry from the ledger-based flow), release it so stock
                // state does not go stale.
                if ($log->ledger_id) {
                    $oldLedger = InventoryLedger::lockForUpdate()->find($log->ledger_id);
                    if ($oldLedger && (string) $oldLedger->graft_usage_id === (string) $log->graft_log_id) {
                        $oldLedger->update([
                            'is_used'        => false,
                            'status'         => 1, // Delivered
                            'graft_usage_id' => null,
                        ]);
                    }
                    $validated['ledger_id'] = null;
                }

                $graft = GraftSize::findOrFail($newGraftId);
                $validated['serial_number'] = $graft->item_no;
                $validated['brand_id']      = $graft->brand_id;
            }

            $log->update($validated);
        });

        $log->refresh()->load([
            'brand:brand_id,brand_name',
            'graftSize:graft_size_id,size',
            'clinic:clinic_id,clinic_name',
            'invoice:id,invoice_number',
            'clinician:id,first_name,middle_name,last_name,clinic_id',
            'clinician.clinic:clinic_id,clinic_name',
        ]);

        $applicationNumber = PatientGraftLog::where('patient_id', $log->patient_id)
            ->where(function ($q) use ($log) {
                $q->where('date_of_service', '<', $log->date_of_service)
                  ->orWhere(function ($qq) use ($log) {
                      $qq->where('date_of_service', $log->date_of_service)
                         ->where('graft_log_id', '<=', $log->graft_log_id);
                  });
            })
            ->count();

        // HIPAA audit trail — record graft log update
        $this->logAudit(
            $request,
            'graft_log_update',
            "Patient graft log updated: {$log->graft_log_code} (patient_id={$log->patient_id})",
            $log->graft_log_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Graft log entry updated successfully.',
            'data'    => $this->transformLog($log, $applicationNumber),
        ]);
    }

    /**
     * Soft-delete a graft log entry and release the linked ledger row.
     */
    public function destroy(int $id): JsonResponse
    {
        $log = PatientGraftLog::findOrFail($id);

        DB::transaction(function () use ($log) {
            if ($log->ledger_id) {
                $ledger = InventoryLedger::lockForUpdate()->find($log->ledger_id);
                if ($ledger && (string) $ledger->graft_usage_id === (string) $log->graft_log_id) {
                    $ledger->update([
                        'is_used'        => false,
                        'status'         => 1, // Delivered
                        'graft_usage_id' => null,
                    ]);
                }
            }

            $log->delete();
        });

        // HIPAA audit trail — record graft log deletion
        $this->logAudit(
            request(),
            'graft_log_delete',
            "Patient graft log deleted: {$log->graft_log_code} (patient_id={$log->patient_id})",
            $log->graft_log_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Graft log entry deleted successfully.',
        ]);
    }

    /**
     * Soft-delete EVERY graft log entry belonging to a single patient.
     * Triggered from the patient card on the list page. Also releases any
     * legacy ledger rows still tied to those logs.
     */
    public function destroyByPatient(int $patientId): JsonResponse
    {
        $logs = PatientGraftLog::where('patient_id', $patientId)
            ->whereNull('deleted_at')
            ->get();

        if ($logs->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No graft log entries to delete for this patient.',
                'deleted' => 0,
            ]);
        }

        $count = DB::transaction(function () use ($logs) {
            $n = 0;
            foreach ($logs as $log) {
                if ($log->ledger_id) {
                    $ledger = InventoryLedger::lockForUpdate()->find($log->ledger_id);
                    if ($ledger && (string) $ledger->graft_usage_id === (string) $log->graft_log_id) {
                        $ledger->update([
                            'is_used'        => false,
                            'status'         => 1,
                            'graft_usage_id' => null,
                        ]);
                    }
                }
                $log->delete();
                $n++;
            }
            return $n;
        });

        // HIPAA audit trail — record bulk patient-level graft log deletion
        $this->logAudit(
            request(),
            'graft_log_delete_by_patient',
            "Bulk deleted {$count} patient graft log entries for patient_id={$patientId}",
            $patientId
        );

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} graft log entries for this patient.",
            'deleted' => $count,
        ]);
    }

    // ------------------------------------------------------------------
    // Internal helpers
    // ------------------------------------------------------------------

    /**
     * Full list of active patients (with clinic + graft count) used by the
     * Add-Log modal dropdown. Unlike buildPatientList(), this does NOT
     * filter out patients without any graft log entries.
     */
    private function buildAllPatientList(): array
    {
        $counts = PatientGraftLog::select('patient_id', DB::raw('COUNT(*) as c'))
            ->whereNull('deleted_at')
            ->groupBy('patient_id')
            ->pluck('c', 'patient_id')
            ->toArray();

        $patients = PatientInfo::with('clinic:clinic_id,clinic_name')
            ->whereNull('deleted_at')
            ->get();

        return $patients->map(function ($p) use ($counts) {
            return [
                'patient_id'   => (int) $p->patient_id,
                'patient_name' => $p->patient_name,
                'clinic_id'    => $p->clinic_id ? (int) $p->clinic_id : null,
                'clinic_name'  => $p->clinic?->clinic_name ?? 'No clinic linked',
                'graft_count'  => (int) ($counts[$p->patient_id] ?? 0),
            ];
        })
        ->sortBy('patient_name', SORT_NATURAL | SORT_FLAG_CASE)
        ->values()
        ->all();
    }

    private function buildPatientList(string $search = ''): array
    {
        // Only include patients that have at least one graft log entry.
        $counts = PatientGraftLog::select('patient_id', DB::raw('COUNT(*) as c'))
            ->whereNull('deleted_at')
            ->groupBy('patient_id')
            ->pluck('c', 'patient_id')
            ->toArray();

        $patientIds = array_keys($counts);
        if (empty($patientIds)) {
            return [];
        }

        // Fetch only patients that appear in the graft log table.
        // Patient name/email are encrypted in DB so SQL LIKE on name is
        // not feasible; do a PHP-side filter after decryption.
        $patients = PatientInfo::with('clinic:clinic_id,clinic_name')
            ->whereIn('patient_id', $patientIds)
            ->whereNull('deleted_at')
            ->get();

        $rows = $patients->map(function ($p) use ($counts) {
            return [
                'patient_id'   => (int) $p->patient_id,
                'patient_name' => $p->patient_name,
                'clinic_id'    => $p->clinic_id ? (int) $p->clinic_id : null,
                'clinic_name'  => $p->clinic?->clinic_name ?? 'No clinic linked',
                'graft_count'  => (int) ($counts[$p->patient_id] ?? 0),
            ];
        });

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($r) use ($needle) {
                return str_contains(mb_strtolower((string) $r['patient_name']), $needle)
                    || str_contains(mb_strtolower((string) $r['clinic_name']), $needle);
            });
        }

        return $rows->sortBy('patient_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function buildSerialList(): array
    {
        $sizes = GraftSize::with('brand:brand_id,brand_name')
            ->where('graft_status', 0) // Active
            ->whereNull('deleted_at')
            ->orderBy('item_no')
            ->limit(200)
            ->get();

        return $sizes->map(fn($g) => $this->transformSerial($g))->values()->all();
    }

    private function buildClinicianList(): array
    {
        $clinicians = User::select('id', 'first_name', 'middle_name', 'last_name', 'clinic_id', 'user_role', 'user_status')
            ->with('clinic:clinic_id,clinic_name')
            ->whereIn('user_role', [2, 3]) // Clinic + Clinician
            ->where('user_status', 0)       // Active
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        return $clinicians->map(function ($u) {
            $name = trim("{$u->first_name} {$u->middle_name} {$u->last_name}");
            return [
                'id'          => (int) $u->id,
                'full_name'   => $name !== '' ? $name : ('User #' . $u->id),
                'clinic_id'   => $u->clinic_id ? (int) $u->clinic_id : null,
                'clinic_name' => $u->clinic?->clinic_name ?? null,
                'role'        => (int) $u->user_role,
            ];
        })->values()->all();
    }

    /**
     * Catalog-based serial payload. `item_no` is used as the serial_number
     * alias so the frontend can keep a single display field. Ledger-related
     * fields (ledger_id, clinic, invoice) are intentionally null because the
     * picker no longer consumes inventory.
     */
    private function transformSerial(GraftSize $g): array
    {
        return [
            'graft_size_id'  => (int) $g->graft_size_id,
            'item_no'        => $g->item_no,
            'serial_number'  => $g->item_no, // alias: item_no IS the serial
            'brand_id'       => $g->brand_id ? (int) $g->brand_id : null,
            'brand_name'     => $g->brand?->brand_name ?? 'N/A',
            'size'           => $g->size,
            'stock'          => (int) ($g->stock ?? 0),
            // Legacy fields retained for frontend backward-compat.
            'ledger_id'      => null,
            'clinic_id'      => null,
            'clinic_name'    => null,
            'invoice_id'     => null,
            'invoice_number' => null,
            'is_used'        => false,
        ];
    }

    private function transformLog(PatientGraftLog $log, int $applicationNumber): array
    {
        $clinicianName = null;
        if ($log->clinician) {
            $name = trim("{$log->clinician->first_name} {$log->clinician->middle_name} {$log->clinician->last_name}");
            $clinicianName = $name !== '' ? $name : ('User #' . $log->clinician->id);
        }

        return [
            'graft_log_id'       => (int) $log->graft_log_id,
            'graft_log_code'     => $log->graft_log_code,
            'patient_id'         => (int) $log->patient_id,
            'application_number' => $applicationNumber,
            'date_of_service'    => optional($log->date_of_service)->format('Y-m-d'),
            'serial_number'      => $log->serial_number,
            'ledger_id'          => $log->ledger_id ? (int) $log->ledger_id : null,
            'brand_id'           => $log->brand_id ? (int) $log->brand_id : null,
            'brand_name'         => $log->brand?->brand_name ?? 'N/A',
            'graft_size_id'      => $log->graft_size_id ? (int) $log->graft_size_id : null,
            'size'               => $log->graftSize?->size ?? null,
            'clinic_id'          => $log->clinic_id ? (int) $log->clinic_id : null,
            'clinic_name'        => $log->clinic?->clinic_name ?? null,
            'invoice_id'         => $log->invoice_id ? (int) $log->invoice_id : null,
            'invoice_number'     => $log->invoice?->invoice_number ?? null,
            'clinician_id'       => $log->clinician_id ? (int) $log->clinician_id : null,
            'clinician_name'     => $clinicianName,
            'clinician_clinic_id'   => $log->clinician?->clinic_id ? (int) $log->clinician->clinic_id : null,
            'clinician_clinic_name' => $log->clinician?->clinic?->clinic_name ?? null,
            'location'           => $log->location,
            'wound_site'         => $log->wound_site,
            'wound_number'       => $log->wound_number,
            'week_number'        => $log->week_number,
            'notes'              => $log->notes,
            'created_at'         => $log->created_at?->format('Y-m-d H:i:s'),
            'updated_at'         => $log->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

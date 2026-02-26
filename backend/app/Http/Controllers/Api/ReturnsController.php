<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Returns;
use App\Models\Brand;
use App\Models\GraftSize;
use App\Models\UsageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogger;

class ReturnsController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_returns';
    }

    protected function getEntityType()
    {
        return 'return';
    }

    /**
     * Get all returns with pagination (minimal data for list view)
     */
    public function getAllReturns(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $returns = Returns::with(['brand.manufacturer', 'graftSize', 'usageLog'])
            ->orderBy('returned_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Minimal data for list view - excludes sensitive details
        $formattedReturns = $returns->map(function ($return) {
            return [
                'id' => (string) $return->return_id,
                'entryType' => $return->entry_type,
                'brandId' => (string) $return->brand_id,
                'brandName' => $return->brand->brand_name ?? null,
                'manufacturerName' => $return->brand->manufacturer->manufacturer_name ?? null,
                'graftSizeId' => (string) $return->graft_size_id,
                'graftSize' => $return->graftSize->size ?? null,
                'graftArea' => $return->graftSize ? (float) $return->graftSize->area : null,
                'reason' => $return->reason,
                'other' => $return->other,
                'graftLogId' => $return->graft_log_id ? (string) $return->graft_log_id : null,
                'serialNumber' => $return->usageLog ? $return->usageLog->serial_number : null,
                'expiryDate' => $return->usageLog && $return->usageLog->expired_at ? $return->usageLog->expired_at->format('Y-m-d') : null,
                'ocrSerialNumber' => $return->ocr_serial_number,
                'ocrExpiryDate' => $return->ocr_expiry_date ? $return->ocr_expiry_date->format('Y-m-d') : null,
                'ocrProductCode' => $return->ocr_product_code,
                'returnedAt' => $return->returned_at->format('Y-m-d H:i:s'),
                'updatedAt' => $return->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'returns' => $formattedReturns,
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ]
        ]);
    }

    /**
     * Get a single return by ID with full details (HIPAA compliant with audit logging)
     */
    public function getReturnById(Request $request, $id)
    {
        $return = Returns::with(['brand.manufacturer', 'graftSize', 'usageLog'])
            ->findOrFail($id);

        // Log audit trail for HIPAA compliance - record who viewed what and when
        $brand = $return->brand;
        $this->logAudit(
            $request,
            'return_view',
            "Return details viewed for brand: {$brand->brand_name}, return_id: {$id}",
            $id
        );

        // Full detail response
        return response()->json([
            'data' => [
                'id' => (string) $return->return_id,
                'entryType' => $return->entry_type,
                'brandId' => (string) $return->brand_id,
                'brandName' => $return->brand->brand_name ?? null,
                'manufacturerName' => $return->brand->manufacturer->manufacturer_name ?? null,
                'graftSizeId' => (string) $return->graft_size_id,
                'graftSize' => $return->graftSize->size ?? null,
                'graftArea' => $return->graftSize ? (float) $return->graftSize->area : null,
                'reason' => $return->reason,
                'other' => $return->other,
                'graftLogId' => $return->graft_log_id ? (string) $return->graft_log_id : null,
                'serialNumber' => $return->usageLog ? $return->usageLog->serial_number : null,
                'expiryDate' => $return->usageLog && $return->usageLog->expired_at ? $return->usageLog->expired_at->format('Y-m-d') : null,
                'ocrSerialNumber' => $return->ocr_serial_number,
                'ocrExpiryDate' => $return->ocr_expiry_date ? $return->ocr_expiry_date->format('Y-m-d') : null,
                'ocrProductCode' => $return->ocr_product_code,
                'returnedAt' => $return->returned_at->format('Y-m-d H:i:s'),
                'updatedAt' => $return->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Create a new return entry
     */
    public function createReturn(Request $request)
    {
        $validated = $request->validate([
            'brandId' => 'required|exists:woundmed_brands,brand_id',
            'graftSizeId' => 'required|exists:woundmed_graft_sizes,graft_size_id',
            'reason' => 'required|string|max:500',
            'other' => 'nullable|string|max:1000',
            'entryType' => 'required|in:manual,upload',
            // graftLogId required for manual, nullable for upload
            'graftLogId' => 'nullable|exists:woundmed_usage_log,graft_log_id',
            // OCR fields for upload entries
            'ocrSerialNumber' => 'nullable|string|max:255',
            'ocrExpiryDate' => 'nullable|date',
            'ocrProductCode' => 'nullable|string|max:100',
        ]);

        // Additional validation: graftLogId must be present for manual entries
        if ($validated['entryType'] === 'manual' && empty($validated['graftLogId'])) {
            throw ValidationException::withMessages([
                'graftLogId' => 'Usage log ID is required for manual entries.'
            ]);
        }

        // Validate that graft size belongs to the selected brand
        $graftSize = GraftSize::where('graft_size_id', $validated['graftSizeId'])
            ->where('brand_id', $validated['brandId'])
            ->first();

        if (!$graftSize) {
            throw ValidationException::withMessages([
                'graftSizeId' => 'The selected graft size does not belong to the selected brand.'
            ]);
        }

        // Check for duplicates based on entry type
        if ($validated['entryType'] === 'manual') {
            // For manual entries: Check if graft_log_id is already linked to a return
            $existingReturn = Returns::where('graft_log_id', $validated['graftLogId'])->first();
            if ($existingReturn) {
                // Get the usage log serial number for the error message
                $usageLog = UsageLog::where('graft_log_id', $validated['graftLogId'])->first();
                $serialNumber = $usageLog ? $usageLog->serial_number : 'Unknown';
                throw ValidationException::withMessages([
                    'graftLogId' => "A return record already exists for this usage log (Serial Number: {$serialNumber})."
                ]);
            }
        } elseif ($validated['entryType'] === 'upload') {
            // For upload entries: Check if ocr_serial_number or ocr_product_code already exists
            $ocrSerialNumber = $validated['ocrSerialNumber'] ?? null;
            $ocrProductCode = $validated['ocrProductCode'] ?? null;

            if ($ocrSerialNumber) {
                $existingBySerial = Returns::where('ocr_serial_number', $ocrSerialNumber)->first();
                if ($existingBySerial) {
                    throw ValidationException::withMessages([
                        'ocrSerialNumber' => "A return record already exists with this OCR Serial Number ({$ocrSerialNumber})."
                    ]);
                }
            }

            if ($ocrProductCode) {
                $existingByProductCode = Returns::where('ocr_product_code', $ocrProductCode)->first();
                if ($existingByProductCode) {
                    throw ValidationException::withMessages([
                        'ocrProductCode' => "A return record already exists with this OCR Product Code ({$ocrProductCode})."
                    ]);
                }
            }
        }

        // Create the return
        $return = Returns::create([
            'graft_log_id' => $validated['graftLogId'],
            'entry_type' => $validated['entryType'],
            'brand_id' => $validated['brandId'],
            'graft_size_id' => $validated['graftSizeId'],
            'reason' => $validated['reason'],
            'other' => $validated['other'] ?? null,
            'ocr_serial_number' => $validated['ocrSerialNumber'] ?? null,
            'ocr_expiry_date' => $validated['ocrExpiryDate'] ?? null,
            'ocr_product_code' => $validated['ocrProductCode'] ?? null,
        ]);

        // Log audit trail
        $brand = Brand::find($validated['brandId']);
        $this->logAudit(
            $request,
            'return_create',
            "Return created for brand: {$brand->brand_name}, size: {$graftSize->size}, reason: {$validated['reason']}",
            $return->return_id
        );

        // Reload with relationships
        $return->load(['brand.manufacturer', 'graftSize', 'usageLog']);

        return response()->json([
            'message' => 'Return created successfully',
            'data' => [
                'id' => (string) $return->return_id,
                'entryType' => $return->entry_type,
                'brandId' => (string) $return->brand_id,
                'brandName' => $return->brand->brand_name,
                'manufacturerName' => $return->brand->manufacturer->manufacturer_name ?? null,
                'graftSizeId' => (string) $return->graft_size_id,
                'graftSize' => $return->graftSize->size,
                'graftArea' => (float) $return->graftSize->area,
                'reason' => $return->reason,
                'other' => $return->other,
                'graftLogId' => $return->graft_log_id ? (string) $return->graft_log_id : null,
                'serialNumber' => $return->usageLog ? $return->usageLog->serial_number : null,
                'expiryDate' => $return->usageLog && $return->usageLog->expired_at ? $return->usageLog->expired_at->format('Y-m-d') : null,
                'ocrSerialNumber' => $return->ocr_serial_number,
                'ocrExpiryDate' => $return->ocr_expiry_date ? $return->ocr_expiry_date->format('Y-m-d') : null,
                'ocrProductCode' => $return->ocr_product_code,
                'returnedAt' => $return->returned_at->format('Y-m-d H:i:s'),
                'updatedAt' => $return->updated_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Update an existing return
     */
    public function updateReturn(Request $request, $id)
    {
        $return = Returns::findOrFail($id);

        $validated = $request->validate([
            'brandId' => 'required|exists:woundmed_brands,brand_id',
            'graftSizeId' => 'nullable|exists:woundmed_graft_sizes,graft_size_id',
            'reason' => 'required|string|max:500',
            'other' => 'nullable|string|max:1000',
            'graftLogId' => 'nullable|exists:woundmed_usage_log,graft_log_id',
            // OCR fields for upload entries
            'ocrSerialNumber' => 'nullable|string|max:255',
            'ocrExpiryDate' => 'nullable|date',
            'ocrProductCode' => 'nullable|string|max:100',
            'ocrSize' => 'nullable|string|max:100',
        ]);

        // For manual entries, graftSizeId is required and must belong to brand
        if ($return->entry_type === 'manual') {
            if (empty($validated['graftSizeId'])) {
                throw ValidationException::withMessages([
                    'graftSizeId' => 'Graft size ID is required for manual entries.'
                ]);
            }

            // Validate that graft size belongs to the selected brand
            $graftSize = GraftSize::where('graft_size_id', $validated['graftSizeId'])
                ->where('brand_id', $validated['brandId'])
                ->first();

            if (!$graftSize) {
                throw ValidationException::withMessages([
                    'graftSizeId' => 'The selected graft size does not belong to the selected brand.'
                ]);
            }
        }

        // Check for duplicates based on entry type (exclude current record)
        if ($return->entry_type === 'manual' && !empty($validated['graftLogId'])) {
            // For manual entries: Check if graft_log_id is already linked to another return
            $existingReturn = Returns::where('graft_log_id', $validated['graftLogId'])
                ->where('return_id', '!=', $id)
                ->first();
            if ($existingReturn) {
                $usageLog = UsageLog::where('graft_log_id', $validated['graftLogId'])->first();
                $serialNumber = $usageLog ? $usageLog->serial_number : 'Unknown';
                throw ValidationException::withMessages([
                    'graftLogId' => "A return record already exists for this usage log (Serial Number: {$serialNumber})."
                ]);
            }
        } elseif ($return->entry_type === 'upload') {
            // For upload entries: Check if ocr_serial_number or ocr_product_code already exists
            $ocrSerialNumber = $validated['ocrSerialNumber'] ?? null;
            $ocrProductCode = $validated['ocrProductCode'] ?? null;

            if ($ocrSerialNumber) {
                $existingBySerial = Returns::where('ocr_serial_number', $ocrSerialNumber)
                    ->where('return_id', '!=', $id)
                    ->first();
                if ($existingBySerial) {
                    throw ValidationException::withMessages([
                        'ocrSerialNumber' => "A return record already exists with this OCR Serial Number ({$ocrSerialNumber})."
                    ]);
                }
            }

            if ($ocrProductCode) {
                $existingByProductCode = Returns::where('ocr_product_code', $ocrProductCode)
                    ->where('return_id', '!=', $id)
                    ->first();
                if ($existingByProductCode) {
                    throw ValidationException::withMessages([
                        'ocrProductCode' => "A return record already exists with this OCR Product Code ({$ocrProductCode})."
                    ]);
                }
            }
        }

        $oldReason = $return->reason;
        
        // Prepare update data
        $updateData = [
            'brand_id' => $validated['brandId'],
            'reason' => $validated['reason'],
            'other' => $validated['other'] ?? null,
        ];

        // For manual entries, update graftSizeId and graftLogId
        if ($return->entry_type === 'manual') {
            $updateData['graft_size_id'] = $validated['graftSizeId'];
            $updateData['graft_log_id'] = $validated['graftLogId'] ?? null;
        }

        // For upload entries, update OCR fields
        if ($return->entry_type === 'upload') {
            $updateData['ocr_serial_number'] = $validated['ocrSerialNumber'] ?? null;
            $updateData['ocr_expiry_date'] = $validated['ocrExpiryDate'] ?? null;
            $updateData['ocr_product_code'] = $validated['ocrProductCode'] ?? null;
            // Note: ocrSize is stored as text, not in graft_size_id for upload entries
        }

        // Update the return
        $return->update($updateData);

        // Log audit trail
        $brand = Brand::find($validated['brandId']);
        $this->logAudit(
            $request,
            'return_update',
            "Return updated for brand: {$brand->brand_name}, reason changed from '{$oldReason}' to '{$validated['reason']}'",
            $id
        );

        // Reload with relationships
        $return->load(['brand.manufacturer', 'graftSize', 'usageLog']);

        return response()->json([
            'message' => 'Return updated successfully',
            'data' => [
                'id' => (string) $return->return_id,
                'entryType' => $return->entry_type,
                'brandId' => (string) $return->brand_id,
                'brandName' => $return->brand->brand_name,
                'manufacturerName' => $return->brand->manufacturer->manufacturer_name ?? null,
                'graftSizeId' => $return->graft_size_id ? (string) $return->graft_size_id : null,
                'graftSize' => $return->graftSize->size ?? null,
                'graftArea' => $return->graftSize ? (float) $return->graftSize->area : null,
                'reason' => $return->reason,
                'other' => $return->other,
                'graftLogId' => $return->graft_log_id ? (string) $return->graft_log_id : null,
                'serialNumber' => $return->usageLog ? $return->usageLog->serial_number : null,
                'expiryDate' => $return->usageLog && $return->usageLog->expired_at ? $return->usageLog->expired_at->format('Y-m-d') : null,
                'ocrSerialNumber' => $return->ocr_serial_number,
                'ocrExpiryDate' => $return->ocr_expiry_date ? $return->ocr_expiry_date->format('Y-m-d') : null,
                'ocrProductCode' => $return->ocr_product_code,
                'returnedAt' => $return->returned_at->format('Y-m-d H:i:s'),
                'updatedAt' => $return->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Soft delete a return entry
     */
    public function deleteReturn(Request $request, $id)
    {
        $return = Returns::findOrFail($id);
        
        $brand = $return->brand;
        $graftSize = $return->graftSize;
        $reason = $return->reason;

        // Soft delete the return (record is retained for audit purposes)
        $return->delete();

        // Log audit trail
        $this->logAudit(
            $request,
            'return_delete',
            "Return soft-deleted for brand: {$brand->brand_name}, size: {$graftSize->size}, reason: {$reason}",
            $id
        );

        return response()->json([
            'message' => 'Return deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted return entry
     */
    public function restoreReturn(Request $request, $id)
    {
        $return = Returns::withTrashed()->findOrFail($id);

        if (!$return->trashed()) {
            return response()->json([
                'message' => 'Return is not deleted',
            ], 400);
        }

        $brand = $return->brand;
        $graftSize = $return->graftSize;

        // Restore the soft-deleted return
        $return->restore();

        // Log audit trail
        $this->logAudit(
            $request,
            'return_restore',
            "Return restored for brand: {$brand->brand_name}, size: {$graftSize->size}",
            $id
        );

        return response()->json([
            'message' => 'Return restored successfully',
        ]);
    }

    /**
     * Get return statistics
     */
    public function getReturnStats(Request $request)
    {
        $totalReturns = Returns::count();
        
        // Returns by reason
        $byReason = Returns::select('reason', DB::raw('count(*) as count'))
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->get();

        // Returns by brand
        $byBrand = Returns::with('brand')
            ->select('brand_id', DB::raw('count(*) as count'))
            ->groupBy('brand_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'brandName' => $item->brand->brand_name ?? 'Unknown',
                    'count' => (int) $item->count,
                ];
            });

        // Returns this month
        $thisMonth = Returns::whereMonth('returned_at', date('m'))
            ->whereYear('returned_at', date('Y'))
            ->count();

        return response()->json([
            'totalReturns' => $totalReturns,
            'thisMonth' => $thisMonth,
            'byReason' => $byReason,
            'byBrand' => $byBrand,
        ]);
    }
}

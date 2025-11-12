<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GraftSize;
use App\Models\Brand;
use App\Models\Clinic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class GraftSizeController extends Controller
{
    public function getAllGraftSizes(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $grafts = GraftSize::with(['clinic', 'brand'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedGrafts = $grafts->map(function ($graft) {
            return [
                'graft_size_id' => (string) $graft->graft_size_id,
                'size' => $graft->size,
                'area' => (float) ($graft->area ?? 0),
                'price' => (float) ($graft->price ?? 0),
                'stock' => (int) $graft->stock,
                'graft_status' => (int) $graft->graft_status,
                'created_at' => $graft->created_at,
            ];
        });

        return response()->json([
            'graftData' => $formattedGrafts,
            'meta' => [
                'current_page' => $grafts->currentPage(),
                'last_page' => $grafts->lastPage(),
                'per_page' => $grafts->perPage(),
                'total' => $grafts->total(),
            ]
        ]);
    }

    public function addNewGraftSize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clinic_id' => ['required', 'string', 'exists:woundmed_clinics,clinic_id'],
            'brand_id' => ['required', 'string', 'exists:woundmed_brands,brand_id'],
            'graftSizes' => ['required', 'array', 'min:1'],  // Changed: Expect array, not JSON string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $graftSizesData = $request->input('graftSizes');  // Already an array from JSON body

        if (!is_array($graftSizesData)) {
            throw ValidationException::withMessages(['graftSizes' => 'Graft sizes must be an array.']);
        }

        // Validate each graft size item
        foreach ($graftSizesData as $index => $sizeData) {
            $sizeValidator = Validator::make($sizeData, [
                'size' => ['required', 'string', 'max:255'],
                'area' => ['required', 'numeric', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                // 'stock' => ['required', 'integer', 'min:0'],
            ]);
            if ($sizeValidator->fails()) {
                throw ValidationException::withMessages($sizeValidator->errors());
            }
            $graftSizesData[$index] = $sizeValidator->validated();  // Replace with validated data
        }

        $created = [];
        $datePrefix = date('Ymd');

        // Optimization: Get the max sequence once before the loop
        $lastGraft = GraftSize::where('graft_number', 'LIKE', "GS-{$datePrefix}-%")
            ->orderBy('graft_number', 'desc')
            ->first();
        $nextSequence = $lastGraft ? (int) substr($lastGraft->graft_number, -4) + 1 : 1;

        foreach ($graftSizesData as $sizeData) {
            $graftNumber = "GS-{$datePrefix}-" . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $nextSequence++;  // Increment for next iteration

            $graft = GraftSize::create([
                'clinic_id' => $validated['clinic_id'],
                'brand_id' => $validated['brand_id'],
                'graft_number' => $graftNumber,
                'size' => $sizeData['size'],
                'area' => $sizeData['area'],
                'price' => $sizeData['price'],
                'stock' => $sizeData['stock'],
                'graft_status' => 0,
            ]);

            $graft->load(['clinic', 'brand']);

            $created[] = [
                'graft_size_id' => (string) $graft->graft_size_id,
                'graft_number' => $graft->graft_number,
                'clinic_id' => $graft->clinic_id,
                'brand_id' => $graft->brand_id,
                'size' => $graft->size,
                'area' => (float) ($graft->area ?? 0),
                'price' => (float) ($graft->price ?? 0),
                'stock' => (int) $graft->stock,
                'graft_status' => (int) $graft->graft_status,
                'created_at' => $graft->created_at,
                'clinic' => [
                    'clinic_id' => (string) $graft->clinic->clinic_id,
                    'clinic_name' => $graft->clinic->clinic_name,
                ],
                'brand' => [
                    'brand_id' => (string) $graft->brand->brand_id,
                    'brand_name' => $graft->brand->brand_name,
                ],
            ];
        }

        return response()->json([
            'message' => 'Graft sizes created successfully',
            'data' => $created
        ], 201);
    }

    public function updateGraftSize(Request $request, $id)
    {
        $graft = GraftSize::findOrFail($id);

        $validated = $request->validate([
            'size' => ['required', 'string', 'max:255'],
            'area' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $graft->update($validated);

        $graft->load(['clinic', 'brand']);

        $formatted = [
            'graft_size_id' => (string) $graft->graft_size_id,
            'graft_number' => $graft->graft_number,
            'clinic_id' => $graft->clinic_id,
            'brand_id' => $graft->brand_id,
            'size' => $graft->size,
            'area' => (float) ($graft->area ?? 0),
            'price' => (float) ($graft->price ?? 0),
            'stock' => (int) $graft->stock,
            'graft_status' => (int) $graft->graft_status,
            'created_at' => $graft->created_at,
            'clinic' => [
                'clinic_id' => (string) $graft->clinic->clinic_id,
                'clinic_name' => $graft->clinic->clinic_name,
            ],
            'brand' => [
                'brand_id' => (string) $graft->brand->brand_id,
                'brand_name' => $graft->brand->brand_name,
            ],
        ];

        return response()->json([
            'message' => 'Graft size updated successfully',
            'data' => $formatted
        ]);
    }

    public function archiveGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);

        if ($graft->graft_status !== 0) {
            return response()->json(['message' => 'Cannot archive: not active'], 400);
        }

        $graft->graft_status = 1;
        $graft->save();

        return response()->json([
            'message' => 'Graft size archived successfully',
            'data' => ['graft_status' => 1]
        ]);
    }

    public function unarchiveGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);

        if ($graft->graft_status !== 1) {
            return response()->json(['message' => 'Cannot unarchive: not archived'], 400);
        }

        $graft->graft_status = 0;
        $graft->save();

        return response()->json([
            'message' => 'Graft size unarchived successfully',
            'data' => ['graft_status' => 0]
        ]);
    }

    public function deleteGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);

        if ($graft->graft_status !== 1) {
            return response()->json(['message' => 'Can only delete archived graft sizes'], 400);
        }

        $graft->delete();

        return response()->json([
            'message' => 'Graft size deleted successfully'
        ]);
    }
}

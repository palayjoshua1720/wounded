<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\GraftSize;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    public function getAllBrands(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $brands = Brand::with(['manufacturer', 'graftSizes'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $formattedBrands = $brands->map(function ($brand) {
            $formattedGraftSizes = $brand->graftSizes->map(function ($size) {
                return [
                    'id' => (string) $size->graft_size_id,
                    'size' => $size->size,
                    'area' => (float) $size->area,
                    'price' => (float) $size->price,
                    'graftStatus' => (int) $size->graft_status,
                ];
            });

            return [
                'id' => (string) $brand->brand_id,
                'brandName' => $brand->brand_name,
                'productType' => $brand->product_type == 0 ? 'Graft' : ($brand->product_type == 1 ? 'Device' : 'Unknown'),
                'mue' => (int) $brand->mue,
                'description' => $brand->description,
                'brandStatus' => (int) $brand->brand_status,
                'manufacturerId' => $brand->manufacturer_id ? (int) $brand->manufacturer_id : null,
                'manufacturerName' => $brand->manufacturer ? $brand->manufacturer->manufacturer_name : null,
                'graftSizes' => $formattedGraftSizes,
                'createdAt' => $brand->created_at,
                'updatedAt' => $brand->updated_at,
            ];
        });

        return response()->json([
            'brandData' => $formattedBrands,
            'meta' => [
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
            ]
        ]);
    }

    public function addBrand(Request $request)
    {
        $validated = $request->validate([
            'brandName' => 'required|string|max:255',
            'manufacturerId' => 'required|exists:woundmed_manufacturers,manufacturer_id',
            'product_type' => 'nullable|in:0,1', // Default to 0 (Graft) if not provided
            'mue' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'brandStatus' => 'required|in:0,1,2',
            'graftSizes' => 'nullable|json',
            'graftSizes.*.size' => 'nullable|string|max:50',
            'graftSizes.*.area' => 'nullable|numeric|min:0',
            'graftSizes.*.price' => 'nullable|numeric|min:0',
            'graftSizes.*.graftStatus' => 'nullable|in:0,1,2',
        ]);

        // Parse and validate graftSizes conditionally
        $graftSizesData = [];
        if ($request->has('graftSizes')) {
            $graftSizesJson = $request->input('graftSizes');
            $parsed = is_string($graftSizesJson) ? json_decode($graftSizesJson, true) : $graftSizesJson;
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['graftSizes' => 'Invalid JSON format for graft sizes.']);
            }
            if (!is_array($parsed)) {
                throw ValidationException::withMessages(['graftSizes' => 'Graft sizes must be an array.']);
            }
            foreach ($parsed as $index => $sizeData) {
                $size = trim($sizeData['size'] ?? '');
                $area = $sizeData['area'] ?? null;
                $price = $sizeData['price'] ?? null;
                $hasInput = !empty($size) || ($area !== null && $area >= 0) || ($price !== null && $price >= 0);
                if ($hasInput) {
                    if (empty($size)) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.size" => 'Size is required when other fields are provided.']);
                    }
                    if ($area === null || $area < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.area" => 'Area is required and must be >= 0 when other fields are provided.']);
                    }
                    if ($price === null || $price < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.price" => 'Price is required and must be >= 0 when other fields are provided.']);
                    }
                    $graftSizesData[] = [
                        'size' => $size,
                        'area' => $area,
                        'price' => $price,
                        'graftStatus' => $sizeData['graftStatus'] ?? 0,
                    ];
                }
                // Empty entries are ignored for creation
            }
        }

        // Create the brand first
        $brand = Brand::create([
            'manufacturer_id' => $validated['manufacturerId'],
            'brand_name' => $validated['brandName'],
            'product_type' => $validated['product_type'] ?? 0, // Default to Graft
            'mue' => $validated['mue'],
            'description' => $validated['description'] ?? null,
            'brand_status' => $validated['brandStatus'],
        ]);

        // Create graft sizes linked to the new brand
        foreach ($graftSizesData as $sizeData) {
            GraftSize::create([
                'brand_id' => $brand->brand_id,
                'size' => $sizeData['size'],
                'area' => $sizeData['area'],
                'price' => $sizeData['price'],
                'graft_status' => $sizeData['graftStatus'],
            ]);
        }

        // Reload brand with relationships for response
        $brand->load(['graftSizes', 'manufacturer']);
        $manufacturer = $brand->manufacturer;

        $formattedGraftSizes = $brand->graftSizes->map(function ($size) {
            return [
                'id' => (string) $size->graft_size_id,
                'size' => $size->size,
                'area' => (float) $size->area,
                'price' => (float) $size->price,
                'graftStatus' => (int) $size->graft_status,
            ];
        });

        return response()->json([
            'message' => 'Brand created successfully',
            'data' => [
                'id' => (string) $brand->brand_id,
                'brandName' => $brand->brand_name,
                'manufacturerName' => $manufacturer ? $manufacturer->manufacturer_name : null,
                'mue' => (int) $brand->mue,
                'description' => $brand->description,
                'brandStatus' => (int) $brand->brand_status,
                'productType' => $brand->product_type == 0 ? 'Graft' : 'Device',
                'graftSizes' => $formattedGraftSizes,
                'createdAt' => $brand->created_at,
                'updatedAt' => $brand->updated_at,
            ],
        ], 201);
    }

    public function updateBrand(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'brandName' => 'required|string|max:255',
            'manufacturerId' => 'required|exists:woundmed_manufacturers,manufacturer_id',
            'product_type' => 'nullable|in:0,1',
            'mue' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'brandStatus' => 'required|in:0,1,2',
            'graftSizes' => 'nullable|json',
            'graftSizes.*.id' => 'nullable|exists:woundmed_graft_sizes,graft_size_id',
            'graftSizes.*.size' => 'nullable|string|max:50',
            'graftSizes.*.area' => 'nullable|numeric|min:0',
            'graftSizes.*.price' => 'nullable|numeric|min:0',
            'graftSizes.*.graftStatus' => 'nullable|in:0,1,2',
        ]);

        // Parse and validate graftSizes conditionally
        if ($request->has('graftSizes')) {
            $graftSizesJson = $request->input('graftSizes');
            $parsed = is_string($graftSizesJson) ? json_decode($graftSizesJson, true) : $graftSizesJson;
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['graftSizes' => 'Invalid JSON format for graft sizes.']);
            }
            if (!is_array($parsed)) {
                throw ValidationException::withMessages(['graftSizes' => 'Graft sizes must be an array.']);
            }
            foreach ($parsed as $index => $sizeData) {
                $size = trim($sizeData['size'] ?? '');
                $area = $sizeData['area'] ?? null;
                $price = $sizeData['price'] ?? null;
                $hasInput = !empty($size) || ($area !== null && $area >= 0) || ($price !== null && $price >= 0);
                if ($hasInput) {
                    if (empty($size)) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.size" => 'Size is required when other fields are provided.']);
                    }
                    if ($area === null || $area < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.area" => 'Area is required and must be >= 0 when other fields are provided.']);
                    }
                    if ($price === null || $price < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.price" => 'Price is required and must be >= 0 when other fields are provided.']);
                    }
                    $status = $sizeData['graftStatus'] ?? 0;
                    $sizeId = $sizeData['id'] ?? null;
                    if ($sizeId) {
                        // Update existing
                        $gs = GraftSize::where('graft_size_id', $sizeId)
                            ->where('brand_id', $id)
                            ->firstOrFail();
                        $gs->update([
                            'size' => $size,
                            'area' => $area,
                            'price' => $price,
                            'graft_status' => $status,
                        ]);
                    } else {
                        // Create new
                        GraftSize::create([
                            'brand_id' => $id,
                            'size' => $size,
                            'area' => $area,
                            'price' => $price,
                            'graft_status' => $status,
                        ]);
                    }
                } else {
                    // All empty: delete if existing
                    $sizeId = $sizeData['id'] ?? null;
                    if ($sizeId) {
                        $gs = GraftSize::where('graft_size_id', $sizeId)
                            ->where('brand_id', $id)
                            ->first();
                        if ($gs) {
                            $gs->delete();
                        }
                    }
                }
            }
        }

        // Update brand
        $brand->update([
            'manufacturer_id' => $validated['manufacturerId'],
            'brand_name' => $validated['brandName'],
            'product_type' => $validated['product_type'] ?? 0,
            'mue' => $validated['mue'],
            'description' => $validated['description'] ?? null,
            'brand_status' => $validated['brandStatus'],
        ]);

        // Reload for response
        $brand->load(['graftSizes', 'manufacturer']);
        $manufacturer = $brand->manufacturer;

        $formattedGraftSizes = $brand->graftSizes->map(function ($size) {
            return [
                'id' => (string) $size->graft_size_id,
                'size' => $size->size,
                'area' => (float) $size->area,
                'price' => (float) $size->price,
                'graftStatus' => (int) $size->graft_status,
            ];
        });

        return response()->json([
            'message' => 'Brand updated successfully',
            'data' => [
                'id' => (string) $brand->brand_id,
                'brandName' => $brand->brand_name,
                'manufacturerName' => $manufacturer ? $manufacturer->manufacturer_name : null,
                'mue' => (int) $brand->mue,
                'description' => $brand->description,
                'brandStatus' => (int) $brand->brand_status,
                'productType' => $brand->product_type == 0 ? 'Graft' : 'Device',
                'graftSizes' => $formattedGraftSizes,
                'createdAt' => $brand->created_at,
                'updatedAt' => $brand->updated_at,
            ],
        ]);
    }

    public function toggleBrandStatus(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $oldStatus = $brand->brand_status;
        $statusMap = [
            2 => 0, // Archived -> Active
            1 => 0, // Inactive -> Active
            0 => 1, // Active -> Inactive
        ];
        $newStatus = $statusMap[$oldStatus] ?? $oldStatus;
        $brand->brand_status = $newStatus;
        $brand->save();

        $statusText = $newStatus === 0 ? 'Active' : 'Inactive';
        if ($oldStatus === 2) {
            $actionType = 'brand_reactivate';
        } elseif ($newStatus === 0) {
            $actionType = 'brand_activate';
        } else {
            $actionType = 'brand_deactivate';
        }

        return response()->json([
            'message' => 'Brand status updated successfully',
            'data'    => [
                'brandStatus' => $newStatus,
            ],
        ]);
    }

    public function deleteBrand(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $name = $brand->brand_name; // Capture before delete
        $brand->delete(); // Soft delete

        // Log successful deletion
        // $this->logAudit($request, 'brand_delete', "Brand deleted: {$name}", $id);

        return response()->json([
            'message' => 'Brand deleted successfully',
        ]);
    }

    public function archiveBrand(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        if ($brand->brand_status !== 2) {
            $oldStatus = $brand->brand_status;
            $brand->brand_status = 2; // Archived
            $brand->save();

            // $this->logAudit($request, 'brand_archive', "Brand archived: {$brand->brand_name} (from status {$oldStatus})", $id);
        }

        return response()->json([
            'message' => 'Brand archived successfully',
            'data'    => [
                'brandStatus' => (int) $brand->brand_status,
            ],
        ]);
    }
}

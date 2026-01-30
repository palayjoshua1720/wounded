<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\GraftSize;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogger;

class BrandController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_brands';
    }

    protected function getEntityType()
    {
        return 'brand';
    }

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
                    'stock' => (int) $size->stock,
                    'graftStatus' => (int) $size->graft_status,
                ];
            });

            $logoUrl = $brand->logo ? asset('storage/' . $brand->logo) : null; // Added logo URL

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
                'logoUrl' => $logoUrl, // Added logo URL to response
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
            'graftSizes.*.stock' => 'nullable|integer|min:0',
            'graftSizes.*.graftStatus' => 'nullable|in:0,1,2',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Added logo validation
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
                        'stock' => $sizeData['stock'] ?? 0,
                        'graftStatus' => $sizeData['graftStatus'] ?? 0,
                    ];
                }
                // Empty entries are ignored for creation
            }
        }

        // Handle logo upload (public storage for easy access)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands/logos', 'public');
        }

        // Create the brand first
        $brand = Brand::create([
            'manufacturer_id' => $validated['manufacturerId'],
            'brand_name' => $validated['brandName'],
            'product_type' => $validated['product_type'] ?? 0, // Default to Graft
            'mue' => $validated['mue'],
            'description' => $validated['description'] ?? null,
            'brand_status' => $validated['brandStatus'],
            'logo' => $logoPath, // Added logo
        ]);

        $this->logAudit($request, 'brand_create', "Brand created: {$validated['brandName']}", $brand->brand_id);

        // Create graft sizes linked to the new brand
        foreach ($graftSizesData as $sizeData) {
            $graftSize = GraftSize::create([
                'brand_id' => $brand->brand_id,
                'size' => $sizeData['size'],
                'area' => $sizeData['area'],
                'price' => $sizeData['price'],
                'stock' => $sizeData['stock'] ?? 0,
                'graft_status' => $sizeData['graftStatus'],
            ]);

            // Log audit trail for graft size creation
            $this->logAudit($request, 'graft_size_create', "Graft size created: {$sizeData['size']} for brand: {$validated['brandName']}", $graftSize->graft_size_id);
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
                'stock' => (int) $size->stock,
                'graftStatus' => (int) $size->graft_status,
            ];
        });

        $logoUrl = $brand->logo ? asset('storage/' . $brand->logo) : null; // Added logo URL

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
                'logoUrl' => $logoUrl, // Added logo URL to response
                'createdAt' => $brand->created_at,
                // 'updatedAt' => $brand->updated_at,
            ],
        ], 201);
    }

    public function updateBrand(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'brandName'         => 'required|string|max:255',
            'manufacturerId'    => 'required|exists:woundmed_manufacturers,manufacturer_id',
            'product_type'      => 'nullable|in:0,1',
            'mue'               => 'required|integer|min:0',
            'description'       => 'nullable|string',
            'brandStatus'       => 'required|in:0,1,2',
            'graftSizes'        => 'nullable|json',
            'graftSizes.*.id'   => 'nullable|exists:woundmed_graft_sizes,graft_size_id',
            'graftSizes.*.size' => 'nullable|string|max:50',
            'graftSizes.*.area' => 'nullable|numeric|min:0',
            'graftSizes.*.price' => 'nullable|numeric|min:0',
            'graftSizes.*.stock' => 'nullable|integer|min:0',
            'graftSizes.*.graftStatus' => 'nullable|in:0,1,2',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_logo'       => 'nullable|in:1', // ← Critical for removal
        ]);

        $oldBrandName = $brand->brand_name;

        // === HANDLE GRAFT SIZES (unchanged – already perfect) ===
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
                $size   = trim($sizeData['size'] ?? '');
                $area   = $sizeData['area'] ?? null;
                $price  = $sizeData['price'] ?? null;
                $hasInput = !empty($size) || ($area !== null && $area >= 0) || ($price !== null && $price >= 0);

                if ($hasInput) {
                    if (empty($size)) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.size" => 'Size is required when other fields are provided.']);
                    }
                    if ($area === null || $area < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.area" => 'Area is required and must be >= 0.']);
                    }
                    if ($price === null || $price < 0) {
                        throw ValidationException::withMessages(["graftSizes.{$index}.price" => 'Price is required and must be >= 0.']);
                    }

                    $status = $sizeData['graftStatus'] ?? 0;
                    $stock  = $sizeData['stock'] ?? 0;
                    $sizeId = $sizeData['id'] ?? null;

                    if ($sizeId) {
                        // Update existing
                        $gs = GraftSize::where('graft_size_id', $sizeId)
                            ->where('brand_id', $id)
                            ->firstOrFail();
                        $oldSize = $gs->size;
                        $gs->update([
                            'size'         => $size,
                            'area'         => $area,
                            'price'        => $price,
                            'stock'        => $stock,
                            'graft_status' => $status,
                        ]);
                        $this->logAudit($request, 'graft_size_update', "Graft size updated: {$oldSize} → {$size} for brand ID: {$id}", $sizeId);
                    } else {
                        // Create new
                        $graftSize = GraftSize::create([
                            'brand_id'     => $id,
                            'size'         => $size,
                            'area'         => $area,
                            'price'        => $price,
                            'stock'        => $stock,
                            'graft_status' => $status,
                        ]);
                        $this->logAudit($request, 'graft_size_create', "Graft size created: {$size} for brand ID: {$id}", $graftSize->graft_size_id);
                    }
                } else {
                    // All fields empty → delete if exists
                    $sizeId = $sizeData['id'] ?? null;
                    if ($sizeId) {
                        $gs = GraftSize::where('graft_size_id', $sizeId)
                            ->where('brand_id', $id)
                            ->first();
                        if ($gs) {
                            $gs->delete();
                            $this->logAudit($request, 'graft_size_delete', "Graft size deleted: {$gs->size} for brand ID: {$id}", $sizeId);
                        }
                    }
                }
            }
        }

        // === HANDLE LOGO (NOW WITH REMOVAL SUPPORT) ===
        if ($request->hasFile('logo')) {
            // New logo uploaded → replace old one
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $logoPath = $request->file('logo')->store('brands/logos', 'public');
            $brand->logo = $logoPath;
        } elseif ($request->has('remove_logo') && $request->input('remove_logo') === '1') {
            // User clicked "X" → explicitly remove logo
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $brand->logo = null;
        }
        // If neither → keep current logo

        // === UPDATE BRAND FIELDS ===
        $brand->update([
            'manufacturer_id' => $validated['manufacturerId'],
            'brand_name'      => $validated['brandName'],
            'product_type'    => $validated['product_type'] ?? 0,
            'mue'             => $validated['mue'],
            'description'     => $validated['description'] ?? null,
            'brand_status'    => $validated['brandStatus'],
        ]);

        $this->logAudit($request, 'brand_update', "Brand updated: {$oldBrandName} → {$validated['brandName']}", $id);

        // Reload relationships
        $brand->load(['graftSizes', 'manufacturer']);
        $manufacturer = $brand->manufacturer;

        $formattedGraftSizes = $brand->graftSizes->map(function ($size) {
            return [
                'id'          => (string) $size->graft_size_id,
                'size'        => $size->size,
                'area'        => (float) $size->area,
                'price'       => (float) $size->price,
                'stock'       => (int) $size->stock,
                'graftStatus' => (int) $size->graft_status,
            ];
        });

        $logoUrl = $brand->logo ? asset('storage/' . $brand->logo) : null;

        return response()->json([
            'message' => 'Brand updated successfully',
            'data'    => [
                'id'              => (string) $brand->brand_id,
                'brandName'       => $brand->brand_name,
                'manufacturerName' => $manufacturer?->manufacturer_name,
                'mue'             => (int) $brand->mue,
                'description'     => $brand->description,
                'brandStatus'     => (int) $brand->brand_status,
                'productType'     => $brand->product_type == 0 ? 'Graft' : 'Device',
                'graftSizes'      => $formattedGraftSizes,
                'logoUrl'         => $logoUrl,
                'updatedAt'       => $brand->updated_at,
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

        // Log successful toggle
        $this->logAudit($request, $actionType, "Brand status toggled to {$statusText}: {$brand->brand_name} (from {$oldStatus})", $id);

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

        // Delete logo if exists
        if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete(); // Soft delete

        // Log successful deletion
        $this->logAudit($request, 'brand_delete', "Brand deleted: {$name}", $id);

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

            $this->logAudit($request, 'brand_archive', "Brand archived: {$brand->brand_name} (from status {$oldStatus})", $id);
        }

        return response()->json([
            'message' => 'Brand archived successfully',
            'data'    => [
                'brandStatus' => (int) $brand->brand_status,
            ],
        ]);
    }
}

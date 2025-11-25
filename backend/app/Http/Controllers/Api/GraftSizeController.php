<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GraftSize;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\AuditLogger;

class GraftSizeController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_graft_sizes';
    }

    protected function getEntityType()
    {
        return 'graft_size';
    }

    public function getAllGraftSizes(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $status = $request->query('status');
        $search = $request->query('search');
        $query = GraftSize::with(['brand.manufacturer'])->orderBy('created_at', 'desc');
        if ($status !== null) {
            $query->where('graft_status', (int) $status);
        }
        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('size', 'like', $like)
                    ->orWhereRaw('CAST(area AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('CAST(price AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('CAST(stock AS CHAR) LIKE ?', [$like])
                    ->orWhereHas('brand', function ($qb) use ($like) {
                        $qb->where('brand_name', 'like', $like);
                    })
                    ->orWhereHas('brand.manufacturer', function ($qb) use ($like) {
                        $qb->where('manufacturer_name', 'like', $like);
                    });
            });
        }
        $grafts = $query->paginate($perPage, ['*'], 'page', $page);
        $formattedGrafts = $grafts->map(function ($graft) {
            $brand = $graft->brand ?? null;
            $manufacturer = $brand?->manufacturer ?? null;
            return [
                'graft_size_id' => (string) $graft->graft_size_id,
                'brand_id' => (string) $graft->brand_id,
                'size' => $graft->size ?? 'N/A',
                'area' => (float) ($graft->area ?? 0),
                'price' => (float) ($graft->price ?? 0),
                'stock' => (int) ($graft->stock ?? 0),
                'graft_status' => (int) ($graft->graft_status ?? 0),
                'created_at' => $graft->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'updated_at' => $graft->updated_at?->toDateTimeString() ?? 'N/A',
                'brand' => [
                    'brand_id' => $brand ? (string) $brand->brand_id : null,
                    'brand_name' => $brand?->brand_name ?? 'N/A',
                ],
                'manufacturer' => [
                    'manufacturer_id' => $manufacturer ? (string) $manufacturer->manufacturer_id : null,
                    'manufacturer_name' => $manufacturer?->manufacturer_name ?? 'N/A',
                ],
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
        try {
            $validator = Validator::make($request->all(), [
                'brand_id' => ['required', 'integer', 'exists:woundmed_brands,brand_id'],
                'graftSizes' => ['required', 'array', 'min:1'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $validated = $validator->validated();
            $graftSizesData = $request->input('graftSizes');
            if (!is_array($graftSizesData)) {
                throw ValidationException::withMessages(['graftSizes' => 'Graft sizes must be an array.']);
            }
            // Validate individual graft sizes
            foreach ($graftSizesData as $index => $sizeData) {
                $sizeValidator = Validator::make($sizeData, [
                    'size' => ['required', 'string', 'max:255'],
                    'area' => ['required', 'numeric', 'min:0'],
                    'price' => ['required', 'numeric', 'min:0'],
                    'stock' => ['nullable', 'integer', 'min:0'],
                ]);
                if ($sizeValidator->fails()) {
                    throw ValidationException::withMessages([
                        "graftSizes.{$index}.size" => $sizeValidator->errors()->get('size'),
                        "graftSizes.{$index}.area" => $sizeValidator->errors()->get('area'),
                        "graftSizes.{$index}.price" => $sizeValidator->errors()->get('price'),
                        "graftSizes.{$index}.stock" => $sizeValidator->errors()->get('stock'),
                    ]);
                }
            }
            $created = [];
            foreach ($graftSizesData as $sizeData) {
                $graft = GraftSize::create([
                    'brand_id' => $validated['brand_id'],
                    'size' => $sizeData['size'],
                    'area' => $sizeData['area'],
                    'price' => $sizeData['price'],
                    'stock' => $sizeData['stock'] ?? 0,
                    'graft_status' => 0, // Default: Active
                ]);
                $graft->load(['brand.manufacturer']);
                // Format the created graft
                $brand = $graft->brand ?? null;
                $manufacturer = $brand?->manufacturer ?? null;
                $created[] = [
                    'graft_size_id' => (string) $graft->graft_size_id,
                    'brand_id' => (string) $validated['brand_id'],
                    'size' => $graft->size ?? 'N/A',
                    'area' => (float) ($graft->area ?? 0),
                    'price' => (float) ($graft->price ?? 0),
                    'stock' => (int) ($graft->stock ?? 0),
                    'graft_status' => (int) ($graft->graft_status ?? 0),
                    'created_at' => $graft->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    'updated_at' => $graft->updated_at?->toDateTimeString() ?? 'N/A',
                    'brand' => [
                        'brand_id' => $brand ? (string) $brand->brand_id : null,
                        'brand_name' => $brand?->brand_name ?? 'N/A',
                    ],
                    'manufacturer' => [
                        'manufacturer_id' => $manufacturer ? (string) $manufacturer->manufacturer_id : null,
                        'manufacturer_name' => $manufacturer?->manufacturer_name ?? 'N/A',
                    ],
                ];
                // Log audit trail
                $this->logAudit($request, 'graft_size_create', "Graft size created: {$sizeData['size']}", $graft->graft_size_id);
            }
            return response()->json([
                'message' => 'Graft sizes created successfully',
                'data' => $created
            ], 201);
        } catch (\Exception $e) {
            // Log for debugging
            Log::error('GraftSize create failed: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'message' => 'Failed to create graft sizes: ' . $e->getMessage()
            ], 500);
        }
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
        $oldSize = $graft->size;
        $graft->update($validated);
        $graft->load(['brand.manufacturer']);
        // Fixed: Null checks and consistent formatting
        $brand = $graft->brand ?? null;
        $manufacturer = $brand?->manufacturer ?? null;
        $formatted = [
            'graft_size_id' => (string) $graft->graft_size_id,
            'brand_id' => (string) $graft->brand_id,
            'size' => $graft->size ?? 'N/A',
            'area' => (float) ($graft->area ?? 0),
            'price' => (float) ($graft->price ?? 0),
            'stock' => (int) $graft->stock,
            'graft_status' => (int) $graft->graft_status, // Preserves current status (0/1/2)
            'created_at' => $graft->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
            'updated_at' => $graft->updated_at?->toDateTimeString() ?? 'N/A',
            'brand' => [
                'brand_id' => $brand ? (string) $brand->brand_id : null,
                'brand_name' => $brand?->brand_name ?? 'N/A',
            ],
            'manufacturer' => [
                'manufacturer_id' => $manufacturer ? (string) $manufacturer->manufacturer_id : null,
                'manufacturer_name' => $manufacturer?->manufacturer_name ?? 'N/A',
            ],
        ];
        // Log audit trail
        $this->logAudit($request, 'graft_size_update', "Graft size updated: {$oldSize} -> {$graft->size}", $id);
        return response()->json([
            'message' => 'Graft size updated successfully',
            'data' => $formatted
        ]);
    }

    public function toggleInactiveGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);
        if ($graft->graft_status !== 0) {
            return response()->json(['message' => 'Cannot deactivate: not active'], 400);
        }
        $graft->graft_status = 1; // Set to Inactive
        $graft->save();
        // Log audit trail
        $this->logAudit(request(), 'graft_size_deactivate', "Graft size deactivated: {$graft->size}", $id);
        return response()->json([
            'message' => 'Graft size deactivated successfully',
            'data' => ['graft_status' => 1]
        ]);
    }

    public function activateGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);
        if ($graft->graft_status !== 1) {
            return response()->json(['message' => 'Cannot activate: not inactive'], 400);
        }
        $graft->graft_status = 0; // Set to Active
        $graft->save();
        // Log audit trail
        $this->logAudit(request(), 'graft_size_activate', "Graft size activated: {$graft->size}", $id);
        return response()->json([
            'message' => 'Graft size activated successfully',
            'data' => ['graft_status' => 0]
        ]);
    }

    public function archiveGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);
        if ($graft->graft_status !== 0 && $graft->graft_status !== 1) { // Allow from Active or Inactive
            return response()->json(['message' => 'Cannot archive: invalid status'], 400);
        }
        $graft->graft_status = 2; // Set to Archive
        $graft->save();
        // Log audit trail
        $this->logAudit(request(), 'graft_size_archive', "Graft size archived: {$graft->size}", $id);
        return response()->json([
            'message' => 'Graft size archived successfully',
            'data' => ['graft_status' => 2]
        ]);
    }

    public function unarchiveGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);
        if ($graft->graft_status !== 2) {
            return response()->json(['message' => 'Cannot unarchive: not archived'], 400);
        }
        $graft->graft_status = 0; // Restore to Active
        $graft->save();
        // Log audit trail
        $this->logAudit(request(), 'graft_size_unarchive', "Graft size unarchived: {$graft->size}", $id);
        return response()->json([
            'message' => 'Graft size unarchived successfully',
            'data' => ['graft_status' => 0]
        ]);
    }

    public function deleteGraftSize($id)
    {
        $graft = GraftSize::findOrFail($id);
        if ($graft->graft_status !== 2) {
            return response()->json(['message' => 'Can only delete archived graft sizes'], 400);
        }
        $size = $graft->size;
        $graft->delete();
        // Log audit trail
        $this->logAudit(request(), 'graft_size_delete', "Graft size deleted: {$size}", $id);
        return response()->json([
            'message' => 'Graft size deleted successfully'
        ]);
    }

    // New: Stats endpoint
    public function getGraftStats()
    {
        $total = GraftSize::count();
        $active = GraftSize::where('graft_status', 0)->count();
        $inactive = GraftSize::where('graft_status', 1)->count();
        $archived = GraftSize::where('graft_status', 2)->count();
        // Top 10 brands by count (with manufacturer)
        $brands = GraftSize::selectRaw('woundmed_brands.brand_id, woundmed_brands.brand_name, COUNT(*) as count')
            ->leftJoin('woundmed_brands', 'graft_sizes.brand_id', '=', 'woundmed_brands.brand_id')
            ->groupBy('woundmed_brands.brand_id', 'woundmed_brands.brand_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($brand) {
                return [
                    'brand_id' => (string) $brand->brand_id,
                    'brand_name' => $brand->brand_name ?? 'Unknown',
                    'count' => (int) $brand->count,
                ];
            });

        echo '<pre>';
        print_r($brands);
        exit();
        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'archived' => $archived, // New: For frontend
            'brands' => $brands,
        ]);
    }

    public function getAllBrands()
    {
        try {
            $brands = Brand::with('manufacturer')->orderBy('brand_name')->get();

            $formattedBrands = $brands->map(function ($brand) {
                $manufacturer = $brand->manufacturer ?? null;
                return [
                    'brand_id' => (string) $brand->brand_id,  // Cast to string for consistency
                    'brand_name' => $brand->brand_name ?? 'N/A',
                    'manufacturer' => $manufacturer ? [
                        'manufacturer_id' => (string) $manufacturer->manufacturer_id,  // Cast to string
                        'manufacturer_name' => $manufacturer->manufacturer_name ?? 'N/A',
                    ] : null,
                ];
            });

            return response()->json([
                'brands' => $formattedBrands,  // Use 'brands' key for simplicity
                'message' => 'Brands fetched successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch brands: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch brands',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

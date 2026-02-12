<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GraftSize;
use App\Models\OtherProduct;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Traits\AuditLogger;
use Illuminate\Support\Arr;

class ProductController extends Controller
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
                'item_no'       => $graft->item_no ?? '',
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
                    'item_no'   => $sizeData['item_no'],
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
        $oldBrandId = $graft->brand_id;
        $oldSize = $graft->size;

        $validated = $request->validate([
            'brand_id' => ['sometimes', 'integer', 'exists:woundmed_brands,brand_id'],
            'item_no'   => ['sometimes', 'required', 'string', 'max:50'],
            'size' => ['required', 'string', 'max:255'],
            'area' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        // Update brand_id if provided and changed
        if (isset($validated['brand_id']) && $validated['brand_id'] !== $oldBrandId) {
            $graft->brand_id = $validated['brand_id'];
        }

        // Update other fields, excluding brand_id (already handled above)
        $graft->update(Arr::except($validated, ['brand_id']));

        $graft->load(['brand.manufacturer']);

        // Format response (unchanged)
        $brand = $graft->brand ?? null;
        $manufacturer = $brand?->manufacturer ?? null;
        $formatted = [
            'graft_size_id' => (string) $graft->graft_size_id,
            'brand_id' => (string) $graft->brand_id,
            'size' => $graft->size ?? 'N/A',
            'area' => (float) ($graft->area ?? 0),
            'price' => (float) ($graft->price ?? 0),
            'stock' => (int) $graft->stock,
            'graft_status' => (int) $graft->graft_status,
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

        // Enhanced audit log for brand change
        $changes = [];
        if (isset($validated['brand_id']) && $validated['brand_id'] !== $oldBrandId) {
            $oldBrand = Brand::find($oldBrandId);
            $newBrand = Brand::find($validated['brand_id']);
            $changes[] = "Brand changed: {$oldBrand?->brand_name} -> {$newBrand?->brand_name}";
        }
        $changes[] = "Size updated: {$oldSize} -> {$graft->size}";
        $this->logAudit($request, 'graft_size_update', implode('; ', $changes), $id);

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

    public function getGraftStats()
    {
        $total = GraftSize::count();
        $active = GraftSize::where('graft_status', 0)->count();
        $inactive = GraftSize::where('graft_status', 1)->count();
        $archived = GraftSize::where('graft_status', 2)->count();

        // Top 10 brands by count (with manufacturer)
        $brands = GraftSize::selectRaw('woundmed_brands.brand_id, woundmed_brands.brand_name, COUNT(*) as count')
            ->leftJoin('woundmed_brands', 'woundmed_graft_sizes.brand_id', '=', 'woundmed_brands.brand_id')  // Fixed table name
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

    # --- Other Product Methods (New) ---
    public function getAllOtherProducts(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $status = $request->query('status');
        $search = $request->query('search');

        $query = OtherProduct::orderBy('created_at', 'desc');

        if ($status !== null) {
            $query->where('status', (int) $status); // Assuming 'status' field added
        }

        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('product_name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereRaw('CAST(price AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('CAST(stock AS CHAR) LIKE ?', [$like]);
            });
        }

        $products = $query->paginate($perPage, ['*'], 'page', $page);

        $formattedProducts = $products->map(function ($product) {
            return [
                'other_product_id' => (string) $product->other_product_id,
                'product_type' => (int) $product->product_type,
                'product_name' => $product->product_name,
                'price' => (float) ($product->price ?? 0),
                'stock' => (int) ($product->stock ?? 0),
                'description' => $product->description,
                'status' => (int) ($product->status ?? 0), // Assuming status field
                'created_at' => $product->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'updated_at' => $product->updated_at?->toDateTimeString() ?? 'N/A',
            ];
        });

        return response()->json([
            'otherProductData' => $formattedProducts,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    public function addOtherProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_type' => ['required', 'integer', 'in:0,1'], // 0=Wound Supplies, 1=Devices
                'product_name' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $product = OtherProduct::create([
                'product_type' => $validated['product_type'],
                'product_name' => $validated['product_name'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'description' => $validated['description'] ?? null,
                'status' => 0,
            ]);

            $formatted = [
                'other_product_id' => (string) $product->other_product_id,
                // ... (add other fields as in getAllOtherProducts)
            ];

            $this->logAudit($request, 'other_product_create', "Other product created: {$validated['product_name']}", $product->other_product_id, 0, null, 'woundmed_other_products', 'other_product');

            return response()->json([
                'message' => 'Other product created successfully',
                'data' => $formatted
            ], 201);
        } catch (\Exception $e) {
            Log::error('OtherProduct create failed: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'message' => 'Failed to create other product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOtherProduct(Request $request, $id)
    {
        $product = OtherProduct::findOrFail($id);

        try {
            $validator = Validator::make($request->all(), [
                'product_type'  => ['sometimes', 'required', 'integer', 'in:0,1'],
                'product_name'  => ['sometimes', 'required', 'string', 'max:255'],
                'price'         => ['sometimes', 'required', 'numeric', 'min:0'],
                'stock'         => ['sometimes', 'required', 'integer', 'min:0'],
                'description'   => ['nullable', 'string', 'max:1000'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Only update fields that were actually sent
            $product->fill($validated);
            $product->save();

            // Optional: reload fresh data with relations if needed
            $product->refresh();

            // Format response similar to your getAllOtherProducts
            $formatted = [
                'other_product_id' => (string) $product->other_product_id,
                'product_type'     => (int) $product->product_type,
                'product_name'     => $product->product_name,
                'price'            => (float) ($product->price ?? 0),
                'stock'            => (int) ($product->stock ?? 0),
                'description'      => $product->description ?? null,
                'status'           => (int) ($product->status ?? 0),
                'created_at'       => $product->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                'updated_at'       => $product->updated_at?->toDateTimeString() ?? 'N/A',
            ];

            // Audit log
            $this->logAudit(
                $request,
                'other_product_update',
                "Other product updated: {$product->product_name}",
                $product->other_product_id
            );

            return response()->json([
                'message' => 'Other product updated successfully',
                'data'    => $formatted
            ]);
        } catch (\Exception $e) {
            Log::error('OtherProduct update failed: ' . $e->getMessage(), [
                'id'      => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to update other product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOtherProductsStats()
    {
        $total    = OtherProduct::count();
        $active   = OtherProduct::where('status', 0)->count();   // assuming you add status column
        $inactive = OtherProduct::where('status', 1)->count();
        $archived = OtherProduct::where('status', 2)->count();

        // Optional: breakdown by product_type
        $types = OtherProduct::selectRaw('product_type, COUNT(*) as count')
            ->groupBy('product_type')
            ->get()
            ->map(function ($row) {
                return [
                    'type'  => (int) $row->product_type,
                    'label' => $row->product_type === 0 ? 'Wound Supplies' : 'Devices',
                    'count' => (int) $row->count,
                ];
            });

        return response()->json([
            'total'    => $total,
            'active'   => $active,
            'inactive' => $inactive,
            'archived' => $archived,
            'types'    => $types,           // similar to brands breakdown
        ]);
    }

    public function deactivateOtherProduct($id)
    {
        $product = OtherProduct::findOrFail($id);
        if ($product->status !== 0) {
            return response()->json(['message' => 'Cannot deactivate: not active'], 400);
        }
        $product->status = 1; // Set to Inactive
        $product->save();
        $this->logAudit(request(), 'other_product_deactivate', "Other product deactivated: {$product->product_name}", $id);
        return response()->json([
            'message' => 'Other product deactivated successfully',
            'data' => ['status' => 1]
        ]);
    }

    public function activateOtherProduct($id)
    {
        $product = OtherProduct::findOrFail($id);
        if ($product->status !== 1) {
            return response()->json(['message' => 'Cannot activate: not inactive'], 400);
        }
        $product->status = 0; // Set to Active
        $product->save();
        $this->logAudit(request(), 'other_product_activate', "Other product activated: {$product->product_name}", $id);
        return response()->json([
            'message' => 'Other product activated successfully',
            'data' => ['status' => 0]
        ]);
    }

    public function archiveOtherProduct($id)
    {
        $product = OtherProduct::findOrFail($id);
        if ($product->status !== 0 && $product->status !== 1) { // Allow from Active or Inactive
            return response()->json(['message' => 'Cannot archive: invalid status'], 400);
        }
        $product->status = 2; // Set to Archive
        $product->save();
        $this->logAudit(request(), 'other_product_archive', "Other product archived: {$product->product_name}", $id);
        return response()->json([
            'message' => 'Other product archived successfully',
            'data' => ['status' => 2]
        ]);
    }

    public function unarchiveOtherProduct($id)
    {
        $product = OtherProduct::findOrFail($id);
        if ($product->status !== 2) {
            return response()->json(['message' => 'Cannot unarchive: not archived'], 400);
        }
        $product->status = 0; // Restore to Active
        $product->save();
        $this->logAudit(request(), 'other_product_unarchive', "Other product unarchived: {$product->product_name}", $id);
        return response()->json([
            'message' => 'Other product unarchived successfully',
            'data' => ['status' => 0]
        ]);
    }

    public function deleteOtherProduct($id)
    {
        $product = OtherProduct::findOrFail($id);
        if ($product->status !== 2) {
            return response()->json(['message' => 'Can only delete archived products'], 400);
        }
        $productName = $product->product_name;
        $product->delete();
        $this->logAudit(request(), 'other_product_delete', "Other product deleted: {$productName}", $id);
        return response()->json([
            'message' => 'Other product deleted successfully'
        ]);
    }
}

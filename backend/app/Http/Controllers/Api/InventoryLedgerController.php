<?php

// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - CONTROLLER
// ----------------------------------------------------------------------------
// This controller handles all CRUD operations for the standalone Inventory
// Ledger Management feature. To remove this module, delete this file,
// remove the routes, and rollback the migration.
// ============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLedger;
use App\Models\Brand;
use App\Models\Clinic;
use App\Models\GraftSize;
use App\Models\OtherProduct;
use App\Models\Invoice;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryLedgerController extends Controller
{
    /**
     * Combined init endpoint: returns brands, clinics, products, invoices, and stats
     * in a single request to minimize HTTP calls on page load.
     */
    public function init(): JsonResponse
    {
        // Brands (id + name only)
        $brands = Brand::select('brand_id', 'brand_name')
            ->whereNull('deleted_at')
            ->orderBy('brand_name')
            ->get()
            ->map(fn($b) => ['id' => (string) $b->brand_id, 'name' => $b->brand_name]);

        // Clinics (id + name only)
        $clinics = Clinic::select('clinic_id', 'clinic_name')
            ->whereNull('deleted_at')
            ->orderBy('clinic_name')
            ->get()
            ->map(fn($c) => ['id' => (string) $c->clinic_id, 'name' => $c->clinic_name]);

        // Products
        $graftSizes = GraftSize::select('graft_size_id', 'size', 'brand_id', 'area', 'price')
            ->with('brand:brand_id,brand_name')
            ->whereNull('deleted_at')
            ->orderBy('size')
            ->get()
            ->map(fn($gs) => [
                'id' => (string) $gs->graft_size_id,
                'type' => 'graft',
                'name' => $gs->size,
                'brand_id' => (string) $gs->brand_id,
                'brand_name' => $gs->brand?->brand_name ?? 'N/A',
                'area' => (float) ($gs->area ?? 0),
                'price' => (float) ($gs->price ?? 0),
            ]);

        $otherProducts = OtherProduct::select('other_product_id', 'product_name', 'price')
            ->whereNull('deleted_at')
            ->orderBy('product_name')
            ->get()
            ->map(fn($op) => [
                'id' => (string) $op->other_product_id,
                'type' => 'other_product',
                'name' => $op->product_name,
                'price' => (float) ($op->price ?? 0),
            ]);

        // Invoices (recent 100 only)
        $invoices = Invoice::select('id', 'invoice_number', 'status', 'clinic_id')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(fn($inv) => [
                'id' => (string) $inv->id,
                'invoice_number' => $inv->invoice_number,
                'status' => $inv->status,
                'clinic_id' => (string) $inv->clinic_id,
            ]);

        // Stats (single query with conditional aggregates)
        $stats = $this->getOptimizedStats();

        return response()->json([
            'success' => true,
            'data' => [
                'brands' => $brands,
                'clinics' => $clinics,
                'graft_sizes' => $graftSizes,
                'other_products' => $otherProducts,
                'invoices' => $invoices,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Get all inventory ledger entries with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $query = InventoryLedger::with([
            'brand:brand_id,brand_name',
            'clinic:clinic_id,clinic_name',
            'order:order_id,order_number,order_code',
            'invoice:id,invoice_number,status',
            'graftSizeProduct:graft_size_id,size',
            'otherProduct:other_product_id,product_name',
        ]);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($bq) use ($search) {
                      $bq->where('brand_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('clinic', function ($cq) use ($search) {
                      $cq->where('clinic_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            $statusMap = [
                'expected' => 0,
                'delivered' => 1,
                'used' => 2,
                'partially_used' => 3,
                'reassigned' => 4,
                'unused' => 5,
                'expired' => 6,
            ];
            if (isset($statusMap[$request->status])) {
                $query->where('status', $statusMap[$request->status]);
            }
        }

        // Brand filter
        if ($request->has('brand_id') && $request->brand_id !== 'all' && $request->brand_id !== '') {
            $query->where('brand_id', $request->brand_id);
        }

        // Clinic filter
        if ($request->has('clinic_id') && $request->clinic_id !== 'all' && $request->clinic_id !== '') {
            $query->where('clinic_id', $request->clinic_id);
        }

        // Invoice status filter
        if ($request->has('invoice_status') && $request->invoice_status !== 'all' && $request->invoice_status !== '') {
            $query->where('invoice_status', $request->invoice_status);
        }

        // Product type filter
        if ($request->has('product_type') && $request->product_type !== 'all' && $request->product_type !== '') {
            $query->where('product_type', $request->product_type);
        }

        // Order ID filter
        if ($request->has('order_id') && $request->order_id !== 'all' && $request->order_id !== '') {
            $query->where('order_id', $request->order_id);
        }

        $ledgers = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $ledgers->map(function ($ledger) {
            return $this->transformLedger($ledger);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $ledgers->total(),
            'current_page' => $ledgers->currentPage(),
            'per_page' => $ledgers->perPage(),
            'last_page' => $ledgers->lastPage(),
        ]);
    }

    /**
     * Get a single inventory ledger entry by ID.
     */
    public function show(int $id): JsonResponse
    {
        $ledger = InventoryLedger::with(['brand', 'clinic', 'order', 'invoice', 'graftSizeProduct', 'otherProduct'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformLedger($ledger),
        ]);
    }

    /**
     * Create a new inventory ledger entry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|unique:woundmed_inventory_ledger,serial_number',
            'product_type' => 'required|in:graft,other_product',
            'product_id' => 'required|integer',
            'brand_id' => 'nullable|integer|exists:woundmed_brands,brand_id',
            'clinic_id' => 'required_if:product_type,graft|nullable|integer|exists:woundmed_clinics,clinic_id',
            'order_id' => 'nullable|integer|exists:woundmed_orders,order_id',
            'status' => 'required|integer|between:0,6',
            'is_used' => 'nullable|boolean',
            'graft_usage_id' => 'nullable|string',
            'invoice_status' => 'nullable|in:unpaid,paid',
            'invoice_id' => 'nullable|integer|exists:woundmed_invoices,id',
            'notes' => 'nullable|string',
        ]);

        // Validate product_id exists in the respective table
        if ($validated['product_type'] === 'graft') {
            $product = GraftSize::find($validated['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Graft size product not found.',
                ], 422);
            }
        } else {
            $product = OtherProduct::find($validated['product_id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Other product not found.',
                ], 422);
            }
        }

        $ledger = InventoryLedger::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventory ledger entry created successfully.',
            'data' => $this->transformLedger($ledger->fresh(['brand', 'clinic', 'order', 'invoice', 'graftSizeProduct', 'otherProduct'])),
        ], 201);
    }

    /**
     * Update an inventory ledger entry.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $ledger = InventoryLedger::findOrFail($id);

        $validated = $request->validate([
            'serial_number' => 'sometimes|string|unique:woundmed_inventory_ledger,serial_number,' . $id . ',ledger_id',
            'product_type' => 'sometimes|in:graft,other_product',
            'product_id' => 'sometimes|integer',
            'brand_id' => 'nullable|integer|exists:woundmed_brands,brand_id',
            'clinic_id' => 'sometimes|integer|exists:woundmed_clinics,clinic_id',
            'order_id' => 'nullable|integer|exists:woundmed_orders,order_id',
            'status' => 'sometimes|integer|between:0,6',
            'is_used' => 'sometimes|boolean',
            'graft_usage_id' => 'nullable|string',
            'invoice_status' => 'sometimes|in:unpaid,paid',
            'invoice_id' => 'nullable|integer|exists:woundmed_invoices,id',
            'notes' => 'nullable|string',
        ]);

        // Validate product_id if product_type or product_id changed
        if (isset($validated['product_type']) || isset($validated['product_id'])) {
            $productType = $validated['product_type'] ?? $ledger->product_type;
            $productId = $validated['product_id'] ?? $ledger->product_id;

            if ($productType === 'graft') {
                $product = GraftSize::find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Graft size product not found.',
                    ], 422);
                }
            } else {
                $product = OtherProduct::find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Other product not found.',
                    ], 422);
                }
            }
        }

        $ledger->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inventory ledger entry updated successfully.',
            'data' => $this->transformLedger($ledger->fresh(['brand', 'clinic', 'order', 'invoice', 'graftSizeProduct', 'otherProduct'])),
        ]);
    }

    /**
     * Search orders for dropdown suggestions.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $limit = $request->query('limit', 10);

        $query = Orders::select('order_id', 'order_number', 'order_code', 'clinic_id')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('order_code', 'like', "%{$search}%");
            });
        }

        $orders = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $orders->map(function ($order) {
                return [
                    'id' => (string) $order->order_id,
                    'order_number' => $order->order_number,
                    'order_code' => $order->order_code,
                    'display' => $order->order_number ?: $order->order_code ?: (string) $order->order_id,
                ];
            }),
        ]);
    }

    /**
     * Soft delete an inventory ledger entry.
     */
    public function destroy(int $id): JsonResponse
    {
        $ledger = InventoryLedger::findOrFail($id);
        $ledger->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventory ledger entry deleted successfully.',
        ]);
    }

    /**
     * Restore a soft-deleted inventory ledger entry.
     */
    public function restore(int $id): JsonResponse
    {
        $ledger = InventoryLedger::withTrashed()->findOrFail($id);

        if (!$ledger->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Entry is not deleted.',
            ], 400);
        }

        $ledger->restore();

        return response()->json([
            'success' => true,
            'message' => 'Inventory ledger entry restored successfully.',
            'data' => $this->transformLedger($ledger->fresh(['brand', 'clinic', 'order', 'invoice', 'graftSizeProduct', 'otherProduct'])),
        ]);
    }

    /**
     * Get inventory ledger statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getOptimizedStats(),
        ]);
    }

    /**
     * Compute stats in a single query using conditional aggregates.
     */
    private function getOptimizedStats(): array
    {
        $aggregates = InventoryLedger::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN invoice_status = 'paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN invoice_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid,
            SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as used,
            SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as unused_count,
            SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as status_0,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as status_1,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as status_2,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as status_3,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as status_4,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as status_5,
            SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as status_6
        ")->first();

        $statusLabels = [
            0 => 'Expected',
            1 => 'Delivered',
            2 => 'Used',
            3 => 'Partially Used',
            4 => 'Reassigned',
            5 => 'Unused',
            6 => 'Expired',
        ];

        $statusBreakdown = [];
        foreach ($statusLabels as $key => $label) {
            $col = 'status_' . $key;
            $statusBreakdown[] = [
                'status' => $label,
                'count' => (int) ($aggregates->$col ?? 0),
            ];
        }

        return [
            'total' => (int) $aggregates->total,
            'paid' => (int) $aggregates->paid,
            'unpaid' => (int) $aggregates->unpaid,
            'used' => (int) $aggregates->used,
            'unused_count' => (int) $aggregates->unused_count,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    /**
     * Get available products for dropdown (graft sizes + other products).
     */
    public function getProducts(): JsonResponse
    {
        $graftSizes = GraftSize::with('brand')
            ->whereNull('deleted_at')
            ->orderBy('size')
            ->get()
            ->map(function ($gs) {
                return [
                    'id' => (string) $gs->graft_size_id,
                    'type' => 'graft',
                    'name' => $gs->size,
                    'brand_id' => (string) $gs->brand_id,
                    'brand_name' => $gs->brand?->brand_name ?? 'N/A',
                    'area' => (float) ($gs->area ?? 0),
                    'price' => (float) ($gs->price ?? 0),
                ];
            });

        $otherProducts = OtherProduct::whereNull('deleted_at')
            ->orderBy('product_name')
            ->get()
            ->map(function ($op) {
                return [
                    'id' => (string) $op->other_product_id,
                    'type' => 'other_product',
                    'name' => $op->product_name,
                    'price' => (float) ($op->price ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'graft_sizes' => $graftSizes,
                'other_products' => $otherProducts,
            ],
        ]);
    }

    /**
     * Get available invoices for dropdown.
     */
    public function getInvoices(): JsonResponse
    {
        $invoices = Invoice::select('id', 'invoice_number', 'status', 'clinic_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => (string) $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'status' => $inv->status,
                    'clinic_id' => (string) $inv->clinic_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Transform a ledger model into a clean API response array.
     */
    private function transformLedger(InventoryLedger $ledger): array
    {
        $productName = 'Unknown';
        $sizeName = null;

        if ($ledger->product_type === 'graft' && $ledger->graftSizeProduct) {
            $productName = $ledger->graftSizeProduct->size ?? 'Unknown Graft';
            $sizeName = $ledger->graftSizeProduct->size ?? null;
        } elseif ($ledger->product_type === 'other_product' && $ledger->otherProduct) {
            $productName = $ledger->otherProduct->product_name ?? 'Unknown Product';
        }

        return [
            'ledger_id' => $ledger->ledger_id,
            'serial_number' => $ledger->serial_number,
            'product_type' => $ledger->product_type,
            'product_id' => (int) $ledger->product_id,
            'product_name' => $productName,
            'brand_id' => $ledger->brand_id ? (string) $ledger->brand_id : null,
            'brand_name' => $ledger->brand?->brand_name ?? 'N/A',
            'size_name' => $sizeName,
            'clinic_id' => (string) $ledger->clinic_id,
            'clinic_name' => $ledger->clinic?->clinic_name ?? 'Unknown Clinic',
            'order_id' => $ledger->order_id ? (string) $ledger->order_id : null,
            'order_number' => $ledger->order?->order_number ?? null,
            'order_code' => $ledger->order?->order_code ?? null,
            'status' => (int) $ledger->status,
            'status_label' => $ledger->status_label,
            'status_color' => $ledger->status_color,
            'is_used' => (bool) $ledger->is_used,
            'graft_usage_id' => $ledger->graft_usage_id,
            'invoice_status' => $ledger->invoice_status,
            'invoice_id' => $ledger->invoice_id ? (string) $ledger->invoice_id : null,
            'invoice_number' => $ledger->invoice?->invoice_number ?? null,
            'notes' => $ledger->notes,
            'created_at' => $ledger->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $ledger->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

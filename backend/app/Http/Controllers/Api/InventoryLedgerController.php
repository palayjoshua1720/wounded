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
use App\Traits\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryLedgerController extends Controller
{
    use AuditLogger;

    /**
     * HIPAA audit log — entity name for woundmed_audit_logs.
     */
    protected function getEntityName()
    {
        return 'woundmed_inventory_ledger';
    }

    /**
     * HIPAA audit log — entity type classifier.
     */
    protected function getEntityType()
    {
        return 'inventory_ledger';
    }
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
            'graft_usage_id' => 'nullable|string|exists:woundmed_usage_log,graft_log_id',
            'invoice_status' => 'nullable|in:unpaid,paid',
            'invoice_id' => 'nullable|integer|exists:woundmed_invoices,id',
            'notes' => 'nullable|string',
        ], [
            'serial_number.unique' => 'This serial number is already registered in the inventory ledger.',
            'graft_usage_id.exists' => 'No graft usage log matches this ID.',
        ]);

        // Validate serial_number exists in woundmed_graft_sizes.item_no for graft entries
        if ($validated['product_type'] === 'graft') {
            $itemExists = GraftSize::where('item_no', $validated['serial_number'])->exists();
            if (!$itemExists) {
                throw ValidationException::withMessages([
                    'serial_number' => 'This serial number does not match any item number in graft sizes.',
                ]);
            }
        }

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

        // HIPAA audit trail — record inventory ledger creation
        $this->logAudit(
            $request,
            'inventory_ledger_create',
            "Inventory ledger entry created (serial={$ledger->serial_number}, product_type={$ledger->product_type})",
            $ledger->ledger_id
        );

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
            'clinic_id' => 'nullable|integer|exists:woundmed_clinics,clinic_id',
            'order_id' => 'nullable|integer|exists:woundmed_orders,order_id',
            'status' => 'sometimes|integer|between:0,6',
            'is_used' => 'sometimes|boolean',
            'graft_usage_id' => 'nullable|string|exists:woundmed_usage_log,graft_log_id',
            'invoice_status' => 'sometimes|in:unpaid,paid',
            'invoice_id' => 'nullable|integer|exists:woundmed_invoices,id',
            'notes' => 'nullable|string',
        ], [
            'serial_number.unique' => 'This serial number is already registered in the inventory ledger.',
            'graft_usage_id.exists' => 'No graft usage log matches this ID.',
        ]);

        // Determine the effective product_type after this update (new value or existing ledger value).
        $effectiveProductType = $validated['product_type'] ?? $ledger->product_type;

        // clinic_id is only MANDATORY when the entry represents a graft. For
        // "other_product" entries it can legitimately be null.
        if ($effectiveProductType === 'graft'
            && array_key_exists('clinic_id', $validated)
            && $validated['clinic_id'] === null) {
            throw ValidationException::withMessages([
                'clinic_id' => 'Clinic is required for graft entries.',
            ]);
        }

        // Validate serial_number exists in woundmed_graft_sizes.item_no for graft entries
        if (isset($validated['serial_number'])) {
            $productType = $validated['product_type'] ?? $ledger->product_type;
            if ($productType === 'graft') {
                $itemExists = GraftSize::where('item_no', $validated['serial_number'])->exists();
                if (!$itemExists) {
                    throw ValidationException::withMessages([
                        'serial_number' => 'This serial number does not match any item number in graft sizes.',
                    ]);
                }
            }
        }

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

        // HIPAA audit trail — record inventory ledger update
        $this->logAudit(
            $request,
            'inventory_ledger_update',
            "Inventory ledger entry updated (ledger_id={$ledger->ledger_id}, serial={$ledger->serial_number})",
            $ledger->ledger_id
        );

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
        $serial = $ledger->serial_number;
        $ledger->delete();

        // HIPAA audit trail — record inventory ledger deletion
        $this->logAudit(
            request(),
            'inventory_ledger_delete',
            "Inventory ledger entry deleted (ledger_id={$id}, serial={$serial})",
            $id
        );

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

        // HIPAA audit trail — record inventory ledger restoration
        $this->logAudit(
            request(),
            'inventory_ledger_restore',
            "Inventory ledger entry restored (ledger_id={$ledger->ledger_id}, serial={$ledger->serial_number})",
            $ledger->ledger_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory ledger entry restored successfully.',
            'data' => $this->transformLedger($ledger->fresh(['brand', 'clinic', 'order', 'invoice', 'graftSizeProduct', 'otherProduct'])),
        ]);
    }

    /**
     * Download import template with reference data.
     */
    public function downloadTemplate()
    {
        // Fetch reference data - use get() to trigger model decryption
        $brands = Brand::whereNull('deleted_at')->orderBy('brand_name')->get()->pluck('brand_name')->toArray();
        $clinics = Clinic::whereNull('deleted_at')->orderBy('clinic_name')->get()->pluck('clinic_name')->toArray();
        $graftSizes = GraftSize::with('brand')
            ->whereNull('deleted_at')
            ->orderBy('size')
            ->get()
            ->map(fn($gs) => ($gs->brand?->brand_name ?? '') . ' - ' . $gs->size)
            ->toArray();
        $otherProducts = OtherProduct::whereNull('deleted_at')->orderBy('product_name')->get()->pluck('product_name')->toArray();
        $orders = Orders::orderBy('created_at', 'desc')->limit(200)->get()->map(fn($o) => $o->order_number ?: $o->order_code ?: (string) $o->order_id)->toArray();
        $invoices = Invoice::orderBy('created_at', 'desc')->limit(200)->get()->pluck('invoice_number')->toArray();

        // Main template sheet headers
        $mainHeaders = [
            'serial_number', 'product_type', 'brand_name', 'graft_size', 'other_product_name',
            'clinic_name', 'order_number', 'status', 'is_used', 'graft_usage_id',
            'invoice_status', 'invoice_number', 'notes'
        ];

        // Build the workbook
        $writer = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Main Template
        $mainSheet = $writer->getActiveSheet();
        $mainSheet->setTitle('Import Data');
        $mainSheet->fromArray([$mainHeaders], null, 'A1');

        // Add sample row
        $sampleRow = [
            'SN-001', 'graft', 'BrandName', 'Size 1', '',
            'ClinicName', 'ORD-2026-001', 1, 'No', '',
            'unpaid', '', 'Sample notes'
        ];
        $mainSheet->fromArray([$sampleRow], null, 'A2');

        // Auto-size columns
        foreach ($mainHeaders as $colIndex => $header) {
            $mainSheet->getColumnDimension(chr(65 + $colIndex))->setWidth(20);
        }

        // Sheet 2: Brands
        $brandsSheet = $writer->createSheet();
        $brandsSheet->setTitle('Brands');
        $brandsSheet->setCellValue('A1', 'brand_name');
        foreach ($brands as $i => $brand) {
            $brandsSheet->setCellValue('A' . ($i + 2), $brand);
        }

        // Sheet 3: Clinics
        $clinicsSheet = $writer->createSheet();
        $clinicsSheet->setTitle('Clinics');
        $clinicsSheet->setCellValue('A1', 'clinic_name');
        foreach ($clinics as $i => $clinic) {
            $clinicsSheet->setCellValue('A' . ($i + 2), $clinic);
        }

        // Sheet 4: Graft Sizes
        $graftSheet = $writer->createSheet();
        $graftSheet->setTitle('Graft Sizes');
        $graftSheet->setCellValue('A1', 'brand_name - size');
        foreach ($graftSizes as $i => $size) {
            $graftSheet->setCellValue('A' . ($i + 2), $size);
        }

        // Sheet 5: Other Products
        $otherSheet = $writer->createSheet();
        $otherSheet->setTitle('Other Products');
        $otherSheet->setCellValue('A1', 'product_name');
        foreach ($otherProducts as $i => $product) {
            $otherSheet->setCellValue('A' . ($i + 2), $product);
        }

        // Sheet 6: Orders
        $ordersSheet = $writer->createSheet();
        $ordersSheet->setTitle('Orders');
        $ordersSheet->setCellValue('A1', 'order_number');
        foreach ($orders as $i => $order) {
            $ordersSheet->setCellValue('A' . ($i + 2), $order);
        }

        // Sheet 7: Invoices
        $invoicesSheet = $writer->createSheet();
        $invoicesSheet->setTitle('Invoices');
        $invoicesSheet->setCellValue('A1', 'invoice_number');
        foreach ($invoices as $i => $invoice) {
            $invoicesSheet->setCellValue('A' . ($i + 2), $invoice);
        }

        // Sheet 8: Instructions
        $instrSheet = $writer->createSheet();
        $instrSheet->setTitle('Instructions');
        $instructions = [
            ['Field', 'Required', 'Description'],
            ['serial_number', 'Yes', 'Unique serial number for the item'],
            ['product_type', 'Yes', 'Must be "graft" or "other_product"'],
            ['brand_name', 'For graft', 'Brand name (must match Brands sheet)'],
            ['graft_size', 'For graft', 'Format: "BrandName - Size" (must match Graft Sizes sheet)'],
            ['other_product_name', 'For other_product', 'Product name (must match Other Products sheet)'],
            ['clinic_name', 'For graft', 'Clinic name (must match Clinics sheet)'],
            ['order_number', 'No', 'Order number (must match Orders sheet)'],
            ['status', 'Yes', '0=Expected, 1=Delivered, 2=Used, 3=Partially Used, 4=Reassigned, 5=Unused, 6=Expired'],
            ['is_used', 'No', 'Yes or No (default: No)'],
            ['graft_usage_id', 'No', 'Graft usage ID if used'],
            ['invoice_status', 'No', 'paid or unpaid (default: unpaid)'],
            ['invoice_number', 'No', 'Invoice number (must match Invoices sheet)'],
            ['notes', 'No', 'Any additional notes'],
        ];
        $instrSheet->fromArray($instructions, null, 'A1');
        $instrSheet->getColumnDimension('A')->setWidth(25);
        $instrSheet->getColumnDimension('B')->setWidth(15);
        $instrSheet->getColumnDimension('C')->setWidth(60);

        // Write to output
        $filename = 'inventory_ledger_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        
        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($writer);
        $xlsxWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Import inventory ledger entries from Excel/CSV file.
     * Accepts name-based columns and resolves them to IDs automatically.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('file');
        $results = [
            'total_rows' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'imported_ids' => [],
        ];

        // Pre-load reference data for name-to-ID resolution (use get() to trigger decryption)
        $brandsByName = Brand::whereNull('deleted_at')->get()->pluck('brand_id', 'brand_name')->toArray();
        $clinicsByName = Clinic::whereNull('deleted_at')->get()->pluck('clinic_id', 'clinic_name')->toArray();
        $otherProductsByName = OtherProduct::whereNull('deleted_at')->get()->pluck('other_product_id', 'product_name')->toArray();
        $ordersByNumber = [];
        foreach (Orders::get() as $order) {
            $id = $order->order_id;
            if (!empty($order->order_number)) {
                $ordersByNumber[$order->order_number] = $id;
            }
            if (!empty($order->order_code)) {
                $ordersByNumber[$order->order_code] = $id;
            }
        }
        $invoicesByNumber = Invoice::get()->pluck('id', 'invoice_number')->toArray();

        // Build graft sizes lookup: "BrandName - Size" => graft_size_id
        $graftSizesByFullName = [];
        $graftSizes = GraftSize::with('brand')->whereNull('deleted_at')->get();
        foreach ($graftSizes as $gs) {
            $fullName = ($gs->brand?->brand_name ?? '') . ' - ' . $gs->size;
            $graftSizesByFullName[$fullName] = $gs->graft_size_id;
        }

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray(new class {}, $file)[0];
            $headerRow = array_shift($rows);
            
            $headerMap = [];
            foreach ($headerRow as $index => $header) {
                $headerMap[$index] = strtolower(trim($header));
            }

            foreach ($rows as $rowIndex => $row) {
                $results['total_rows']++;
                $rowData = [];
                
                foreach ($row as $index => $value) {
                    if (isset($headerMap[$index])) {
                        $rowData[$headerMap[$index]] = is_string($value) ? trim($value) : $value;
                    }
                }

                // Skip empty rows
                if (empty(array_filter($rowData))) {
                    $results['total_rows']--;
                    continue;
                }

                try {
                    $errors = [];
                    $resolvedIds = [];

                    // Validate serial_number
                    if (empty($rowData['serial_number'])) {
                        $errors[] = 'Serial number is required';
                    } elseif (InventoryLedger::where('serial_number', $rowData['serial_number'])->exists()) {
                        $errors[] = 'Serial number already exists in ledger';
                    }

                    // Validate product_type
                    if (empty($rowData['product_type']) || !in_array($rowData['product_type'], ['graft', 'other_product'])) {
                        $errors[] = 'Product type must be "graft" or "other_product"';
                    }

                    // Validate status
                    if (!isset($rowData['status']) || !is_numeric($rowData['status']) || $rowData['status'] < 0 || $rowData['status'] > 6) {
                        $errors[] = 'Status must be a number between 0 and 6';
                    }

                    // Resolve names to IDs based on product_type
                    if (!empty($rowData['product_type'])) {
                        if ($rowData['product_type'] === 'graft') {
                            // Resolve brand_name
                            if (!empty($rowData['brand_name'])) {
                                $brandName = $rowData['brand_name'];
                                if (isset($brandsByName[$brandName])) {
                                    $resolvedIds['brand_id'] = $brandsByName[$brandName];
                                } else {
                                    $errors[] = "Brand not found: '{$brandName}'";
                                }
                            } else {
                                $errors[] = 'Brand name is required for graft products';
                            }

                            // Resolve graft_size (format: "BrandName - Size")
                            if (!empty($rowData['graft_size'])) {
                                $graftSizeName = $rowData['graft_size'];
                                if (isset($graftSizesByFullName[$graftSizeName])) {
                                    $resolvedIds['product_id'] = $graftSizesByFullName[$graftSizeName];
                                } else {
                                    $errors[] = "Graft size not found: '{$graftSizeName}'. Expected format: 'BrandName - Size'";
                                }
                            } else {
                                $errors[] = 'Graft size is required for graft products';
                            }

                            // Resolve clinic_name
                            if (!empty($rowData['clinic_name'])) {
                                $clinicName = $rowData['clinic_name'];
                                if (isset($clinicsByName[$clinicName])) {
                                    $resolvedIds['clinic_id'] = $clinicsByName[$clinicName];
                                } else {
                                    $errors[] = "Clinic not found: '{$clinicName}'";
                                }
                            } else {
                                $errors[] = 'Clinic name is required for graft products';
                            }
                        } else {
                            // other_product
                            if (!empty($rowData['other_product_name'])) {
                                $productName = $rowData['other_product_name'];
                                if (isset($otherProductsByName[$productName])) {
                                    $resolvedIds['product_id'] = $otherProductsByName[$productName];
                                } else {
                                    $errors[] = "Other product not found: '{$productName}'";
                                }
                            } else {
                                $errors[] = 'Other product name is required for other_product type';
                            }
                        }
                    }

                    // Resolve optional order_number
                    if (!empty($rowData['order_number'])) {
                        $orderNumber = $rowData['order_number'];
                        if (isset($ordersByNumber[$orderNumber])) {
                            $resolvedIds['order_id'] = $ordersByNumber[$orderNumber];
                        } else {
                            $errors[] = "Order not found: '{$orderNumber}'. Use the order number or order code (e.g. ORD-...)";
                        }
                    }

                    // Resolve optional invoice_number
                    if (!empty($rowData['invoice_number'])) {
                        $invoiceNumber = $rowData['invoice_number'];
                        if (isset($invoicesByNumber[$invoiceNumber])) {
                            $resolvedIds['invoice_id'] = $invoicesByNumber[$invoiceNumber];
                        } else {
                            $errors[] = "Invoice not found: '{$invoiceNumber}'";
                        }
                    }

                    // If there are validation errors, skip this row
                    if (!empty($errors)) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowIndex + 2,
                            'serial_number' => $rowData['serial_number'] ?? 'N/A',
                            'errors' => $errors,
                        ];
                        continue;
                    }

                    // Parse is_used
                    $isUsed = false;
                    if (!empty($rowData['is_used'])) {
                        $val = strtolower($rowData['is_used']);
                        $isUsed = in_array($val, ['yes', 'true', '1', 'y']);
                    }

                    // Create the ledger entry
                    $ledgerData = [
                        'serial_number' => $rowData['serial_number'],
                        'product_type' => $rowData['product_type'],
                        'product_id' => $resolvedIds['product_id'],
                        'brand_id' => $resolvedIds['brand_id'] ?? null,
                        'clinic_id' => $resolvedIds['clinic_id'] ?? null,
                        'order_id' => $resolvedIds['order_id'] ?? null,
                        'status' => (int) $rowData['status'],
                        'is_used' => $isUsed,
                        'graft_usage_id' => !empty($rowData['graft_usage_id']) ? $rowData['graft_usage_id'] : null,
                        'invoice_status' => !empty($rowData['invoice_status']) ? $rowData['invoice_status'] : 'unpaid',
                        'invoice_id' => $resolvedIds['invoice_id'] ?? null,
                        'notes' => !empty($rowData['notes']) ? $rowData['notes'] : null,
                    ];

                    $ledger = InventoryLedger::create($ledgerData);
                    $results['successful']++;
                    $results['imported_ids'][] = $ledger->ledger_id;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowIndex + 2,
                        'serial_number' => $rowData['serial_number'] ?? 'N/A',
                        'errors' => [$e->getMessage()],
                    ];
                }
            }

            // HIPAA audit trail
            $this->logAudit(
                $request,
                'inventory_ledger_import',
                sprintf(
                    'Bulk import completed: %d total rows, %d successful, %d failed. Imported IDs: [%s]',
                    $results['total_rows'],
                    $results['successful'],
                    $results['failed'],
                    implode(', ', $results['imported_ids'])
                )
            );

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Import completed: %d entries imported successfully, %d failed.',
                    $results['successful'],
                    $results['failed']
                ),
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            $this->logAudit(
                $request,
                'inventory_ledger_import_failed',
                'Import failed with error: ' . $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to process the file: ' . $e->getMessage(),
            ], 500);
        }
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
            $productName = 'Graft';
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

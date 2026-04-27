<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PatientInfo;
use App\Models\Brand;
use App\Models\Clinic;
use App\Models\IVR;
use App\Models\GraftSize;
use App\Models\Manufacturer;
use App\Models\OtherProduct;
use App\Models\Orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\EmailService;
use App\Template\OrderNotificationEmail;
use App\Template\OtherProductOrderNotificationEmail;
use App\Template\FollowupOrderNotificationEmail;
use App\Helpers\OrderHelper;
use Illuminate\Support\Facades\Storage;
use App\Traits\AuditLogger;

class OrderController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_orders';
    }

    protected function getEntityType()
    {
        return 'order';
    }

    public function getAllOrders(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        try {
            $orders = Orders::with(['clinic', 'clinician', 'patient', 'brand.manufacturer', 'graft', 'ivr.manufacturer'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'order_data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                ]
            ]);
        } catch (\Exception  $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllOrdersById(Request $request, $orderId)
    {
        try {
            $order = Orders::with([
                'clinic',
                'clinician',
                'patient',
                'brand.manufacturer',
                'graft',
                'ivr'
            ])
            ->where('order_id', $orderId)
            ->whereNull('deleted_at')
            ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'order'   => $order
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllClinics(Request $request)
    {
        try {
            $clinics = Clinic::with(['clinicians'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'clinic_data' => $clinics,
            ]);
        } catch (\Exception  $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch clinics: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllPatients(Request $request)
    {
        try {
            $clinics = PatientInfo::with(['clinics', 'ivrs.manufacturer.brands', 'user'])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'patient_data' => $clinics,
            ]);
        } catch (\Exception  $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch clinics: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAllBrands(Request $request)
    {
        $brandData = Brand::with(['manufacturer',])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'brand_data' => $brandData,
        ]);
    }

    public function getAllGraftSizes(Request $request)
    {
        $graftSizeData = GraftSize::with(['brand.manufacturer',])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'graft_size_data' => $graftSizeData,
        ]);
    }

    public function getAllOtherProducts(Request $request)
    {
        $otherProductData = OtherProduct::whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'other_product_data' => $otherProductData,
        ]);
    }

    public function getAllOtherProductsById(Request $request, $otherProductId)
    {
        $otherProduct = OtherProduct::where('other_product_id', $otherProductId)
            ->whereNull('deleted_at')
            ->first();

        if (!$otherProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Other product not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'other_product_data' => $otherProduct
        ]);
    }

    public function addNewOrder(Request $request)
    {
        $userId = $request->user()->id ?? null;

        $request->merge([
            'items' => json_decode($request->items, true),
            'products' => collect(json_decode($request->products, true) ?? [])
                ->filter(function ($product) {
                    return isset($product['other_product_id']) 
                        && $product['other_product_id'] !== null
                        && $product['other_product_id'] !== '';
                })
                ->values()
                ->toArray(),
        ]);

        $validated = $request->validate([
            'clinic_id'       => 'required|integer|exists:woundmed_clinics,clinic_id',
            'clinician_id'    => 'required|integer|exists:woundmed_users,id',
            'patient_id'      => 'required|integer|exists:woundmed_patient_info,patient_id',
            'ivr_id'          => 'required|integer|exists:woundmed_ivr,ivr_id',
            'manufacturer_id' => 'required|integer|exists:woundmed_manufacturers,manufacturer_id',
            'notes'           => 'nullable|string|max:1500',
            'order_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',

            'items'           => 'required_without:products|array|min:1',
            'items.*.brand_id'     => 'required|integer|exists:woundmed_brands,brand_id',
            'items.*.graft_id'     => 'required|integer|exists:woundmed_graft_sizes,graft_size_id',
            'items.*.ivr_id'       => 'required|integer|exists:woundmed_ivr,ivr_id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.asp'          => 'required|numeric|min:0',
            'items.*.product_type' => 'required|integer|in:0,1',
            'items.*.device_type'  => 'nullable|string|max:255',

            'products' => 'nullable|array',
            'products.*.other_product_id' => 'integer|exists:woundmed_other_products,other_product_id',
            'products.*.quantity' => 'integer|min:1',
            'products.*.price' => 'numeric|min:0',
            'products.*.product_type' => 'numeric|min:0',
        ]);

        # Validate Stocks graft items and product items
        $graftIds = collect($validated['items'])->pluck('graft_id')->unique()->all();
        $grafts = GraftSize::whereIn('graft_size_id', $graftIds)->get()->keyBy('graft_size_id');

        $productIds = collect($validated['products'] ?? [])->pluck('other_product_id')->filter()->unique()->all();
        $otherProducts = OtherProduct::whereIn('other_product_id', $productIds)->get()->keyBy('other_product_id');

        $errors = [];

        foreach ($validated['items'] as $idx => $item) {
            $graft = $grafts->get($item['graft_id']);
            if (!$graft) {
                $errors["items.$idx.graft_id"] = "Selected graft size not found.";
                continue;
            }
            if ($graft->stock < $item['quantity']) {
                $errors["items.$idx.quantity"] =
                    "Insufficient stock for {$graft->size} (available: {$graft->stock}, requested: {$item['quantity']})";
            }
        }

        foreach ($validated['products'] ?? [] as $idx => $prod) {
            if (empty($prod['other_product_id'])) continue;
            $product = $otherProducts->get($prod['other_product_id']);
            if (!$product) {
                $errors["products.$idx.other_product_id"] = "Selected product not found.";
                continue;
            }
            if ($product->stock < $prod['quantity']) {
                $errors["products.$idx.quantity"] =
                    "Insufficient stock for '{$product->product_name}' (available: {$product->stock}, requested: {$prod['quantity']})";
            }
        }

        if ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot place order – insufficient stock or invalid items',
                'errors'  => $errors,
            ], 422);

        }

        # process order in db:transaction
        $order = null;

        try {
            $order = DB::transaction(function () use ($validated, $request) {

                # 1. Atomic decrement with safety check
                foreach ($validated['items'] as $item) {
                    $affected = GraftSize::where('graft_size_id', $item['graft_id'])
                        ->where('stock', '>=', $item['quantity'])
                        ->decrement('stock', $item['quantity']);

                    if ($affected === 0) {
                        throw new \RuntimeException(
                            "Stock no longer available for graft ID {$item['graft_id']}"
                        );
                    }
                }

                foreach ($validated['products'] ?? [] as $prod) {
                    if (empty($prod['other_product_id'])) continue;

                    $affected = OtherProduct::where('other_product_id', $prod['other_product_id'])
                        ->where('stock', '>=', $prod['quantity'])
                        ->decrement('stock', $prod['quantity']);

                    if ($affected === 0) {
                        throw new \RuntimeException(
                            "Stock no longer available for product ID {$prod['other_product_id']}"
                        );
                    }
                }

                # 2. All reservations succeeded -> create order
                $orderCode   = 'ORD-' . strtoupper(uniqid());
                $trackingNum = 'TRK-' . strtoupper(Str::random(10));

                $filePath = null;
                if ($request->hasFile('order_file')) {
                    $filename = time() . '_' . $request->file('order_file')->getClientOriginalName();
                    $filePath = $request->file('order_file')->storeAs('order', $filename, 'private');
                }

                return Orders::create([
                    'order_code'         => $orderCode,
                    'clinic_id'          => $validated['clinic_id'],
                    'user_id'            => $validated['clinician_id'],
                    'patient_id'         => $validated['patient_id'],
                    'ivr_id'             => $validated['ivr_id'],
                    'manufacturer_id'    => $validated['manufacturer_id'],
                    'tracking_num'       => $trackingNum,
                    'notes'              => $validated['notes'] ?? null,
                    'items'              => $validated['items'],
                    'other_product_items'=> $validated['products'] ?? null,
                    'order_file'         => $filePath,
                    'order_status'       => 0,
                    'ordered_at'         => now(),
                ]);
            });

            $this->logAudit($request, 'create_new_order', "Order created successfully: {$order->order_code}", $order->order_id);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order could not be placed – stock was taken by another order. Please try again.',
                'error'   => $e->getMessage(),
            ], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data'  => $request->except('order_file'),
            ]);

            $this->logAudit($request, 'create_new_order', "Failed to create order", $userId);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again or contact support.',
            ], 500);
        }

        # start for email notification after order creation
        $token = Str::random(64);

        DB::table('magic_tokens')->insert([
            'manufacturer_id' => $validated['manufacturer_id'],
            'order_id'        => $order->order_id,
            'token'           => hash('sha256', $token),
            'expires_at'      => now()->addDays(60),
            'created_at'      => now(),
        ]);

        $email = $request->order_email;
        $orderUrl = config('app.frontend_url') . '/woundmed-order?' . http_build_query([
            'token'       => $token,
            'order_id'    => $order->order_id,
        ]);

        $productOnlyUrl = config('app.frontend_url') . '/woundmed-order?' . http_build_query([
            'token'        => $token,
            'order_id'     => $order->order_id,
            'productonly'  => 'true',
        ]);

        $totalAsp = 0;
        foreach ($validated['items'] as $item) {
            $totalAsp += $item['asp'] * $item['quantity'];
        }

        $graftItems = [];
        foreach ($validated['items'] ?? [] as $item) {
            $graftItems[] = [
                'brand_name' => OrderHelper::getBrandName($item['brand_id'] ?? null) ?? '—',
                'size_name'  => OrderHelper::getGraftSizeName($item['graft_id'] ?? null) ?? '—',
                'quantity'   => (int) ($item['quantity'] ?? 0),
                'asp'        => (float) ($item['asp'] ?? 0),
                'subtotal'   => (float) ($item['asp'] ?? 0) * (int) ($item['quantity'] ?? 0),
            ];
        }

        $otherProductItems = [];
        $otherSubtotal = 0;

        foreach ($validated['products'] ?? [] as $prod) {
            if (empty($prod['other_product_id'])) continue;

            $product = OtherProduct::find($prod['other_product_id']);
            if (!$product) continue;

            $price          = (float) ($product->price ?? $product->asp ?? 0);
            $qty            = (int) ($prod['quantity'] ?? 0);
            $productType    = (int) ($prod['product_type'] ?? 0);
            $sub            = $price * $qty;

            $productTypeLabel = match ($productType) {
                0 => 'Wound Supplies',
                1 => 'Devices',
                default => 'Unknown Product',
            };

            $otherProductItems[] = [
                'product_name' => $product->product_name ?? 'Unknown Product',
                'quantity'     => $qty,
                'product_type' => $productTypeLabel,
                'price'        => $price,
                'subtotal'     => $sub,
            ];

            $otherSubtotal += $sub;
        }

        $graftSubtotal = array_sum(array_column($graftItems, 'subtotal'));
        $totalAsp = $graftSubtotal + $otherSubtotal;

        # Pass prepared data to email template
        $emailBody = OrderNotificationEmail::getTemplate([
            'order_code'          => $order['order_code'],
            'tracking_number'     => $order['tracking_num'],
            'clinic_name'         => OrderHelper::getClinicName($validated['clinic_id']),
            'clinician_name'      => OrderHelper::getClinicianName($validated['clinician_id']),
            'manufacturer_name'   => OrderHelper::getManufacturerName($validated['manufacturer_id']),
            'patient_name'        => OrderHelper::getPatientName($validated['patient_id']),

            'items'               => $graftItems,
            'graft_subtotal'      => $graftSubtotal,

            'other_product_items' => $otherProductItems,
            'other_subtotal'      => $otherSubtotal,

            'total_asp'           => $totalAsp,
            'order_link'          => $orderUrl,
        ]);

        # prepare other product data to email template
        $otherProductEmailBody = OtherProductOrderNotificationEmail::getTemplate([
            'order_code'          => $order['order_code'],
            'tracking_number'     => $order['tracking_num'],
            'clinic_name'         => OrderHelper::getClinicName($validated['clinic_id']),
            'clinician_name'      => OrderHelper::getClinicianName($validated['clinician_id']),
            'manufacturer_name'   => OrderHelper::getManufacturerName($validated['manufacturer_id']),
            'patient_name'        => OrderHelper::getPatientName($validated['patient_id']),

            'items'               => $graftItems,
            'graft_subtotal'      => $graftSubtotal,

            'other_product_items' => $otherProductItems,
            'other_subtotal'      => $otherSubtotal,

            'total_asp'           => $totalAsp,
            'order_link'          => $productOnlyUrl,
        ]);

        $emailService = new EmailService();

        $params = [
            'to'        => $email,
            'from'      => 'noreply@woundmed.com',
            'from_name' => 'WOUNDMED INC. Order Notification',
            'subject'   => "New Order Created ({$order['order_code']})",
            'body'      => $emailBody,
        ];

        $OtherProductParams = [
            'to'        => $email,
            // 'to'        => 'office@woundmedinc.com', // live
            // 'cc'        => ['woundmedinc@gmail.com', 'info@woundmedinc.com'], // live
            'cc'        => ['prospteam@gmail.com', 'joshuapalay.web2@gmail.com'], // test
            'from'      => 'noreply@woundmed.com',
            'from_name' => 'WOUNDMED INC. Other Product Order Notification',
            'subject'   => "Other Product Order Details ({$order['order_code']})",
            'body'      => $otherProductEmailBody,
        ];

        $emailResults = [];

        $emailResults['main'] = $emailService->send_email(
            $params,
            'Order created',
            'Order created'
        );

        if (!empty($otherProductItems)) {
            $emailResults['other'] = $emailService->send_email(
                $OtherProductParams,
                'Other Product Order created',
                'Other Product Order created'
            );
        }

        # error logging
        foreach ($emailResults as $type => $result) {
            if (!$result) {
                \Log::error('Email failed', [
                    'type'      => $type,
                    'order_id'  => $order->order_id,
                    'orderCode' => $order->orderCode,
                    'to'        => $email,
                ]);
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Order created successfully!',
            'order_id' => $order->order_id,
        ], 201);
    }

    public function updateOrder(Request $request, $orderId)
    {
        // dd($request->all());
        $request->merge([
            'items' => json_decode($request->items, true),
            'products' => collect(json_decode($request->products, true) ?? [])
                ->filter(function ($product) {
                    return isset($product['other_product_id']) 
                        && $product['other_product_id'] !== null
                        && $product['other_product_id'] !== '';
                })
                ->values()
                ->toArray(),
        ]);

        $validated = $request->validate([
            'clinic_id' => 'required|int|max:255',
            'clinician_id' => 'required|int|max:255',
            'patient_id' => 'required|int|max:255',
            'notes' => 'nullable|string',
            'order_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            
            'items' => 'required|array|min:1',
            'items.*.brand_id' => 'required|integer|exists:woundmed_brands,brand_id',
            'items.*.graft_id' => 'required|integer|exists:woundmed_graft_sizes,graft_size_id',
            'items.*.ivr_id' => 'required|integer|exists:woundmed_ivr,ivr_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.asp' => 'required|numeric|min:0',
            'items.*.product_type' => 'required|integer|in:0,1',
            'items.*.device_type' => 'nullable|string|max:255',

            'products' => 'nullable|array',
            'products.*.other_product_id' => 'integer|exists:woundmed_other_products,other_product_id',
            'products.*.quantity' => 'integer|min:1',
            'products.*.price' => 'numeric|min:0',
            'products.*.product_type' => 'numeric|min:0',
        ]);

        $order = Orders::findOrFail($orderId);

        # Prevent update if order is already processed
        if ($order->order_status >= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update order that has been acknowledged, shipped or delivered.',
            ], 422);
        }

        # Load current (old) items for delta calculation
        $oldItems = $order->items ?? [];
        $oldProducts = $order->other_product_items ?? [];

        # early/initial stock check
        $graftIds = collect($validated['items'])->pluck('graft_id')->unique()->all();
        $grafts = GraftSize::whereIn('graft_size_id', $graftIds)->get()->keyBy('graft_size_id');

        $productIds = collect($validated['products'])->pluck('other_product_id')->filter()->unique()->all();
        $otherProducts = OtherProduct::whereIn('other_product_id', $productIds)->get()->keyBy('other_product_id');

        $errors = [];

        # requested quantities checker (order items)
        foreach ($validated['items'] as $idx => $item) {
            $graft = $grafts->get($item['graft_id']);
            if (!$graft) {
                $errors["items.$idx.graft_id"] = "Graft size not found.";
                continue;
            }

            # Find old quantity for this exact graft_id
            $oldQty = 0;
            foreach ($oldItems as $old) {
                if (($old['graft_id'] ?? null) == $item['graft_id']) {
                    $oldQty = (int)($old['quantity'] ?? 0);
                    break;
                }
            }

            $netRequired = (int)$item['quantity'] - $oldQty;

            if ($netRequired > 0 && $graft->stock < $netRequired) {
                $errors["items.$idx.quantity"] = 
                    "Insufficient stock for {$graft->size} (available: {$graft->stock}, need additional: {$netRequired})";
            }
        }

        # requested quantities checker (other product)
        foreach ($validated['products'] ?? [] as $idx => $prod) {
            if (empty($prod['other_product_id'])) continue;

            $product = $otherProducts->get($prod['other_product_id']);
            if (!$product) {
                $errors["products.$idx.other_product_id"] = "Product not found.";
                continue;
            }

            $oldQty = 0;
            foreach ($oldProducts as $oldP) {
                if (($oldP['other_product_id'] ?? null) == $prod['other_product_id']) {
                    $oldQty = (int)($oldP['quantity'] ?? 0);
                    break;
                }
            }

            $netRequired = (int)$prod['quantity'] - $oldQty;

            if ($netRequired > 0 && $product->stock < $netRequired) {
                $errors["products.$idx.quantity"] = 
                    "Insufficient stock for '{$product->product_name}' (available: {$product->stock}, need additional: {$netRequired})";
            }
        }

        if ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update order – insufficient stock or invalid items',
                'errors'  => $errors,
            ], 422);
        }

        try {
            DB::transaction(function () use ($order, $validated, $oldItems, $oldProducts, $request) {

                # A. Restore old stock (increase)
                foreach ($oldItems as $oldItem) {
                    GraftSize::where('graft_size_id', $oldItem['graft_id'])
                        ->increment('stock', $oldItem['quantity'] ?? 0);
                }

                foreach ($oldProducts as $oldProd) {
                    if (empty($oldProd['other_product_id'])) continue;
                    OtherProduct::where('other_product_id', $oldProd['other_product_id'])
                        ->increment('stock', $oldProd['quantity'] ?? 0);
                }

                # B. Reserve new quantities (decrease)
                foreach ($validated['items'] as $item) {
                    $affected = GraftSize::where('graft_size_id', $item['graft_id'])
                        ->where('stock', '>=', $item['quantity'])
                        ->decrement('stock', $item['quantity']);

                    if ($affected === 0) {
                        throw new \RuntimeException("Stock no longer available for graft ID {$item['graft_id']}");
                    }
                }

                foreach ($validated['products'] ?? [] as $prod) {
                    if (empty($prod['other_product_id'])) continue;

                    $affected = OtherProduct::where('other_product_id', $prod['other_product_id'])
                        ->where('stock', '>=', $prod['quantity'])
                        ->decrement('stock', $prod['quantity']);

                    if ($affected === 0) {
                        throw new \RuntimeException("Stock no longer available for product ID {$prod['other_product_id']}");
                    }
                }

                # C. Update order record
                $orderCode = 'ORD-' . strtoupper(uniqid());
                $trackingNum = 'TRK-' . strtoupper(Str::random(10));

                $filePath = $order->order_file;
                if ($request->hasFile('order_file')) {
                    
                    if ($filePath) Storage::disk('private')->delete($filePath);

                    $filename = time() . '_' . $request->file('order_file')->getClientOriginalName();
                    $filePath = $request->file('order_file')->storeAs('order', $filename, 'private');
                }

                $order->update([
                    'order_code'         => $orderCode,
                    'clinic_id'          => $validated['clinic_id'],
                    'user_id'            => $validated['clinician_id'],
                    'patient_id'         => $validated['patient_id'],
                    'tracking_num'       => $trackingNum,
                    'notes'              => $validated['notes'] ?? $order->notes,
                    'items'              => $validated['items'],
                    'other_product_items'=> $validated['products'] ?? [],
                    'order_file'         => $filePath,
                    'order_status'       => 0,
                    'ordered_at'         => now(),
                ]);
            });

            $this->logAudit($request, 'update_order_details', "Order updated successfully: {$order->order_code}", $order->order_id);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed – stock was taken by another order during processing. Please try again.',
                'error'   => $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            \Log::error('Order update failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            $this->logAudit($request, 'update_order_details', "Failed to update order details", $userId);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order. Please try again or contact support.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'order_id' => $order->order_id,
        ]);
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $validated = $request->validate([
            'order_status' => 'required|integer|min:0|max:4'
        ]);

        try {
            $order = Orders::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            $order->update([
                'order_status' => $validated['order_status']
            ]);

            $this->logAudit($request, 'update_order_status', "Order status updated successfully: {$validated['order_status']}, {$order->order_code}", $order->order_id);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
            ]);
        } catch (\Exception $e) {
            $this->logAudit($request, 'update_order_status', "Failed to update order status: {$order->order_code}", $order->order_id);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteOrder(Request $request, $orderId)
    {
        try {
            $order = Orders::findOrFail($orderId);
            $order->delete();

            $this->logAudit($request, 'delete_order', "Order details deleted successfully: {$order->order_code}", $order->order_id);

            return response()->json([
                'message' => 'Order details deleted successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logAudit($request, 'delete_order', "Operation failed: {$order->order_code}", $order->order_id);

            return response()->json([
                'success' => false,
                'message' => 'Operation failed', 
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            $this->logAudit($request, 'delete_order', "Failed to delete order details: {$order->order_code}", $order->order_id);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order details: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function validateMagicLink(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

                $tokenPlain = $request->input('token');
                $orderId    = $request->input('order_id');

                $token = DB::table('magic_tokens')
                    ->where('order_id', $orderId)
                    ->where('token', hash('sha256', $tokenPlain))
                    ->first();

                if (!$token) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid magic link.'
                    ], 400);
                }

                # Check if already used
                if (!is_null($token->used_at)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This access link has already been used.'
                    ], 400);
                }

                if ($token->expires_at < now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This magic link has expired.'
                    ], 400);
                }

                # VALID
                $order = Orders::with(['clinic', 'clinician', 'patient', 'manufacturer', 'brand.manufacturer', 'graft', 'ivr'])
                    ->where('order_id', $orderId)
                    ->first();

                $orderEmail = $order->manufacturer->order_email ?? null;

                $this->logAudit($request, 'magic_link_access', "Magic link accessed successfully for Order ID {$orderId}", $orderId, 0, $orderEmail);

                return response()->json([
                    'success' => true,
                    'order'   => $order
                ]);
            });

        } catch (\Throwable $e) {

            \Log::critical('Magic link validation failed: ' . $e->getMessage());

            $this->logAudit($request, 'magic_link_access', "Magic link access faild for Order ID {$orderId}", $orderId, 1, $orderEmail);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    public function updateMagicOrderStatus(Request $request, $orderId)
    {
        $validated = $request->validate([
            'order_status'  => 'required|integer|min:0|max:4',
            'order_number'  => 'required_if:order_status,1|string|max:255',
            'tracking_code' => 'required_if:order_status,2|string|max:255',
            'tracking_link' => 'required_if:order_status,2|string|max:255',
            'token'         => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $validated, $orderId) {

            $order = Orders::findOrFail($orderId);

            $tokenRecord = DB::table('magic_tokens')
                ->where('order_id', $orderId)
                ->where('token', hash('sha256', $validated['token']))
                ->first();

            if (!$tokenRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid magic link.'
                ], 400);
            }

            if (!is_null($tokenRecord->used_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This access link has already been used.'
                ], 400);
            }
        
            $dataToUpdate = [
                'order_status' => $validated['order_status'],
            ];

            if ($request->filled('order_number')) {
                $dataToUpdate['order_number'] = $request->order_number;
            }

            if ($request->filled('tracking_code')) {
                $dataToUpdate['tracking_code'] = $request->tracking_code;
            }

            if ($request->filled('tracking_link')) {
                $dataToUpdate['tracking_link'] = $request->tracking_link;
            }

            $order->update($dataToUpdate);

            # Mark token used if final status
            if ((int) $validated['order_status'] === 3) {
                DB::table('magic_tokens')
                    ->where('order_id', $orderId)
                    ->update(['used_at' => now()]);
            }

            # Update order status
            $order = Orders::findOrFail($orderId);
            $order->update([
                'order_status' => $validated['order_status']
            ]);

            $orderEmail = $order->manufacturer->order_email ?? null;

            $this->logAudit($request, 'update_magic_order_status', "Order status updated successfully Order ID {$orderId}, new status: {$validated['order_status']}", $orderId, 0, $orderEmail ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
            ]);
        });
    }

    public function followUpOrder(Request $request, $orderId)
    {
        $user = Auth::user();

        try {
            if (!$user || $user->user_role !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can send follow-up emails.'
                ], 403);
            }

            $order = Orders::with(['clinic', 'clinician', 'patient', 'manufacturer', 'brand.manufacturer', 'graft', 'ivr'])
            ->findOrFail($orderId);


            if ((int) $order->order_status !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Follow-up is only allowed for submitted orders.'
                ], 400);
            }

            if ($order->followup_last_sent_at) {
                $last = Carbon::parse($order->followup_last_sent_at);
                if ($last->diffInHours(now()) < 24) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Follow-up email already sent recently. Try again later.'
                    ], 429);
                }
            }

            $manufacturer = $order->manufacturer;
            $emails = [];

            if ($manufacturer) {
                if (is_array($manufacturer->order_email)) {
                    $emails = $manufacturer->order_email;
                } else {
                    $emails = array_map('trim', explode(',', $manufacturer->order_email));
                }
            }

            if (empty($emails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This manufacturer has no email configured.'
                ], 400);
            }

            $token = Str::random(64);

            DB::table('magic_tokens')->insert([
                'manufacturer_id' => optional($order->manufacturer)->manufacturer_id,
                'order_id'        => $order->order_id,
                'token'           => hash('sha256', $token),
                'expires_at'      => now()->addDays(60),
                'created_at'      => now(),
            ]);

            $orderUrl = config('app.frontend_url')
                    . '/woundmed-order?token=' . $token
                    . '&order_id=' . $order->order_id;

            $items = array_map(function ($item) {
                return [
                    'brand_name' => OrderHelper::getBrandName($item['brand_id']),
                    'size_name'  => OrderHelper::getGraftSizeName($item['graft_id']),
                    'quantity'   => $item['quantity'] ?? 1,
                    'subtotal'   => ($item['asp'] ?? 0) * ($item['quantity'] ?? 1),
                ];
            }, $order->items);

            // flag - continue here

            $totalAsp = array_reduce($items, fn($sum, $i) => $sum + $i['subtotal'], 0);

            $emailBody = FollowupOrderNotificationEmail::getTemplate([
                'order_code'        => $order->order_code,
                'tracking_number'   => $order->tracking_num,
                'clinic_name'       => optional($order->clinic)->clinic_name,
                'clinician_name' => trim(
                    ($order->clinician->first_name ?? '') . ' ' . 
                    ($order->clinician->last_name ?? '')
                ),
                'manufacturer_name' => optional($order->manufacturer)->manufacturer_name,
                'patient_name'      => optional($order->patient)->patient_name,
                'items'             => $items,
                'total_asp'         => $totalAsp,
                'order_link'        => $orderUrl
            ]);

            $emailService = new EmailService();

            foreach ($emails as $to) {
                $emailService->send_email([
                    'to'        => $to,
                    'from'      => 'noreply@woundmed.com',
                    'from_name' => 'WOUNDMED INC. Follow up Notification',
                    'subject'   => "Follow-Up Required ({$order->order_code})",
                    'body'      => $emailBody,
                ], 'Follow-up email sent', 'Follow-up email sent');
            }

            $order->update([
                'followup_last_sent_at' => now()
            ]);

            $this->logAudit($request, 'follow_up_order', "Follow-up email sent successfully: {$order->order_code}", $order->order_id);

            return response()->json([
                'success' => true,
                'message' => 'Follow-up email sent successfully!',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send follow up notification: ' . $th->getMessage(),
            ], 500);
        }
    }

    // Manufacturer User
    public function getAllOrdersByManufacturers(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 10);

        if (!$user || $user->user_role !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only manufacturers can access this.',
            ], 403);
        }

        if (!$user->manufacturer_id) {
            return response()->json([
                'success' => false,
                'message' => 'This user is not assigned to any manufacturer.',
            ], 403);
        }

        try {
            $orders = Orders::with(['clinic', 'clinician', 'patient', 'brand.manufacturer', 'graft', 'ivr'])
                ->where('manufacturer_id', $user->manufacturer_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'order_data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $th->getMessage(),
            ], 500);
        }
    }

    // Clinic User
    public function getAllOrdersByClinics(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->query('per_page', 10);

        if (!$user || !in_array($user->user_role, [2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only clinics can access this.',
            ], 403);
        }

        if (!$user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'This user is not assigned to any manufacturer.',
            ], 403);
        }

        try {
            $orders = Orders::with(['clinic', 'clinician', 'patient', 'brand.manufacturer', 'graft', 'ivr.manufacturer'])
                ->where('clinic_id', $user->clinic_id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'order_data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total(),
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function userWithClinic(Request $request)
    {
        $user = Auth::user()->load('clinic');

        return response()->json([
            'success' => true,
            'user_data' => $user
        ]);
    }

    public function addNewOrderByClinic(Request $request)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->user_role, [2, 3])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only clinic users can place orders this way.',
            ], 403);
        }

        $request->merge([
            'items'    => json_decode($request->items ?? '[]', true) ?? [],
            'products' => collect(json_decode($request->products ?? '[]', true) ?? [])
                ->filter(fn($p) => !empty($p['other_product_id']) && is_numeric($p['other_product_id']))
                ->values()
                ->toArray(),
        ]);

        $validated = $request->validate([
            'clinic_id'       => 'required|integer|exists:woundmed_clinics,clinic_id',
            'clinician_id'    => 'required|integer|exists:woundmed_users,id',
            'patient_id'      => 'required|integer|exists:woundmed_patient_info,patient_id',
            'ivr_id'          => 'required|integer|exists:woundmed_ivr,ivr_id',
            'manufacturer_id' => 'required|integer|exists:woundmed_manufacturers,manufacturer_id',
            'notes'           => 'nullable|string|max:1500',
            'order_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',

            # Graft items
            'items'           => 'required_without:products|array|min:1',
            'items.*.brand_id'     => 'required|integer|exists:woundmed_brands,brand_id',
            'items.*.graft_id'     => 'required|integer|exists:woundmed_graft_sizes,graft_size_id',
            'items.*.ivr_id'       => 'required|integer|exists:woundmed_ivr,ivr_id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.asp'          => 'required|numeric|min:0',
            'items.*.product_type' => 'required|integer|in:0,1',
            'items.*.device_type'  => 'nullable|string|max:255',

            # Other products
            'products'                    => 'required_without:items|array',
            'products.*.other_product_id' => 'required|integer|exists:woundmed_other_products,other_product_id',
            'products.*.quantity'         => 'required|integer|min:1',
            'products.*.price'            => 'required|numeric|min:0',
            'products.*.product_type'     => 'nullable|numeric|min:0',
        ]);

        # Stock validation
        $graftIds      = collect($validated['items'] ?? [])->pluck('graft_id')->unique()->all();
        $grafts        = GraftSize::whereIn('graft_size_id', $graftIds)->get()->keyBy('graft_size_id');

        $productIds    = collect($validated['products'] ?? [])->pluck('other_product_id')->unique()->all();
        $otherProducts = OtherProduct::whereIn('other_product_id', $productIds)->get()->keyBy('other_product_id');

        $errors = [];

        foreach ($validated['items'] ?? [] as $idx => $item) {
            $graft = $grafts->get($item['graft_id']);
            if (!$graft) {
                $errors["items.$idx.graft_id"] = "Graft size not found.";
                continue;
            }
            if ($graft->stock < $item['quantity']) {
                $errors["items.$idx.quantity"] =
                    "Insufficient stock for {$graft->size} (avail: {$graft->stock}, req: {$item['quantity']})";
            }
        }

        foreach ($validated['products'] ?? [] as $idx => $prod) {
            $product = $otherProducts->get($prod['other_product_id']);
            if (!$product) {
                $errors["products.$idx.other_product_id"] = "Product not found.";
                continue;
            }
            if ($product->stock < $prod['quantity']) {
                $errors["products.$idx.quantity"] =
                    "Insufficient stock for '{$product->product_name}' (avail: {$product->stock}, req: {$prod['quantity']})";
            }
        }

        if ($errors) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot place order – insufficient stock or invalid items',
                'errors'  => $errors,
            ], 422);
        }

        # Transaction: reserve stock + create order + handle file
        $order = null;

        try {
            $order = DB::transaction(function () use ($validated, $request, $user) {

                # Reserve grafts
                foreach ($validated['items'] ?? [] as $item) {
                    $affected = GraftSize::where('graft_size_id', $item['graft_id'])
                        ->where('stock', '>=', $item['quantity'])
                        ->decrement('stock', $item['quantity']);
                    if ($affected === 0) {
                        throw new \RuntimeException("Stock gone for graft {$item['graft_id']}");
                    }
                }

                # Reserve other products
                foreach ($validated['products'] ?? [] as $prod) {
                    $affected = OtherProduct::where('other_product_id', $prod['other_product_id'])
                        ->where('stock', '>=', $prod['quantity'])
                        ->decrement('stock', $prod['quantity']);
                    if ($affected === 0) {
                        throw new \RuntimeException("Stock gone for product {$prod['other_product_id']}");
                    }
                }

                # Handle file upload
                $filePath = null;
                if ($request->hasFile('order_file')) {
                    $filename = time() . '_' . $request->file('order_file')->getClientOriginalName();
                    $filePath = $request->file('order_file')->storeAs('order', $filename, 'private');
                }

                # Create order
                $orderCode   = 'ORD-' . strtoupper(uniqid());
                $trackingNum = 'TRK-' . strtoupper(Str::random(10));

                return Orders::create([
                    'order_code'         => $orderCode,
                    'clinic_id'          => $validated['clinic_id'],
                    'user_id'            => $validated['clinician_id'],
                    'patient_id'         => $validated['patient_id'],
                    'ivr_id'             => $validated['ivr_id'],
                    'manufacturer_id'    => $validated['manufacturer_id'],
                    'tracking_num'       => $trackingNum,
                    'notes'              => $validated['notes'] ?? null,
                    'items'              => $validated['items'] ?? [],
                    'other_product_items'=> $validated['products'] ?? [],
                    'order_file'         => $filePath,
                    'order_status'       => 0,
                    'ordered_at'         => now(),
                    // 'ordering_user_id' => $user->id,
                ]);
            });

            $this->logAudit(
                $request,
                'create_clinic_order',
                "Clinic order created: {$order->order_code} "
                . "(grafts: " . count($validated['items'] ?? []) . ", "
                . "products: " . count($validated['products'] ?? []) . ")",
                $order->order_id
            );

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order failed – stock taken by another request. Please retry.',
                'error'   => $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            \Log::error('Clinic order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logAudit($request, 'create_clinic_order', 'Failed to create clinic order', $user->id ?? null);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again or contact support.',
            ], 500);
        }

        # Magic token + Email preparation
        $token = Str::random(64);

        DB::table('magic_tokens')->insert([
            'manufacturer_id' => $validated['manufacturer_id'],
            'order_id'        => $order->order_id,
            'token'           => hash('sha256', $token),
            'expires_at'      => now()->addDays(60),
            'created_at'      => now(),
        ]);

        $orderUrl = config('app.frontend_url') . '/woundmed-order?' . http_build_query([
            'token'    => $token,
            'order_id' => $order->order_id,
        ]);

        # Prepare data for email
        $graftItems    = [];
        $graftSubtotal = 0;
        foreach ($validated['items'] ?? [] as $item) {
            $sub = (float)($item['asp'] ?? 0) * (int)($item['quantity'] ?? 0);
            $graftSubtotal += $sub;
            $graftItems[] = [
                'brand_name' => OrderHelper::getBrandName($item['brand_id'] ?? null) ?? '—',
                'size_name'  => OrderHelper::getGraftSizeName($item['graft_id'] ?? null) ?? '—',
                'quantity'   => (int)($item['quantity'] ?? 0),
                'asp'        => (float)($item['asp'] ?? 0),
                'subtotal'   => $sub,
            ];
        }

        $otherProductItems = [];
        $otherSubtotal     = 0;
        foreach ($validated['products'] ?? [] as $prod) {
            $product = OtherProduct::find($prod['other_product_id']);
            if (!$product) continue;

            $price = (float)($prod['price'] ?? $product->price ?? $product->asp ?? 0);
            $qty   = (int)($prod['quantity'] ?? 0);
            $sub   = $price * $qty;
            $otherSubtotal += $sub;

            $typeLabel = match ((int)($prod['product_type'] ?? 0)) {
                0 => 'Wound Supplies',
                1 => 'Devices',
                default => 'Other',
            };

            $otherProductItems[] = [
                'product_name' => $product->product_name ?? 'Unknown',
                'quantity'     => $qty,
                'product_type' => $typeLabel,
                'price'        => $price,
                'subtotal'     => $sub,
            ];
        }

        $totalAsp = $graftSubtotal + $otherSubtotal;

        $emailBody = OrderNotificationEmail::getTemplate([
            'order_code'          => $order->order_code,
            'tracking_number'     => $order->tracking_num,
            'clinic_name'         => OrderHelper::getClinicName($validated['clinic_id']),
            'clinician_name'      => OrderHelper::getClinicianName($validated['clinician_id']),
            'manufacturer_name'   => OrderHelper::getManufacturerName($validated['manufacturer_id']),
            'patient_name'        => OrderHelper::getPatientName($validated['patient_id']),
            'items'               => $graftItems,
            'graft_subtotal'      => $graftSubtotal,
            'other_product_items' => $otherProductItems,
            'other_subtotal'      => $otherSubtotal,
            'total_asp'           => $totalAsp,
            'order_link'          => $orderUrl,
        ]);

        echo '<pre>';
        print_r($emailBody);
        echo '<br>';
        echo '</pre>';
        exit;
        
        # prepare other product data to email template
        $otherProductEmailBody = OtherProductOrderNotificationEmail::getTemplate([
            'order_code'          => $order['order_code'],
            'tracking_number'     => $order['tracking_num'],
            'clinic_name'         => OrderHelper::getClinicName($validated['clinic_id']),
            'clinician_name'      => OrderHelper::getClinicianName($validated['clinician_id']),
            'manufacturer_name'   => OrderHelper::getManufacturerName($validated['manufacturer_id']),
            'patient_name'        => OrderHelper::getPatientName($validated['patient_id']),

            'items'               => $graftItems,
            'graft_subtotal'      => $graftSubtotal,

            'other_product_items' => $otherProductItems,
            'other_subtotal'      => $otherSubtotal,

            'total_asp'           => $totalAsp,
            'order_link'          => $productOnlyUrl,
        ]);

        # Send email
        $emailService = new EmailService();
        $toEmail      = $request->order_email ?? $order->manufacturer?->order_email ?? null;

        if ($toEmail) {
            $mainParams = [
                'to'        => $toEmail,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WOUNDMED INC. Orders',
                'subject'   => "New Clinic Order: {$order->order_code}",
                'body'      => $emailBody,
            ];

            $emailResults['main'] = $emailService->send_email(
                $mainParams,
                'Clinic order created (main)',
                'Clinic order notification'
            );

            # separate email for other products (with different recipients)
            if (!empty($otherProductItems)) {
                $otherParams = [
                    'to'        => $toEmail,
                    // 'to'     => 'office@woundmedinc.com',               // ← live
                    // 'cc'     => ['woundmedinc@gmail.com', 'info@woundmedinc.com'], // ← live
                    'cc'        => ['prospteam@gmail.com', 'joshuapalay.web2@gmail.com'], // ← test / dev
                    'from'      => 'noreply@woundmed.com',
                    'from_name' => 'WOUNDMED INC. Orders',
                    'subject'   => "Other Products – Clinic Order: {$order->order_code}",
                    'body'      => $otherProductEmailBody,
                ];

                $emailResults['other'] = $emailService->send_email(
                    $otherParams,
                    'Clinic other-products order',
                    'Other products notification'
                );
            }

            # error logging
            foreach ($emailResults as $type => $result) {
                if (!$result) {
                    \Log::error('Email failed', [
                        'type'      => $type,
                        'order_id'  => $order->order_id,
                        'orderCode' => $order->orderCode,
                        'to'        => $email,
                    ]);
                }
            }
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Order created successfully!',
            'order_id' => $order->order_id,
        ], 201);
    }

    public function updateOrderByClinic(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'clinic_id' => 'required|int|max:255',
                'clinician_id' => 'required|int|max:255',
                'patient_id' => 'required|int|max:255',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.brand_id' => 'required|integer|exists:woundmed_brands,brand_id',
                'items.*.graft_id' => 'required|integer|exists:woundmed_graft_sizes,graft_size_id',
                'items.*.ivr_id' => 'required|integer|exists:woundmed_ivr,ivr_id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.asp' => 'required|numeric|min:0',
                'items.*.product_type' => 'required|integer|in:0,1',
                'items.*.device_type' => 'nullable|string|max:255',
            ]);

            $orderCode = 'ORD-' . strtoupper(uniqid(10));
            $trackingNum = 'TRK-' . strtoupper(Str::random(10));

            $order = Orders::findOrFail($orderId);

            $order->update([
                'order_code' => $orderCode,
                'clinic_id' => $validated['clinic_id'],
                'user_id' => $validated['clinician_id'],
                'patient_id' => $validated['patient_id'],
                'tracking_num' => $trackingNum,
                'notes' => $validated['notes'],
                'items' => $validated['items'],
                'order_status' => 0,
                'ordered_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order details updated successfully!',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function viewOrderFile(string $filename)
    {
// <<<<<<< Updated upstream
//         $filename = urldecode($filename);

//         # prevent path traversal
//         if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
//             abort(403, 'Invalid filename');
//         }

//         $allowedPrefixes = ['order/', 'ivr/'];

//         $disk = Storage::disk('private');
//         $foundPath = null;

//         foreach ($allowedPrefixes as $prefix) {
//             $fullPath = $prefix . $filename;

//             if ($disk->exists($fullPath)) {
//                 $foundPath = $fullPath;
//                 break;
// =======
        $decodedFilename = urldecode($filename);

        // Handle encrypted files (.enc) — decrypt on-the-fly
        if (str_ends_with($decodedFilename, '.enc')) {
            if (Storage::disk('local')->exists($decodedFilename)) {
                $fileService = app(\App\Services\FileEncryptionService::class);
                $fileData    = $fileService->decryptAndRetrieve($decodedFilename, 'local');
                return response($fileData['contents'], 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="order_file.pdf"',
                    'Content-Length'      => strlen($fileData['contents']),
                ]);
            }
            return abort(404, 'File not found.');
        }

        // Legacy: plain file lookup
        $path = "ivr/" . $decodedFilename;

        if (!Storage::disk('private')->exists($path)) {
            $path = "order/" . $decodedFilename;

            if (!Storage::disk('private')->exists($path)) {
                $path = $decodedFilename;

                // $this->logAudit(
                //     request(),
                //     'view_order_file', 
                //     "User viewed order file: {$filename} (Order #{$filename})",
                //     $order->order_id,
                //     null,
                //     null
                // );

                if (!Storage::disk('private')->exists($path)) {
                    return abort(404, 'File not found.');
                }
// >>>>>>> Stashed changes
            }
        }

        // if (!$foundPath) {
        //     abort(404, 'File not found');
        // }

        // $order = Orders::where('order_file', $foundPath)->first();
        // if (!$order) {
        //     abort(404);
        // }

        // if (!Auth::check() || !in_array(Auth::id(), [$order->user_id, $order->manufacturer->user_id ?? null])) {
        //     abort(403, 'Unauthorized');
        // }

        // $mimeType = $disk->mimeType($foundPath);
        // $size     = $disk->size($foundPath);
        // $lastMod  = $disk->lastModified($foundPath);

        // return response()
        //     ->file($disk->path($foundPath), [
        //         'Content-Type'        => $mimeType,
        //         'Content-Length'      => $size,
        //         'Content-Disposition' => 'inline; filename="' . $filename . '"',
        //         'Last-Modified'       => gmdate('D, d M Y H:i:s', $lastMod) . ' GMT',
        //         'Cache-Control'       => 'private, max-age=3600', // 1 hour – adjust as needed
        //     ]);
    }

    public function downloadOrderFile(Request $request, $id)
    {
        try {
            $manufacturer = Manufacturer::findOrFail($id);

            if (!$manufacturer->order_file || !Storage::disk('private')->exists($manufacturer->order_file)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $path = $manufacturer->order_file;

            // Handle encrypted files (.enc) — decrypt on-the-fly
            if (str_ends_with($path, '.enc')) {
                if (!Storage::disk('local')->exists($path)) {
                    return response()->json(['error' => 'File not found'], 404);
                }
                $fileService = app(\App\Services\FileEncryptionService::class);
                $fileData    = $fileService->decryptAndRetrieve($path, 'local');
                return response($fileData['contents'], 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="order_form.pdf"',
                    'Content-Length'      => strlen($fileData['contents']),
                ]);
            }

            $this->logAudit($request, 'download_order_file', "Downloaded Order file for manufacturer: {$manufacturer->manufacturer_name}", $manufacturer->manufacturer_id, 0, $request->user()?->id);

            // Legacy: plain file
            if (!Storage::disk('private')->exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            $filename = basename($path);
            return Storage::disk('private')->download($path, $filename);
        } catch (\Throwable $e) {
            \Log::critical('Downloading Order file failed: ' . $e->getMessage());

            $this->logAudit($request, 'download_order_file', "Failed to download Order file for manufacturer: {$manufacturer->manufacturer_name}", $manufacturer->manufacturer_id, 1, $request->user()?->id);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}

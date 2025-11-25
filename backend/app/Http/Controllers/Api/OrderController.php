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
use App\Models\Orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\EmailService;
use App\Template\OrderNotificationEmail;
use App\Template\FollowupOrderNotificationEmail;
use App\Helpers\OrderHelper;

class OrderController extends Controller
{
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

    public function addNewOrder(Request $request)
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
                'ivr_id' => 'required|integer|exists:woundmed_ivr,ivr_id',
                'manufacturer_id' => 'required|integer|exists:woundmed_manufacturers,manufacturer_id',
            ]);

            $orderCode = 'ORD-' . strtoupper(uniqid());
            $trackingNum = 'TRK-' . strtoupper(Str::random(10));

            $order = Orders::create([
                'order_code' => $orderCode,
                'clinic_id' => $validated['clinic_id'],
                'user_id' => $validated['clinician_id'],
                'patient_id' => $validated['patient_id'],
                'ivr_id' => $validated['ivr_id'],
                'manufacturer_id' => $validated['manufacturer_id'],
                'tracking_num' => $trackingNum,
                'notes' => $validated['notes'],
                'items' => $validated['items'],
                'order_status' => 0,
                'ordered_at' => now(),
            ]);

            # update stock by subtracting the current graft stock from quantity for each graftsizes
            DB::transaction(function() use ($validated) {
                foreach ($validated['items'] as $item) {

                    $graft = GraftSize::lockForUpdate()->find($item['graft_id']);

                    if ($graft->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for " . $graft->size);
                    }

                    $graft->stock -= $item['quantity'];
                    $graft->save();
                }
            });

            $token = Str::random(64);

            DB::table('magic_tokens')->insert([
                'manufacturer_id' => $validated['manufacturer_id'],
                'order_id'        => $order->order_id,
                'token'           => hash('sha256', $token),
                'expires_at'      => now()->addDays(60),
                'created_at'      => now(),
            ]);

            $email = $request->primary_email;
            $orderUrl = config('app.frontend_url')
                . '/woundmed-order?token=' . $token
                . '&order_id=' . $order->order_id;

            $totalAsp = 0;
            foreach ($validated['items'] as $item) {
                $totalAsp += $item['asp'] * $item['quantity'];
            }

            $emailBody = OrderNotificationEmail::getTemplate([
                'order_code'        => $orderCode,
                'tracking_number'   => $trackingNum,
                'clinic_name'       => OrderHelper::getClinicName($validated['clinic_id']),
                'clinician_name'    => OrderHelper::getClinicianName($validated['clinician_id']),
                'manufacturer_name' => OrderHelper::getManufacturerName($validated['manufacturer_id']),
                'patient_name'      => OrderHelper::getPatientName($validated['patient_id']),
                'items'             => array_map(function ($item) {
                    return [
                        'brand_name' => OrderHelper::getBrandName($item['brand_id']),
                        'size_name'  => OrderHelper::getGraftSizeName($item['graft_id']),
                        'quantity'   => $item['quantity'],
                        'asp'        => $item['asp'],
                        'subtotal'   => $item['asp'] * $item['quantity'],
                    ];
                }, $validated['items']),
                'total_asp'       => $totalAsp,
                'order_link'      => $orderUrl
            ]);

            $emailService = new EmailService();

            $params = [
                'to'        => $email,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WoundMed Orders',
                'subject'   => "New Order Created: {$orderCode}",
                'body'      => $emailBody,
            ];

            $response = $emailService->send_email(
                $params,
                'Order created',
                'Order created'
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
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

    public function updateOrder(Request $request, $orderId)
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

    public function updateOrderStatus(Request $request, $orderId)
    {
        $validated = $request->validate([
            'order_status' => 'required|integer|min:0|max:4'
        ]);

        $order = Orders::findOrFail($orderId);
        $order->update([
            'order_status' => $validated['order_status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
        ]);
    }

    public function deleteOrder(Request $request, $orderId)
    {
        try {
            $order = Orders::findOrFail($orderId);
            $order->delete();

            return response()->json([
                'message' => 'Order details deleted successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Operation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order details: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function validateMagicLink(Request $request)
    {

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
                'message' => 'This magic link has already been used.'
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

        return response()->json([
            'success' => true,
            'order'   => $order
        ]);
    }

    public function updateMagicOrderStatus(Request $request, $orderId)
    {
        $validated = $request->validate([
            'order_status' => 'required|integer|min:0|max:4',
            'token'        => 'required|string'
        ]);

        $order = Orders::findOrFail($orderId);
        $order->update([
            'order_status' => $validated['order_status']
        ]);

        # Validate magic token first
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

        # If already used (expired)
        if (!is_null($tokenRecord->used_at)) {
            return response()->json([
                'success' => false,
                'message' => 'This magic link has already been used.'
            ], 400);
        }

        # Update order status
        $order = Orders::findOrFail($orderId);
        $order->update([
            'order_status' => $validated['order_status']
        ]);

        if ((int) $validated['order_status'] === 3) {
            DB::table('magic_tokens')
                ->where('order_id', $orderId)
                ->update(['used_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
        ]);
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
                if (is_array($manufacturer->primary_email)) {
                    $emails = $manufacturer->primary_email;
                } else {
                    $emails = array_map('trim', explode(',', $manufacturer->primary_email));
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
                    'from_name' => 'WoundMed Admin',
                    'subject'   => "Follow-Up Required: {$order->order_code}",
                    'body'      => $emailBody,
                ], 'Follow-up email sent', 'Follow-up email sent');
            }

            $order->update([
                'followup_last_sent_at' => now()
            ]);

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

        if (!$user || $user->user_role !== 2) {
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
        // $userId = Auth::id();
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
                'ivr_id' => 'required|integer|exists:woundmed_ivr,ivr_id',
                'manufacturer_id' => 'required|integer|exists:woundmed_manufacturers,manufacturer_id',
            ]);

            $orderCode = 'ORD-' . strtoupper(uniqid());
            $trackingNum = 'TRK-' . strtoupper(Str::random(10));

            $order = Orders::create([
                'order_code' => $orderCode,
                'clinic_id' => $validated['clinic_id'],
                'user_id' => $validated['clinician_id'],
                'patient_id' => $validated['patient_id'],
                // 'ordering_user_id' => $userId,
                'ivr_id' => $validated['ivr_id'],
                'manufacturer_id' => $validated['manufacturer_id'],
                'tracking_num' => $trackingNum,
                'notes' => $validated['notes'],
                'items' => $validated['items'],
                'order_status' => 0,
                'ordered_at' => now(),
            ]);

            # update stock by subtracting the current graft stock from quantity for each graftsizes
            DB::transaction(function() use ($validated) {
                foreach ($validated['items'] as $item) {

                    $graft = GraftSize::lockForUpdate()->find($item['graft_id']);

                    if ($graft->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for " . $graft->size);
                    }

                    $graft->stock -= $item['quantity'];
                    $graft->save();
                }
            });

            $token = Str::random(64);

            DB::table('magic_tokens')->insert([
                'manufacturer_id' => $validated['manufacturer_id'],
                'order_id'        => $order->order_id,
                'token'           => hash('sha256', $token),
                'expires_at'      => now()->addDays(60),
                'created_at'      => now(),
            ]);

            $email = $request->primary_email;
            $orderUrl = config('app.frontend_url')
                . '/woundmed-order?token=' . $token
                . '&order_id=' . $order->order_id;

            $totalAsp = 0;
            foreach ($validated['items'] as $item) {
                $totalAsp += $item['asp'] * $item['quantity'];
            }

            $emailBody = OrderNotificationEmail::getTemplate([
                'order_code'        => $orderCode,
                'tracking_number'   => $trackingNum,
                'clinic_name'       => OrderHelper::getClinicName($validated['clinic_id']),
                'clinician_name'    => OrderHelper::getClinicianName($validated['clinician_id']),
                'manufacturer_name' => OrderHelper::getManufacturerName($validated['manufacturer_id']),
                'patient_name'      => OrderHelper::getPatientName($validated['patient_id']),
                'items'             => array_map(function ($item) {
                    return [
                        'brand_name' => OrderHelper::getBrandName($item['brand_id']),
                        'size_name'  => OrderHelper::getGraftSizeName($item['graft_id']),
                        'quantity'   => $item['quantity'],
                        'asp'        => $item['asp'],
                        'subtotal'   => $item['asp'] * $item['quantity'],
                    ];
                }, $validated['items']),
                'total_asp'       => $totalAsp,
                'order_link'      => $orderUrl
            ]);

            $emailService = new EmailService();

            $params = [
                'to'        => $email,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WoundMed Orders',
                'subject'   => "New Order Created: {$orderCode}",
                'body'      => $emailBody,
            ];

            $response = $emailService->send_email(
                $params,
                'Order created',
                'Order created'
            );

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
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
}

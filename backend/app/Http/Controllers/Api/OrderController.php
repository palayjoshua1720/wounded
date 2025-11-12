<?php

namespace App\Http\Controllers\Api;

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

class OrderController extends Controller
{
    public function getAllOrders(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        try {
            $orders = Orders::with(['clinic', 'clinician', 'patient', 'brand.manufacturer', 'graft', 'ivr'])
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
            $clinics = PatientInfo::with(['clinics', 'ivrs', 'user'])
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
            ]);

            $orderCode = 'ORD-' . strtoupper(uniqid());
            $trackingNum = 'TRK-' . strtoupper(Str::random(10));

            $order = Orders::create([
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
}

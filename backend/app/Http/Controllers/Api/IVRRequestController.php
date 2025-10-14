<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Brand;
use App\Models\IVR;
use App\Models\Manufacturer;
use App\Models\PatientInfo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IVRRequestController extends Controller
{
    public function getAllIVRRequests(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $ivrRequests = IVR::with([
            'clinic',
            'brand.manufacturer',
            'manufacturer',
            'patient'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return response()->json([
            'data' => $ivrRequests->items(),
            'meta' => [
                'current_page' => $ivrRequests->currentPage(),
                'last_page'    => $ivrRequests->lastPage(),
                'per_page'     => $ivrRequests->perPage(),
                'total'        => $ivrRequests->total(),
            ]
        ]);
    }

    public function getAllBrands(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $brandData = Brand::with([
            'manufacturer',
        ])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return response()->json([
            'brand_data' => $brandData->items(),
            'meta' => [
                'current_page' => $brandData->currentPage(),
                'last_page'    => $brandData->lastPage(),
                'per_page'     => $brandData->perPage(),
                'total'        => $brandData->total(),
            ]
        ]);
    }

    public function getAllClinic(Request $request)
    {
        echo '<pre>';
        print_r("This is Clinic");
        echo '<br>';
        echo '</pre>';
        exit;
    }

    public function getAllPatientInfo(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $patientData = PatientInfo::with([
            'user',
            'clinics',
            'ivrs'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return response()->json([
            'patient_data' => $patientData->items(),
            'meta' => [
                'current_page' => $patientData->currentPage(),
                'last_page'    => $patientData->lastPage(),
                'per_page'     => $patientData->perPage(),
                'total'        => $patientData->total(),
            ]
        ]);
    }

    public function addIVRRequest(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $tempPassword = Str::random(12);
        $prevHash = $this->getLastRowHash();

        try {
            $validated = $request->validate([
                'patient_id' => 'required|int|max:255',
                'brand_id' => 'required|int|max:255',
                'notes' => 'required|string',
            ]);

            $newIVR = IVR::create([
                'clinic_id' => $request['clinic_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'manufacturer_id' => $request['manufacturer_id'] ?? null,
                'patient_id' => $validated['patient_id'] ?? null,
                'description' => $validated['notes'] ?? null,
                'eligibility_status' => $request['eligibility_status'] ?? 0,
                'submitted_at' => now(),
                'timestamp' => now(),
            ]);

            $newIVR->update([
                'ivr_number' => '#IVR-' . $newIVR->ivr_id
            ]);
        } catch (ValidationException $e) {
            //throw $th;
        }
    }

    public function updateIVRRequest(Request $request, $id)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $tempPassword = Str::random(12);
        $prevHash = $this->getLastRowHash();

        try {
            $validated = $request->validate([
                'patient_id' => 'required|int|max:255',
                'brand_id' => 'required|int|max:255',
                'notes' => 'required|string',
            ]);

            $ivr = IVR::findOrFail($id);
            $ivr->update([
                'clinic_id' => $request['clinic_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'manufacturer_id' => $request['manufacturer_id'] ?? null,
                'patient_id' => $validated['patient_id'] ?? null,
                'description' => $validated['notes'] ?? null,
                'eligibility_status' => $validated['eligibility_status'] ?? 0,
                'ivr_status' => $validated['ivr_status'] ?? 0,
                'verified_at' => now(),
                'timestamp' => now(),
            ]);
        } catch (ValidationException $e) {
            //throw $th;
        }
    }

    public function deleteIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->delete();

        return response()->json(['message' => 'IVR Request deleted successfully.']);
    }

    public function archiveIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->update(['ivr_status' => 1]);

        return response()->json(['message' => 'IVR Request archived successfully.']);
    }

    public function unarchiveIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->update(['ivr_status' => 0]);

        return response()->json(['message' => 'IVR Request unarchived successfully.']);
    }

    private function generateRowHash(array $data, $prevHash = null)
    {
        $string = json_encode($data) . $prevHash;
        return hash('sha256', $string);
    }

    private function getLastRowHash()
    {
        $last = DB::table('woundmed_audit_logs')
            ->select('row_hash')
            ->latest('audit_log_id')
            ->first();
        return $last?->row_hash ?? null;
    }
}
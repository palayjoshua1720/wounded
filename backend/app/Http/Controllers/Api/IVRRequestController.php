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
use Illuminate\Support\Facades\Storage;

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

        $ivrRequests->getCollection()->transform(function ($ivr) {

            if ($ivr->filepath) {
                $fileName = basename($ivr->filepath);

                $ivr->filepath = url('/storage/ivr/' . $fileName);
            }

            return $ivr;
        });

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

    public function getAllManufacturers(Request $request)
    {

        $manufacturerData = Manufacturer::orderBy('created_at', 'desc')
            ->where('manufacturer_status', 0)
            ->get();


        return response()->json([
            'success' => true,
            'manufacturer_data' => $manufacturerData,
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
                'brand_id' => 'nullable|int|max:255',
                'manufacturer_id' => 'required|int|max:255',
                'filepath' => 'required|file|mimes:pdf,doc,docx|max:10240',
                'notes' => 'nullable|string',
            ]);

            $path = $request->file('filepath')->store('ivr', 'public');

            $newIVR = IVR::create([
                'ivr_number' => '#IVR-' . strtoupper(uniqid()),
                'clinic_id' => $request['clinic_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'manufacturer_id' => $validated['manufacturer_id'] ?? null,
                'patient_id' => $validated['patient_id'] ?? null,
                'filepath' => $path,
                'description' => $validated['notes'] ?? null,
                'eligibility_status' => $request['eligibility_status'] ?? 0,
                'submitted_at' => now(),
                'timestamp' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'IVR created successfully!',
            ]);

        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create IVR: ' . $th->getMessage(),
            ], 500);
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
                'brand_id' => 'nullable|int|max:255',
                'manufacturer_id' => 'required|int|max:255',
                'notes' => 'required|string',
                'filepath' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
                'remove_existing_file' => 'nullable|boolean',
            ]);

            $ivr = IVR::findOrFail($id);

            if ($request->hasFile('filepath')) {
                if ($ivr->filepath && Storage::disk('private')->exists($ivr->filepath)) {
                    Storage::disk('private')->delete($ivr->filepath);
                }

                $newPath = $request->file('filepath')->store('ivr', 'private');
                $ivr->filepath = $newPath;
            }

            elseif ($request->remove_existing_file) {
                if ($ivr->filepath && Storage::disk('private')->exists($ivr->filepath)) {
                    Storage::disk('private')->delete($ivr->filepath);
                }

                $ivr->filepath = null;
            }

            $ivr->clinic_id = $request->clinic_id ?? $ivr->clinic_id;
            $ivr->brand_id = $validated['brand_id'] ?? null;
            $ivr->manufacturer_id = $validated['manufacturer_id'];
            $ivr->patient_id = $validated['patient_id'];
            $ivr->description = $validated['notes'];
            // $ivr->verified_at = now();
            // $ivr->timestamp = now();

            $ivr->save();

            return response()->json([
                'success' => true,
                'message' => 'Order details updated successfully!',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update IVR: ' . $th->getMessage(),
            ], 500);
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

    public function downloadIVRForm($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        if (!$manufacturer->filepath || !Storage::disk('private')->exists($manufacturer->filepath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $filename = basename($manufacturer->filepath);
        return Storage::disk('private')->download($manufacturer->filepath, $filename);
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
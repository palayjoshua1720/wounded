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
use App\Services\EmailService;
use App\Template\IVRRequestNotificationEmail;
use App\Helpers\IVRHelper;
use App\Traits\AuditLogger;

class IVRRequestController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_ivr';
    }

    protected function getEntityType()
    {
        return 'ivr';
    }

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

        // $ivrRequests->getCollection()->transform(function ($ivr) {

        //     if ($ivr->filepath) {
        //         $fileName = basename($ivr->filepath);

        //         $ivr->filepath = url('/storage/ivr/' . $fileName);
        //     }

        //     return $ivr;
        // });

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
            'clinic',
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
        $tempPassword = Str::random(12);
        try {
            $validated = $request->validate([
                'patient_id' => 'required|int|max:255',
                'brand_id' => 'nullable|int|max:255',
                'manufacturer_id' => 'required|int|max:255',
                'filepath' => 'required|file|mimes:pdf,doc,docx|max:10240',
                'notes' => 'nullable|string',
            ]);
            // $path = $request->file('filepath')->store('ivr', 'public');
            $path = null;
            if ($request->hasFile('filepath')) {
                $filename = time().'.'.$request->file('filepath')->getClientOriginalExtension();
                $path = $request->file('filepath')->storeAs('ivr', $filename, 'private');
            }
            
            $ivrNumber = '#IVR-' . strtoupper(uniqid());
            $newIVR = IVR::create([
                'ivr_number' => $ivrNumber,
                'clinic_id' => $request['clinic_id'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'manufacturer_id' => $validated['manufacturer_id'] ?? null,
                'patient_id' => $validated['patient_id'] ?? null,
                'filepath' => $path,
                'description' => $validated['notes'] ?? null,
                'eligibility_status' => 0,
                'submitted_at' => now(),
                'timestamp' => now(),
            ]);
            $token = Str::random(64);
            DB::table('magic_tokens')->insert([
                'ivr_id'          => $newIVR->ivr_id,
                'manufacturer_id' => $validated['manufacturer_id'],
                'token'           => hash('sha256', $token),
                'expires_at'      => now()->addDays(60),
                'created_at'      => now(),
            ]);
            $email = $request->primary_email;
            $ivrUrl = config('app.frontend_url')
                . '/woundmed-ivr-request?token=' . $token
                . '&ivr_id=' . $newIVR->ivr_id;
            $emailBody = IVRRequestNotificationEmail::getTemplate([
                'ivr_number'        => $ivrNumber,
                'patient_name'      => IVRHelper::getPatientName($validated['patient_id']),
                'clinic_name'       => IVRHelper::getClinicName($request['clinic_id']),
                'brand_name'        => IVRHelper::getManufacturerName($validated['manufacturer_id']),
                'manufacturer_name' => IVRHelper::getManufacturerName($validated['manufacturer_id']),
                'eligibility_label'  => 'Pending',
                'file_url'          => $path,
                'ivr_link'      => $ivrUrl
            ]);
            $emailService = new EmailService();
            $params = [
                'to'        => $email,
                'from'      => 'noreply@woundmed.com',
                'from_name' => 'WoundMed IVR',
                'subject'   => "New IVR Request: {$ivrNumber}",
                'body'      => $emailBody,
            ];
            $response = $emailService->send_email(
                $params,
                'IVR Request',
                'IVR Request'
            );

            $this->logAudit($request, 'ivr_request_create', "IVR request created: {$ivrNumber}", $newIVR->ivr_id);

            return response()->json([
                'success' => true,
                'message' => 'IVR created successfully!',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create IVR Request: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateIVRRequest(Request $request, $id)
    {
        $tempPassword = Str::random(12);

        try {
            $validated = $request->validate([
                'patient_id' => 'required|int|max:255',
                'brand_id' => 'nullable|int|max:255',
                'manufacturer_id' => 'required|int|max:255',
                'eligibility_status' => 'nullable|int|max:255',
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
            } elseif ($request->remove_existing_file) {
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
            $ivr->eligibility_status = $validated['eligibility_status'];

            $ivr->save();

            $this->logAudit($request, 'ivr_request_update', "IVR request updated: {$ivr->ivr_number}", $ivr->ivr_id);

            return response()->json([
                'success' => true,
                'message' => 'IVR details updated successfully!',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update IVR: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function updateEligibilityStatus(Request $request, $id)
    {
        $tempPassword = Str::random(12);

        try {
            $validated = $request->validate([
                'eligibility_status' => 'required|int|max:255',
            ]);

            $ivr = IVR::findOrFail($id);

            $ivr->eligibility_status = $validated['eligibility_status'];

            $ivr->save();

            $this->logAudit($request, 'ivr_request_eligiblestatus_update', "IVR Eligiblitiy Status Updated: {$validated['eligibility_status']}", $ivr->ivr_id);

            return response()->json([
                'success' => true,
                'message' => 'IVR eligibility status updated successfully!',
            ]);
        } catch (ValidationException $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update IVR eligibility status: ' . $th->getMessage(),
            ], 500);
        }
    }

    public function deleteIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->delete();
        
        $this->logAudit($request, 'ivr_request_delete', "IVR Request Deleted: {$ivr->ivr_number}", $ivr->ivr_id);

        return response()->json(['message' => 'IVR Request deleted successfully.']);
    }

    public function archiveIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->update(['ivr_status' => 1]);

        $this->logAudit($request, 'ivr_request_archive', "IVR Request Archived: {$ivr->ivr_number}", $ivr->ivr_id);

        return response()->json(['message' => 'IVR Request archived successfully.']);
    }

    public function unarchiveIVRRequest(Request $request, $id)
    {
        $ivr = IVR::findOrFail($id);
        $ivr->update(['ivr_status' => 0]);

        $this->logAudit($request, 'ivr_request_unarchive', "IVR Request Unarchived: {$ivr->ivr_number}", $ivr->ivr_id);

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

    // magic links
    public function validateMagicLinkIVR(Request $request)
    {

        $tokenPlain = $request->input('token');
        $ivrId    = $request->input('ivr_id');

        $token = DB::table('magic_tokens')
            ->where('ivr_id', $ivrId)
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
        $ivrRequests = IVR::with([
            'clinic',
            'brand.manufacturer',
            'manufacturer',
            'patient'
        ])
            ->where('ivr_id', $ivrId)
            ->first();

        return response()->json([
            'success' => true,
            'ivr_data'   => $ivrRequests
        ]);
    }

    public function updateMagicIVRStatus(Request $request, $ivrId)
    {
        $validated = $request->validate([
            'eligibility_status' => 'required|integer|min:0|max:4',
            'token'              => 'required|string'
        ]);

        $ivr = IVR::findOrFail($ivrId);

        $ivr->update([
            'eligibility_status' => $validated['eligibility_status']
        ]);

        # Validate magic token first
        $tokenRecord = DB::table('magic_tokens')
            ->where('ivr_id', $ivrId)
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
                'message' => 'This access link has already been used.'
            ], 400);
        }

        # Update IVR status
        $ivr = IVR::findOrFail($ivrId);
        $ivr->update([
            'eligibility_status' => $validated['eligibility_status']
        ]);

        if ((int) $validated['eligibility_status'] === 1) {
            DB::table('magic_tokens')
                ->where('ivr_id', $ivrId)
                ->update(['used_at' => now()]);
        }

        $this->logAudit($request, 'ivr_request_magic_update', "IVR Request Magic Link Updated: {$validated['eligibility_status']}", $ivr->ivr_id);

        return response()->json([
            'success' => true,
            'message' => 'IVR status updated successfully.',
        ]);
    }

    // live ivr file streaming
    public function viewIVRFile($filename)
    {
        $path = "ivr/" . $filename;

        if (!Storage::disk('private')->exists($path)) {
            return abort(404, 'File not found.');
        }

        $file = Storage::disk('private')->get($path);
        $mime = Storage::disk('private')->mimeType($path);

        return response($file, 200)->header('Content-Type', $mime);
    }
}

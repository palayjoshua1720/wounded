<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manufacturer;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogger;

class ManufacturerController extends Controller
{
    use AuditLogger;

    protected function getEntityName()
    {
        return 'woundmed_manufacturers';
    }

    protected function getEntityType()
    {
        return 'manufacturer';
    }

    public function getAllManufacturers(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $manufacturers = Manufacturer::with(['brands'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $formattedManufacturers = $manufacturers->map(function ($m) {
            $logoUrl = $m->logo ? asset('storage/' . $m->logo) : null;

            $formattedBrands = $m->brands->map(function ($b) {
                return [
                    'id' => (string) $b->brand_id,
                    'brandName' => $b->brand_name,
                    'brandStatus' => (int) $b->brand_status,
                ];
            });

            return [
                'id'                => (string) $m->manufacturer_id,
                'manufacturerName'  => $m->manufacturer_name,
                'primaryEmail'      => $m->primary_email,
                'secondaryEmail'    => $m->secondary_email,
                'website'           => $m->website,
                'address'           => $m->address,
                'contactPerson'     => $m->contact_person,
                'contactNumber'     => $m->contact_number,
                'filename'          => $m->filepath ? basename($m->filepath) : '',
                'manufacturerStatus' => (int) $m->manufacturer_status,
                'logoUrl'           => $logoUrl,  // Using existing 'logo' field
                'brands'            => $formattedBrands,
                'createdAt'         => $m->created_at,
                'updatedAt'         => $m->updated_at,
            ];
        });

        return response()->json([
            'manufacturerData' => $formattedManufacturers,
            'meta' => [
                'current_page' => $manufacturers->currentPage(),
                'last_page'    => $manufacturers->lastPage(),
                'per_page'     => $manufacturers->perPage(),
                'total'        => $manufacturers->total(),
            ]
        ]);
    }

    public function addManufacturer(Request $request)
    {
        $validated = $request->validate([
            'manufacturerName'  => 'required|string|max:255',
            'primaryEmail'      => 'required|email|unique:woundmed_manufacturers,primary_email',
            'secondaryEmail'    => 'nullable|email',
            'website'           => 'nullable|url',
            'address'           => 'nullable|string|max:255',
            'contactPerson'     => 'required|string|max:255',
            'contactNumber'     => 'required|string|max:20',
            'ivrForm'           => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional logo, 2MB max
            'manufacturerStatus' => 'required|in:0,1,2',
        ]);

        // Store IVR file securely in private storage
        $path = $request->file('ivrForm')->store('manufacturers', 'private');

        // Handle logo upload (public storage for easy access)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('manufacturers/logos', 'public');
        }

        $manufacturer = Manufacturer::create([
            'manufacturer_name'     => $validated['manufacturerName'],
            'primary_email'         => $validated['primaryEmail'],
            'secondary_email'       => $validated['secondaryEmail'] ?? null,
            'website'               => $validated['website'] ?? null,
            'address'               => $validated['address'] ?? null,
            'contact_person'        => $validated['contactPerson'],
            'contact_number'        => $validated['contactNumber'],
            'filepath'              => $path, // store relative path only
            'logo'                  => $logoPath, // Using existing 'logo' field
            'manufacturer_status'   => $validated['manufacturerStatus'] ?? 0,
        ]);

        $this->logAudit($request, 'manufacturer_create', "Manufacturer created: {$validated['manufacturerName']}", $manufacturer->manufacturer_id);

        return response()->json([
            'message' => 'Manufacturer created successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function archiveManufacturer(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        if ($manufacturer->manufacturer_status !== 2) {
            $oldStatus = $manufacturer->manufacturer_status;
            $manufacturer->manufacturer_status = 2; // Archived
            $manufacturer->save();
            $this->logAudit($request, 'manufacturer_archive', "Manufacturer archived: {$manufacturer->manufacturer_name} (from status {$oldStatus})", $id);
        }
        return response()->json([
            'message' => 'Manufacturer archived successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function toggleManufacturerStatus(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $oldStatus = $manufacturer->manufacturer_status;
        $statusMap = [
            2 => 0, // Archived -> Active
            1 => 0, // Inactive -> Active
            0 => 1, // Active -> Inactive
        ];
        $newStatus = $statusMap[$oldStatus] ?? $oldStatus;
        $manufacturer->manufacturer_status = $newStatus;
        $manufacturer->save();
        $statusText = $newStatus === 0 ? 'Active' : 'Inactive';
        if ($oldStatus === 2) {
            $actionType = 'manufacturer_reactivate';
        } elseif ($newStatus === 0) {
            $actionType = 'manufacturer_activate';
        } else {
            $actionType = 'manufacturer_deactivate';
        }
        // Log successful toggle
        $this->logAudit($request, $actionType, "Manufacturer status toggled to {$statusText}: {$manufacturer->manufacturer_name} (from {$oldStatus})", $id);
        return response()->json([
            'message' => 'Manufacturer status updated successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function deleteManufacturer(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $name = $manufacturer->manufacturer_name; // Capture before delete
        $manufacturer->delete(); // Soft delete
        // Log successful deletion
        $this->logAudit($request, 'manufacturer_delete', "Manufacturer deleted: {$name}", $id);
        return response()->json([
            'message' => 'Manufacturer deleted successfully',
        ]);
    }

    public function updateManufacturer(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $validated = $request->validate([
            'manufacturerName'  => 'required|string|max:255',
            'primaryEmail'      => 'required|email|unique:woundmed_manufacturers,primary_email,' . $id . ',manufacturer_id',
            'secondaryEmail'    => 'nullable|email',
            'website'           => 'nullable|url',
            'address'           => 'nullable|string|max:255',
            'contactPerson'     => 'required|string|max:255',
            'contactNumber'     => 'required|string|max:20',
            'ivrForm'           => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional logo
            'manufacturerStatus' => 'required|in:0,1,2',
        ]);

        // Handle IVR file update if provided
        if ($request->hasFile('ivrForm')) {
            // Optionally delete old file if exists
            if ($manufacturer->filepath && Storage::disk('private')->exists($manufacturer->filepath)) {
                Storage::disk('private')->delete($manufacturer->filepath);
            }
            $path = $request->file('ivrForm')->store('manufacturers', 'private');
            $manufacturer->filepath = $path;
        }

        // Handle logo update if provided
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($manufacturer->logo && Storage::disk('public')->exists($manufacturer->logo)) {
                Storage::disk('public')->delete($manufacturer->logo);
            }
            $logoPath = $request->file('logo')->store('manufacturers/logos', 'public');
            $manufacturer->logo = $logoPath;
        }

        $oldName = $manufacturer->manufacturer_name;
        $manufacturer->manufacturer_name = $validated['manufacturerName'];
        $manufacturer->primary_email = $validated['primaryEmail'];
        $manufacturer->secondary_email = $validated['secondaryEmail'] ?? null;
        $manufacturer->website = $validated['website'] ?? null;
        $manufacturer->address = $validated['address'] ?? null;
        $manufacturer->contact_person = $validated['contactPerson'];
        $manufacturer->contact_number = $validated['contactNumber'];
        $manufacturer->manufacturer_status = $validated['manufacturerStatus'];
        $manufacturer->save();
        // Log successful update
        $this->logAudit($request, 'manufacturer_update', "Manufacturer updated: {$oldName} -> {$validated['manufacturerName']}", $id);
        return response()->json([
            'message' => 'Manufacturer updated successfully',
            'data'    => $manufacturer,
        ]);
    }

    //
    public function handleAction(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $action = $request->input('action');
        switch ($action) {
            case 'activate':
                $manufacturer->manufacturer_status = 0; // Active
                break;
            case 'deactivate':
                $manufacturer->manufacturer_status = 1; // Inactive
                break;
            case 'reactivate':
                $manufacturer->manufacturer_status = 0; // Back to Active
                break;
            case 'archive':
                $manufacturer->manufacturer_status = 2; // Archived
                break;
            case 'delete':
                $manufacturer->forceDelete();
                // return response()->json([
                //     'message' => 'Manufacturer deleted successfully',
                //     // 'data'    => null,
                // ]);
        }
        $manufacturer->save();
        return response()->json([
            'message' => "Manufacturer {$action}d successfully",
            'data'    => $manufacturer,
        ]);
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
}

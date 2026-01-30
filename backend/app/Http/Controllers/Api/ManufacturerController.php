<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manufacturer;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
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
                'orderEmail'        => $m->order_email,
                'eligibilityEmail'  => $m->eligibility_email,
                'secondaryEmail'    => $m->secondary_email,
                'website'           => $m->website,
                'address'           => $m->address,
                'contactPerson'     => $m->contact_person,
                'contactNumber'     => $m->contact_number,
                'ivrFilename'       => $m->ivr_file       ? basename($m->ivr_file)       : '',
                'orderFilename'     => $m->order_file     ? basename($m->order_file)     : '',
                'onboardingFilename' => $m->onboarding_file ? basename($m->onboarding_file) : '',
                'manufacturerStatus' => (int) $m->manufacturer_status,
                'logoUrl'           => $logoUrl,
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
            'orderEmail'           => 'required|email',
            'eligibilityEmail'     => 'required|email',
            'secondaryEmail'    => 'nullable|email',
            'website'           => 'nullable|url',
            'address'           => 'nullable|string|max:255',
            'contactPerson'     => 'required|string|max:255',
            'contactNumber'     => 'required|string|max:20',
            'manufacturerStatus' => 'required|in:0,1,2',
            'ivrForm'            => 'required|file|mimes:pdf,doc,docx|max:10240',
            'orderForm'          => 'required|file|mimes:pdf,doc,docx|max:10240',
            'onboardingForm'     => 'required|file|mimes:pdf,doc,docx|max:10240',
            'logo'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Store files in organized subfolders
        $ivrPath       = $request->file('ivrForm')->store('manufacturers/ivr', 'private');
        $orderPath     = $request->file('orderForm')->store('manufacturers/orders', 'private');
        $onboardingPath = $request->file('onboardingForm')->store('manufacturers/onboarding', 'private');

        // Handle logo upload (public storage for easy access)
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('manufacturers/logos', 'public');
        }

        $manufacturer = Manufacturer::create([
            'manufacturer_name'     => $validated['manufacturerName'],
            'primary_email'         => $validated['primaryEmail'],
            'order_email'           => $validated['orderEmail'],
            'eligibility_email'     => $validated['eligibilityEmail'],
            'secondary_email'       => $validated['secondaryEmail'] ?? null,
            'website'               => $validated['website'] ?? null,
            'address'               => $validated['address'] ?? null,
            'contact_person'        => $validated['contactPerson'],
            'contact_number'        => $validated['contactNumber'],
            'ivr_file'              => $ivrPath,
            'order_file'            => $orderPath,
            'onboarding_file'       => $onboardingPath,
            'logo'                  => $logoPath, // Using existing 'logo' field
            'manufacturer_status'   => $validated['manufacturerStatus'] ?? 0,
        ]);

        $this->logAudit($request, 'manufacturer_create', "Manufacturer created: {$validated['manufacturerName']}", $manufacturer->manufacturer_id);

        return response()->json([
            'message' => 'Manufacturer created successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function updateManufacturer(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $validated = $request->validate([
            'manufacturerName'   => 'required|string|max:255',
            'primaryEmail'       => 'required|email|unique:woundmed_manufacturers,primary_email,' . $id . ',manufacturer_id',
            'orderEmail'         => 'required|email',
            'eligibilityEmail'   => 'required|email',
            'secondaryEmail'     => 'nullable|email',
            'website'            => 'nullable|url',
            'address'            => 'nullable|string|max:255',
            'contactPerson'      => 'required|string|max:255',
            'contactNumber'      => 'required|string|max:20',
            'manufacturerStatus' => 'required|in:0,1,2',

            // Files are optional on update
            'ivrForm'            => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'orderForm'          => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'onboardingForm'     => 'nullable|file|mimes:pdf,doc,docx|max:10240',

            'logo'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_logo'        => 'nullable|in:1',
            'remove_ivr'         => 'nullable|in:1',
            'remove_order'       => 'nullable|in:1',
            'remove_onboarding'  => 'nullable|in:1',
        ]);

        $oldName = $manufacturer->manufacturer_name;

        // ── LOGO ────────────────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($manufacturer->logo && Storage::disk('public')->exists($manufacturer->logo)) {
                Storage::disk('public')->delete($manufacturer->logo);
            }
            $manufacturer->logo = $request->file('logo')->store('manufacturers/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($manufacturer->logo && Storage::disk('public')->exists($manufacturer->logo)) {
                Storage::disk('public')->delete($manufacturer->logo);
            }
            $manufacturer->logo = null;
        }

        // ── IVR FILE ────────────────────────────────────────────────────────────
        if ($request->hasFile('ivrForm')) {
            if ($manufacturer->ivr_file && Storage::disk('private')->exists($manufacturer->ivr_file)) {
                Storage::disk('private')->delete($manufacturer->ivr_file);
            }
            $manufacturer->ivr_file = $request->file('ivrForm')->store('manufacturers/ivr', 'private');
        } elseif ($request->boolean('remove_ivr')) {
            if ($manufacturer->ivr_file && Storage::disk('private')->exists($manufacturer->ivr_file)) {
                Storage::disk('private')->delete($manufacturer->ivr_file);
            }
            $manufacturer->ivr_file = null;
        }

        // ── ORDER FILE ──────────────────────────────────────────────────────────
        if ($request->hasFile('orderForm')) {
            if ($manufacturer->order_file && Storage::disk('private')->exists($manufacturer->order_file)) {
                Storage::disk('private')->delete($manufacturer->order_file);
            }
            $manufacturer->order_file = $request->file('orderForm')->store('manufacturers/orders', 'private');
        } elseif ($request->boolean('remove_order')) {
            if ($manufacturer->order_file && Storage::disk('private')->exists($manufacturer->order_file)) {
                Storage::disk('private')->delete($manufacturer->order_file);
            }
            $manufacturer->order_file = null;
        }

        // ── ONBOARDING FILE ─────────────────────────────────────────────────────
        if ($request->hasFile('onboardingForm')) {
            if ($manufacturer->onboarding_file && Storage::disk('private')->exists($manufacturer->onboarding_file)) {
                Storage::disk('private')->delete($manufacturer->onboarding_file);
            }
            $manufacturer->onboarding_file = $request->file('onboardingForm')->store('manufacturers/onboarding', 'private');
        } elseif ($request->boolean('remove_onboarding')) {
            if ($manufacturer->onboarding_file && Storage::disk('private')->exists($manufacturer->onboarding_file)) {
                Storage::disk('private')->delete($manufacturer->onboarding_file);
            }
            $manufacturer->onboarding_file = null;
        }

        // Basic fields
        $manufacturer->fill([
            'manufacturer_name'   => $validated['manufacturerName'],
            'primary_email'       => $validated['primaryEmail'],
            'order_email'         => $validated['orderEmail'],
            'eligibility_email'   => $validated['eligibilityEmail'],
            'secondary_email'     => $validated['secondaryEmail'] ?? null,
            'website'             => $validated['website'] ?? null,
            'address'             => $validated['address'] ?? null,
            'contact_person'      => $validated['contactPerson'],
            'contact_number'      => $validated['contactNumber'],
            'manufacturer_status' => $validated['manufacturerStatus'],
        ]);

        $manufacturer->save();

        $this->logAudit($request, 'manufacturer_update', "Manufacturer updated: {$oldName} → {$validated['manufacturerName']}", $id);

        return response()->json([
            'message' => 'Manufacturer updated successfully',
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
        $name = $manufacturer->manufacturer_name;

        // Clean up files
        foreach (['ivr_file', 'order_file', 'onboarding_file'] as $field) {
            $path = $manufacturer->$field;
            if ($path && Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }
        }

        if ($manufacturer->logo && Storage::disk('public')->exists($manufacturer->logo)) {
            Storage::disk('public')->delete($manufacturer->logo);
        }

        $manufacturer->delete();

        $this->logAudit($request, 'manufacturer_delete', "Manufacturer deleted: {$name}", $id);

        return response()->json([
            'message' => 'Manufacturer deleted successfully',
        ]);
    }

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

    public function downloadFile(Request $request, $id, $type)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $path = match ($type) {
            'ivr'        => $manufacturer->ivr_file,
            'order'      => $manufacturer->order_file,
            'onboarding' => $manufacturer->onboarding_file,
            default      => null,
        };

        if (!$path || !Storage::disk('private')->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $filename = basename($path);
        return Storage::disk('private')->download($path, $filename);
    }
}

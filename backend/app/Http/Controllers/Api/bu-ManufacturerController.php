<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManufacturerController extends Controller
{
    public function getAllManufacturers(Request $request)
    {
        $perPage = $request->query('per_page', 10);

        $manufacturers = DB::table('woundmed_manufacturers')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);


        $formattedManufacturers = $manufacturers->map(function ($m) {
            return [
                'id'        => (string) $m->manufacturer_id,
                'manufacturerName'      => $m->manufacturer_name,
                'primaryEmail'     => $m->primary_email,
                'secondaryEmail' => $m->secondary_email,
                'website'   => $m->website,
                'address'   => $m->address,
                'contactPerson' => $m->contact_person,
                'contactNumber' => $m->contact_number,
                'filepath'  => $m->filepath,
                'manufacturerStatus' => (int) $m->manufacturer_status,
                'createdAt' => $m->created_at,
                'updatedAt' => $m->updated_at,
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
            'secondaryEmail'     => 'nullable|email',
            'website'            => 'nullable|url',
            'address'            => 'nullable|string|max:255',
            'contactPerson'      => 'required|string|max:255',
            'contactNumber'      => 'required|string|max:20',
            'ivrForm'           => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'manufacturerStatus' => 'required|in:0,1,2',
        ]);

        // store file securely in private storage
        $path = $request->file('ivrForm')->store('manufacturers', 'private');

        $manufacturer = Manufacturer::create([
            'manufacturer_name'    => $validated['manufacturerName'],
            'primary_email'        => $validated['primaryEmail'],
            'secondary_email'      => $validated['secondaryEmail'] ?? null,
            'website'              => $validated['website'] ?? null,
            'address'              => $validated['address'] ?? null,
            'contact_person'       => $validated['contactPerson'],
            'contact_number'       => $validated['contactNumber'],
            'filepath'             => $path, // store relative path only
            'manufacturer_status'  => $validated['manufacturerStatus'] ?? 0,
        ]);

        return response()->json([
            'message' => 'Manufacturer created successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function archiveManufacturer($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        if ($manufacturer->manufacturer_status !== 2) {
            $manufacturer->manufacturer_status = 2; // Archived
            $manufacturer->save();
        }

        return response()->json([
            'message' => 'Manufacturer archived successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function toggleManufacturerStatus($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        // If archived, set to Active
        if ($manufacturer->manufacturer_status === 2) {
            $manufacturer->manufacturer_status = 0;
        }
        // If inactive, set to Active
        elseif ($manufacturer->manufacturer_status === 1) {
            $manufacturer->manufacturer_status = 0;
        }
        // If active, set to Inactive
        elseif ($manufacturer->manufacturer_status === 0) {
            $manufacturer->manufacturer_status = 1;
        }

        $manufacturer->save();

        return response()->json([
            'message' => 'Manufacturer status updated successfully',
            'data'    => $manufacturer,
        ]);
    }

    public function deleteManufacturer($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $manufacturer->delete(); // soft delete (use forceDelete() for permanent)

        return response()->json([
            'message' => 'Manufacturer deleted successfully',
        ]);
    }

    public function updateManufacturer(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $validated = $request->validate([
            'manufacturerName'  => 'required|string|max:255',
            'primaryEmail' => 'required|email|unique:woundmed_manufacturers,primary_email,' . $id . ',manufacturer_id',
            'secondaryEmail'    => 'nullable|email',
            'website'           => 'nullable|url',
            'address'           => 'nullable|string|max:255',
            'contactPerson'     => 'required|string|max:255',
            'contactNumber'     => 'required|string|max:20',
            'ivrForm'           => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'manufacturerStatus' => 'required|in:0,1,2',
        ]);

        // If a new file is uploaded, store it and update the path
        if ($request->hasFile('ivrForm')) {
            $path = $request->file('ivrForm')->store('manufacturers', 'private');
            $manufacturer->filepath = $path;
        }

        $manufacturer->manufacturer_name = $validated['manufacturerName'];
        $manufacturer->primary_email = $validated['primaryEmail'];
        $manufacturer->secondary_email = $validated['secondaryEmail'] ?? null;
        $manufacturer->website = $validated['website'] ?? null;
        $manufacturer->address = $validated['address'] ?? null;
        $manufacturer->contact_person = $validated['contactPerson'];
        $manufacturer->contact_number = $validated['contactNumber'];
        $manufacturer->manufacturer_status = $validated['manufacturerStatus'];

        $manufacturer->save();

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

<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Clinic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    
    public function getAllActivity(Request $request)
    {
        $query = DB::table('woundmed_audit_logs')
            ->select(
                'audit_log_id',
                'user_id',
                'attempted_identifier',
                'ip_address',
                'action_type',
                'action_message',
                'entity_id',
                'entity',
                'entity_type',
                'status',
                'timestamp'
            )
            ->whereIn('entity_type', ['clinician', 'authentication'])
            ->orderBy('timestamp', 'desc')
            ->limit(10);

        $logs = $query->get();

        return response()->json([
            'message' => 'Clinician activity fetched successfully',
            'data' => $logs
        ]);
    }

    public function getAllClinicians(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $simple  = $request->query('simple', false);

        $query = DB::table('woundmed_users as u')
            ->where('u.user_role', 3);

        if ($simple) {
            $clinicians = $query
                ->select('u.id', DB::raw("CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) as name"), 'u.user_role')
                ->orderBy('u.created_at')
                ->get();

            return response()->json($clinicians);
        }

        $users = $query
            ->leftJoin('woundmed_clinic_clinician as cc', 'u.id', '=', 'cc.clinician_id')
            ->select(
                'u.id',
                'u.email',
                'u.first_name',
                'u.middle_name',
                'u.last_name',
                'u.phone',
                'u.user_status',
                'u.last_logged_in',
                'u.created_at',
                DB::raw('GROUP_CONCAT(cc.clinic_id) as clinic_ids')
            )
            ->groupBy(
                'u.id',
                'u.email',
                'u.first_name',
                'u.middle_name',
                'u.last_name',
                'u.phone',
                'u.user_status',
                'u.last_logged_in',
                'u.created_at'
            )
            ->orderBy('u.created_at', 'desc')
            ->paginate($perPage);

        $formattedUsers = $users->map(function ($user) {
            $fullname = $user->first_name . 
                ($user->middle_name ? ' ' . $user->middle_name . '. ' : ' ') . 
                $user->last_name;

            return [
                'id'        => (string) $user->id,
                'email'     => $user->email,
                'name'      => $fullname,
                'phone'     => $user->phone,
                'role'      => 'Clinician',
                'clinicIds' => $user->clinic_ids ? explode(',', $user->clinic_ids) : [],
                'isActive'  => $user->user_status,
                'lastLogin' => $user->last_logged_in,
                'createdAt' => $user->created_at,
            ];
        });

        return response()->json([
            'user_data' => $formattedUsers,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ]
        ]);
    }


    public function addClinician(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $tempPassword = Str::random(12);
        $prevHash = $this->getLastRowHash();

        $roleMap = [
            'Admin' => 1,
            'Manager' => 2,
            'Clinician' => 3,
        ];

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:woundmed_users,email',
                'role' => 'required|string',
                'isActive' => 'boolean',
                'clinicId' => 'nullable|integer',
                'manufacturerId' => 'nullable|integer',
                'phone' => 'nullable|string|max:20',
            ]);

            $validated['role'] = $roleMap[$validated['role']] ?? $validated['role'];
            $newClinician = User::create([
                'clinic_id' => $validated['clinicId'] ?? null,
                'manufacturer_id' => $validated['manufacturerId'] ?? null,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => bcrypt($tempPassword),
                'force_password_change' => true,
                'user_role' => $validated['role'],
                'user_status' => $validated['isActive'] ?? 0,
                'phone' => $validated['phone'] ?? null,
            ]);

            if($newClinician){
                $log = [
                    'user_id' => $user->id,
                    'attempted_identifier' => null,
                    'ip_address'=> $ip,
                    'action_type' => 'add_clincian',
                    'action_message' => 'Successful Adding Clinician',
                    'entity_id' => $user->id,
                    'entity' => 'woundmed_users',
                    'entity_type' => 'authentication',
                    'status' => 0,
                    'timestamp' => now(),
                ];
                $log['prev_hash'] = $prevHash;
                $log['row_hash'] = $this->generateRowHash($log, $prevHash);

                DB::table('woundmed_audit_logs')->insert($log);

                return response()->json([
                    'message' => 'Clinician Added Successfully'
                ]);
            } else {
                $log = [
                    'user_id' => $user->id,
                    'attempted_identifier' => null,
                    'ip_address'=> $ip,
                    'action_type' => 'add_clincian',
                    'action_message' => 'Failed Adding Clinician',
                    'entity_id' => $user->id,
                    'entity' => 'woundmed_users',
                    'entity_type' => 'authentication',
                    'status' => 1,
                    'timestamp' => now(),
                ];
                $log['prev_hash'] = $prevHash;
                $log['row_hash'] = $this->generateRowHash($log, $prevHash);

                DB::table('woundmed_audit_logs')->insert($log);

                return response()->json([
                    'message' => 'Error on adding Clinician'
                ], 401);
            }
        } catch (ValidationException $e) {
            $log = [
                'user_id' => $user->id,
                'attempted_identifier' => null,
                'ip_address'=> $ip,
                'action_type' => 'add_clincian',
                'action_message' => 'Failed Adding Clinician, Email already taken',
                'entity_id' => $user->id,
                'entity' => 'woundmed_users',
                'entity_type' => 'authentication',
                'status' => 1,
                'timestamp' => now(),
            ];
            $log['prev_hash'] = $prevHash;
            $log['row_hash'] = $this->generateRowHash($log, $prevHash);

            DB::table('woundmed_audit_logs')->insert($log);
            
            return response()->json([
                'message' => 'Error on adding Clinician',
                'errors'  => $e->errors()
            ], 422);
        }
    }

    public function getAllClinic(Request $request)
    {
        $perPage = $request->query('per_page', 9);

        $clinics = DB::table('woundmed_clinics')
            // ->where('clinic_status', 0)
            ->whereNull('deleted_at')
            ->paginate($perPage);

        $formattedClinics = $clinics->map(function ($clinic) {
            $clinicians = DB::table('woundmed_clinic_clinician as cc')
                ->join('woundmed_users as u', 'cc.clinician_id', '=', 'u.id')
                ->where('cc.clinic_id', $clinic->clinic_id)
                ->where('u.user_role', 3)
                ->select('u.id', 'u.first_name', 'u.middle_name', 'u.last_name', 'u.email', 'u.phone')
                ->get()
                ->map(function ($u) {
                    $fullname = $u->first_name .
                        ($u->middle_name ? ' ' . $u->middle_name . '. ' : ' ') .
                        $u->last_name;

                    return [
                        'id'    => (string) $u->id,
                        'name'  => $fullname,
                        'email' => $u->email,
                        'phone' => $u->phone,
                    ];
                });

            return [
                'id'            => (string) $clinic->clinic_id,
                'clinicId'      => (string) $clinic->clinic_code,
                'clinicPubId'   => (string) $clinic->clinic_public_id,
                'name'          => $clinic->clinic_name,
                'email'         => $clinic->email,
                'contactPerson' => $clinic->contact_person ?? null,
                'phone'         => $clinic->phone ?? null,
                'address'       => $clinic->address ?? null,
                'isActive'      => $clinic->clinic_status,
                'createdAt'     => $clinic->created_at,
                'updatedAt'     => $clinic->updated_at,
                'clinicians'    => $clinicians,
                'assigned_clinician_ids'    => $clinicians,
            ];
        });

        return response()->json([
            'user_data' => $formattedClinics,
            'meta' => [
                'current_page' => $clinics->currentPage(),
                'last_page'    => $clinics->lastPage(),
                'per_page'     => $clinics->perPage(),
                'total'        => $clinics->total(),
            ]
        ]);
    }

    public function addClinic(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $tempPassword = Str::random(12);
        $prevHash = $this->getLastRowHash();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:woundmed_users,email',
            'contactPerson' => 'required|string',
            'publicId' => 'nullable|string|max:20',
            'isActive' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:20',
            'assigned_clinicians_id' => 'nullable|array',
            'assigned_clinicians_id.*' => 'integer|exists:woundmed_users,id',
        ]);

        $clinic = Clinic::create([
            'clinic_name' => $validated['name'],
            'email' => $validated['email'],
            'clinic_public_id' => $validated['publicId'],
            'contact_person' => $validated['contactPerson'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'clinic_status' => $request->boolean('isActive'),
            'assigned_clinician_ids' => $validated['assigned_clinicians_id'],
            'created_at' => now(),
        ]);

        if (!empty($validated['assigned_clinicians_id'])) {
            $clinic->clinicians()->attach($validated['assigned_clinicians_id']);
        }

        return response()->json([
            'message' => 'Clinic created successfully',
            'data' => $clinic,
        ]);
    }

    public function updateClinic(Request $request, $clinicId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:woundmed_users,email,' . $clinicId . ',clinic_id',
            'contactPerson' => 'required|string',
            'publicId' => 'nullable|string|max:20',
            'isActive' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:20',
            'assigned_clinicians_id' => 'nullable|array',
            'assigned_clinicians_id.*' => 'integer|exists:woundmed_users,id',
        ]);

        $clinic = Clinic::findOrFail($clinicId);

        $clinic->update([
            'clinic_name' => $validated['name'],
            'email' => $validated['email'],
            'clinic_public_id' => $validated['publicId'],
            'contact_person' => $validated['contactPerson'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'clinic_status' => $request->boolean('isActive'),
        ]);

        if (!empty($validated['assigned_clinicians_id'])) {
            $clinic->clinicians()->sync($validated['assigned_clinicians_id']);
        }

        return response()->json([
            'message' => 'Clinic updated successfully',
            'data' => $clinic,
        ]);
    }

    public function updateClinicStatus(Request $request, $clinicId)
    {
        $clinic = Clinic::findOrFail($clinicId);

        if ($clinic->clinic_status == 0) {
            $clinic->clinic_status = 1;
        } elseif ($clinic->clinic_status == 1) {
            $clinic->clinic_status = 0;
        }

        $clinic->save();

        return response()->json([
            'success' => true,
            'status' => $clinic->clinic_status,
            'isActive' => $clinic->clinic_status == 0,
            'label'   => $clinic->clinic_status == 0 ? 'Active' : ($clinic->clinic_status == 1 ? 'Inactive' : 'Archived'),
        ]);

    }

    public function deleteClinic(Request $request, $clinicId)
    {
        $clinic = Clinic::findOrFail($clinicId);
        $clinic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Clinic deleted successfully',
        ]);
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

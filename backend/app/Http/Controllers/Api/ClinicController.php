<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    public function getAllClinicians(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $users = DB::table('woundmed_users')
            ->where('user_role', 3)
            ->orderBy('created_at', 'desc')
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
                'clinicId'  => $user->clinic_id ?? null,
                'manufacturerId'  => $user->manufacturer_id ?? null,
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

    public function getAllClinic(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $clinics = DB::table('woundmed_clinics')
            ->where('clinic_status', 0)
            ->paginate($perPage);

        $formattedClinics = $clinics->map(function ($clinic) {
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
                'createdAt'       => $clinic->created_at,
                'updatedAt'     => $clinic->updated_at,
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

    public function addClinician(Request $request)
    {
        $ip = $request->server('HTTP_X_FORWARDED_FOR') ?? $request->server('REMOTE_ADDR');
        $tempPassword = Str::random(12);

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
                DB::table('woundmed_audit_logs')->insert([
                    'user_id'   => auth()->id(),
                    'ip_address'=> $ip,
                    'action_type'    => 'add_clincian',
                    'action_message' => 'Successful Adding Clinician',
                    'entity_id' => $newClinician->id,
                    'entity'    => 'woundmed_users',
                    'entity_type' => 'clinician',
                    'status'    => 0,
                    'timestamp' => now(),
                ]);

                return response()->json([
                    'message' => 'Clinician Added Successfully'
                ]);
            } else {
                DB::table('woundmed_audit_logs')->insert([
                    'user_id'   => auth()->id(),
                    'ip_address'=> $ip,
                    'attempted_identifier' => $validated['email'],
                    'action_type'    => 'add_clincian',
                    'action_message' => 'Failed Adding Clinician',
                    'entity_id' => $newClinician->id,
                    'entity'    => 'woundmed_users',
                    'entity_type' => 'clinician',
                    'status'    => 1,
                    'timestamp' => now(),
                ]);
                return response()->json([
                    'message' => 'Error on adding Clinician'
                ], 401);
            }
        } catch (ValidationException $e) {
            DB::table('woundmed_audit_logs')->insert([
                'user_id'   => auth()->id(),
                'ip_address'=> $ip,
                'attempted_identifier' => $request['email'],
                'action_type'    => 'add_clincian',
                'action_message' => 'Failed Adding Clinician, Email already taken',
                'entity_id' => null,
                'entity'    => 'woundmed_users',
                'entity_type' => 'clinician',
                'status'    => 1,
                'timestamp' => now(),
            ]);
            return response()->json([
                'message' => 'Error on adding Clinician',
                'errors'  => $e->errors()
            ], 422);
        }
    }

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
}

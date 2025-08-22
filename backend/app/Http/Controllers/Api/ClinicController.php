<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:woundmed_users,email',
            'role' => 'required|string',
            'isActive' => 'boolean',
            'clinicId' => 'nullable|integer',
            'manufacturerId' => 'nullable|integer',
            'phone' => 'nullable|string',
        ]);

        $validated['role'] = $roleMap[$validated['role']] ?? $validated['role'];
        $user = User::create([
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

        if($user){
            DB::table('woundmed_audit_logs')->insert([
                'user_id'   => $user->id,
                'ip_address'=> $ip,
                'action'    => 'logged_out',
                'entity_id' => $user->id,
                'entity'    => 'woundmed_users',
                'timestamp' => now(),
            ]);

            return response()->json([
                'message' => 'Clinician Added Successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'Erro on adding Clinician'
            ], 401);
        }
    }
}

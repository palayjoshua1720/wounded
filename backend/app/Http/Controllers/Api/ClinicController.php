<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicController extends Controller
{
    public function getAllUsers(Request $request)
    {
        $users = DB::table('woundmed_users')
            ->where('user_role', 2)
            ->get();

        $formattedUsers = $users->map(function ($user) {
            $fullname = $user->first_name . 
                ($user->middle_name ? ' ' . $user->middle_name . ' ' : ' ') . 
                $user->last_name;
            return [
                'id'        => (string) $user->id,
                'email'     => $user->email,
                'name'      => $fullname,
                'role'      => 'clinician',
                'clinicId'  => $user->clinic_id ?? null,
                'isActive'  => $user->user_status,
                'lastLogin' => $user->last_logged_in,
                'createdAt' => $user->created_at,
            ];
        });

        return response()->json($formattedUsers);
    }
}

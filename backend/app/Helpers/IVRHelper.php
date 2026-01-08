<?php

namespace App\Helpers;

use App\Models\Brand;
use App\Models\GraftSize;
use App\Models\Clinic;
use App\Models\PatientInfo;
use App\Models\User;
use App\Models\Manufacturer;

class IVRHelper
{
    public static function getBrandName($brandId)
    {
        return Brand::where('brand_id', $brandId)->value('brand_name') ?? 'Unknown Brand';
    }

    public static function getGraftSizeName($graftId)
    {
        return GraftSize::where('graft_size_id', $graftId)->value('size') ?? 'Unknown Size';
    }

    public static function getClinicName($clinicId)
    {
        return Clinic::where('clinic_id', $clinicId)->value('clinic_name') ?? 'Unknown Clinic';
    }

    public static function getPatientName($patientId)
    {
        return PatientInfo::where('patient_id', $patientId)->value('patient_name') ?? 'Unknown Patient';
    }

    public static function getClinicianName($userId)
    {
        $user = User::select('first_name', 'last_name')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return 'Unknown Clinician';
        }

        return trim($user->first_name . ' ' . $user->last_name);
    }

    public static function getManufacturerName($manufacturerId)
    {
        return Manufacturer::where('manufacturer_id', $manufacturerId)->value('manufacturer_name') ?? 'Unknown Manufacturer';
    }
}

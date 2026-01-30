<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IVR extends Model
{
    use SoftDeletes;

    protected $table = 'woundmed_ivr';
    protected $primaryKey = 'ivr_id';
    public $timestamps = true;

    protected $dates = ['deleted_at', 'verified_at', 'submitted_at'];

    protected $fillable = [
        'clinic_id',
        'brand_id',
        'manufacturer_id',
        'patient_id',
        'ivr_file',
        'description',
        'eligibility_status',
        'ivr_status',
        'verified_at',
        'submitted_at',
        'ivr_number',
    ];

    # Relationships
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    // Manufacturer relation
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'manufacturer_id');
    }

    // Brand relation
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    // Patient relation
    public function patient()
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id', 'patient_id');
    }
}

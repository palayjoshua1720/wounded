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

    protected $table = 'woundmend_ivr';
    protected $primaryKey = 'ivr_id';
    public $timestamps = true;

    protected $dates = ['deleted_at', 'verified_at', 'submitted_at'];

    protected $fillable = [
        'clinic_id',
        'brand_id',
        'manufacturer_id',
        'patient_id',
        'description',
        'eligibility_status',
        'ivr_status',
        'verified_at',
        'submitted_at',
    ];
}

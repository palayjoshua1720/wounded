<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_patient_info';
    protected $primaryKey = 'patient_id';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'patient_name',
        'email',
        'clinic_id',
        'updated_by',
    ];

    /**
     * Relationships
     */

    // Patient belongs to a User (clinician) - creator
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Patient was last updated by this User
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    // Patient belongs to a Clinic
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    // Patient may have multiple IVR records
    public function ivrs()
    {
        return $this->hasMany(IVR::class, 'patient_id', 'patient_id');
    }

    public function clinics()
    {
        return $this->hasManyThrough(
            Clinic::class,
            User::class,
            'id',
            'clinic_id',
            'user_id',
            'id'
        );
    }
}

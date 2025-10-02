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
    ];

    /**
     * Relationships
     */

    // Patient belongs to a User (clinician)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Patient may have multiple IVR records
    public function ivrs()
    {
        return $this->hasMany(IVR::class, 'patient_id', 'patient_id');
    }
}

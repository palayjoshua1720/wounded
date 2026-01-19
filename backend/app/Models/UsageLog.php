<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    use HasFactory;

    protected $table = 'woundmed_usage_log';
    protected $primaryKey = 'graft_log_id';

    // Disable automatic created_at and updated_at management
    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'logged_by',
        'graft_size_id',
        'serial_number',
        'date_of_service',
        'expired_at',
        'filepath',
        'wound_part',
        'log_status',
        'description',
        'quantity_used',
        'logged_at',
    ];

    protected $casts = [
        'date_of_service' => 'date',
        'expired_at' => 'date',
        'logged_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the patient associated with this usage log
     */
    public function patient()
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the user (clinician) who logged this usage
     */
    public function clinician()
    {
        return $this->belongsTo(User::class, 'logged_by', 'id');
    }

    /**
     * Get the graft size for this usage log
     */
    public function graftSize()
    {
        return $this->belongsTo(GraftSize::class, 'graft_size_id', 'graft_size_id');
    }
}

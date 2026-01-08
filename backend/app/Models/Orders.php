<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orders extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_orders';
    protected $primaryKey = 'order_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'order_code',
        'order_number',
        'clinic_id',
        'user_id',
        'patient_id',
        'ivr_id',
        'manufacturer_id',
        'tracking_num',
        'tracking_code',
        'items',
        'order_status',
        'notes',
        'ordered_at',
        'followup_last_sent_at',
    ];

    protected $casts = [
        'items' => 'array',
        'ordered_at' => 'datetime',
        'followup_last_sent_at' => 'datetime',
    ];

    # Relationships
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function clinician()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id', 'patient_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function graft()
    {
        return $this->belongsTo(GraftSize::class, 'graft_id', 'graft_size_id');
    }

    public function ivr()
    {
        return $this->belongsTo(IVR::class, 'ivr_id', 'ivr_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'manufacturer_id');
    }
}

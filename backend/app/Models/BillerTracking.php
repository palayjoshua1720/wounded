<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsData;

class BillerTracking extends Model
{
    use EncryptsData;
    
    protected $table = 'woundmed_biller_tracking';
    
    protected $fillable = [
        'patient_name',
        'invoice_number',
        'service_date',
        'submission_date',
        'medicare_amount',
        'provider_amount',
        'status',
        'clinician',
        'notes'
    ];
    
    protected $casts = [
        'service_date' => 'date',
        'submission_date' => 'date',
        'medicare_amount' => 'decimal:2',
        'provider_amount' => 'decimal:2'
    ];
}

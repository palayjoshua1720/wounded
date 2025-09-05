<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $table = 'woundmed_clinics';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'clinic_name',
        'email',
        'clinic_code',
        'clinic_public_code',
        'contact_person',
        'clinic_status',
        'phone',
        'address',
        'assigned_clinician_ids',
        'clinic_status',
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'clinic_status' => 'boolean',
        'assigned_clinician_ids' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($clinic) {
            $today = now()->format('Ymd');

            $latestToday = Clinic::whereDate('created_at', now()->toDateString())
                ->orderBy('clinic_id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($latestToday && preg_match('/-(\d{4})$/', $latestToday->clinic_code, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            }

            $clinic->clinic_code = 'CL-' . $today . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function clinicians()
    {
        return $this->belongsToMany(
            User::class,
            'woundmed_clinic_clinician',
            'clinic_id',
            'clinician_id'
        );
    }  
}

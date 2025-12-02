<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use SoftDeletes;
    protected $table      = 'woundmed_clinics';
    protected $primaryKey = 'clinic_id';
    public $timestamps    = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'clinic_name',
        'email',
        'clinic_code',
        'clinic_public_id',
        'contact_person',
        'clinic_status',
        'phone',
        'address',
        'logo',
    ];

    protected $casts = [
        'isActive'               => 'boolean',
        'clinic_status'          => 'integer',
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

    public function getAllClinicianIdsAttribute()
    {
        $pivotClinicians = $this->clinicians()->pluck('users.id')->toArray();
        $jsonClinicians  = $this->assigned_clinician_ids ?? [];
        return array_unique(array_merge($pivotClinicians, $jsonClinicians));
    }

    // Only get active clinics by default
    public function scopeActive($query)
    {
        return $query->where('clinic_status', 0);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'clinic_id', 'clinic_id');
    }
}

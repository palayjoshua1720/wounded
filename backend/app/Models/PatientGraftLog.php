<?php

// ============================================================================
// PATIENT GRAFT LOG MODULE - MODEL
// ----------------------------------------------------------------------------
// This model powers the standalone Patient Graft Log feature.
// To remove this module, delete this file and rollback the migration.
// ============================================================================

namespace App\Models;

use App\Traits\EncryptsData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientGraftLog extends Model
{
    use HasFactory, SoftDeletes, EncryptsData;

    protected $table = 'woundmed_patient_graft_log';
    protected $primaryKey = 'graft_log_id';
    public $timestamps = true;

    protected $fillable = [
        'graft_log_code',
        'patient_id',
        'ledger_id',
        'serial_number',
        'graft_size_id',
        'brand_id',
        'clinic_id',
        'invoice_id',
        'date_of_service',
        'location',
        'wound_site',
        'wound_number',
        'week_number',
        'clinician_id',
        'notes',
        'logged_by',
    ];

    protected $casts = [
        'date_of_service' => 'date',
        'wound_number'    => 'integer',
        'week_number'     => 'integer',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Auto-generate a unique public-facing code (GRL-XXXXXXXX) when a new
     * log is created and no code was explicitly supplied.
     */
    protected static function booted(): void
    {
        static::creating(function (self $log) {
            if (empty($log->graft_log_code)) {
                $log->graft_log_code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a guaranteed-unique graft_log_code.
     */
    public static function generateUniqueCode(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = 'GRL-';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = self::where('graft_log_code', $code)->exists();
        } while ($exists);

        return $code;
    }

    /**
     * The patient this graft log belongs to.
     */
    public function patient()
    {
        return $this->belongsTo(PatientInfo::class, 'patient_id', 'patient_id');
    }

    /**
     * The clinician who performed the application.
     */
    public function clinician()
    {
        return $this->belongsTo(User::class, 'clinician_id', 'id');
    }

    /**
     * User who logged the entry.
     */
    public function loggedBy()
    {
        return $this->belongsTo(User::class, 'logged_by', 'id');
    }

    /**
     * The graft size catalog row.
     */
    public function graftSize()
    {
        return $this->belongsTo(GraftSize::class, 'graft_size_id', 'graft_size_id');
    }

    /**
     * The brand of the graft used.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    /**
     * The clinic where the application happened.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    /**
     * The invoice associated with the graft.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    /**
     * The inventory ledger row this application consumed.
     */
    public function ledger()
    {
        return $this->belongsTo(InventoryLedger::class, 'ledger_id', 'ledger_id');
    }
}

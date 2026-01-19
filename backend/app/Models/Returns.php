<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;

    protected $table = 'woundmed_returns';
    protected $primaryKey = 'return_id';

    // Custom timestamps - only updated_at is auto-managed
    const CREATED_AT = 'returned_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'graft_log_id',
        'entry_type',
        'brand_id',
        'graft_size_id',
        'reason',
        'other',
        'returned_at',
        'ocr_serial_number',
        'ocr_expiry_date',
        'ocr_product_code',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'updated_at' => 'datetime',
        'ocr_expiry_date' => 'date',
    ];

    /**
     * Get the usage log associated with this return (if linked)
     */
    public function usageLog()
    {
        return $this->belongsTo(UsageLog::class, 'graft_log_id', 'graft_log_id');
    }

    /**
     * Get the brand for this return
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    /**
     * Get the graft size for this return
     */
    public function graftSize()
    {
        return $this->belongsTo(GraftSize::class, 'graft_size_id', 'graft_size_id');
    }
}

<?php

// ============================================================================
// INVENTORY LEDGER MANAGEMENT MODULE - MODEL
// ----------------------------------------------------------------------------
// This model powers the standalone Inventory Ledger Management feature.
// To remove this module, delete this file and rollback the migration.
// ============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLedger extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_inventory_ledger';
    protected $primaryKey = 'ledger_id';
    public $timestamps = true;

    protected $fillable = [
        'serial_number',
        'product_type',
        'product_id',
        'brand_id',
        'clinic_id',
        'order_id',
        'status',
        'is_used',
        'graft_usage_id',
        'invoice_status',
        'invoice_id',
        'notes',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_used' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the brand associated with this ledger entry.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    /**
     * Get the clinic associated with this ledger entry.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    /**
     * Get the invoice associated with this ledger entry.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    /**
     * Get the order associated with this ledger entry.
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    /**
     * Get the graft size product associated with this ledger entry.
     */
    public function graftSizeProduct()
    {
        return $this->belongsTo(GraftSize::class, 'product_id', 'graft_size_id');
    }

    /**
     * Get the other product associated with this ledger entry.
     */
    public function otherProduct()
    {
        return $this->belongsTo(OtherProduct::class, 'product_id', 'other_product_id');
    }

    /**
     * Get the product name based on product_type.
     */
    public function getProductNameAttribute(): string
    {
        if ($this->product_type === 'graft') {
            return 'Graft';
        }

        return $this->otherProduct?->product_name ?? 'Unknown Product';
    }

    /**
     * Get the size display (only applicable for grafts).
     */
    public function getSizeDisplayAttribute(): ?string
    {
        if ($this->product_type === 'graft') {
            return $this->graftSizeProduct?->size ?? null;
        }
        return null;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            0 => 'Expected',
            1 => 'Delivered',
            2 => 'Used',
            3 => 'Partially Used',
            4 => 'Reassigned',
            5 => 'Unused',
            6 => 'Expired',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            0 => 'yellow',
            1 => 'blue',
            2 => 'green',
            3 => 'orange',
            4 => 'purple',
            5 => 'gray',
            6 => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Scope: filter by status.
     */
    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope: filter by brand.
     */
    public function scopeByBrand($query, int $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    /**
     * Scope: filter by invoice status.
     */
    public function scopeByInvoiceStatus($query, string $invoiceStatus)
    {
        return $query->where('invoice_status', $invoiceStatus);
    }

    /**
     * Scope: search by serial number.
     */
    public function scopeSearchSerial($query, string $search)
    {
        return $query->where('serial_number', 'like', "%{$search}%");
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $table = 'woundmed_invoices';

    protected $fillable = [
        'invoice_number',
        'clinic_id',
        'amount',
        'status',
        'invoice_date',
        'due_date',
        'paid_date',
        'order_id',
        'serials',
        'line_items',
        'has_line_items',
        'notes',
        'needs_review',
        'sync_status',
        'partial_payment',
        'paid_amount',
        'payment_method',
        'payment_reference',
        'pdf_path',
        'bill_to',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'paid_date'       => 'date',
        'serials'         => 'array',
        'line_items'      => 'array',
        'has_line_items'  => 'boolean',
        'needs_review'    => 'boolean',
        'partial_payment' => 'boolean',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function serialPayments()
    {
        return $this->hasMany(SerialPayment::class, 'invoice_id');
    }

}

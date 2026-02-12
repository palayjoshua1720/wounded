<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialPayment extends Model
{
    protected $table = 'serial_payments';

    protected $fillable = [
        'invoice_id',
        'serial_number',
        'status',
        'amount',
        'paid_date',
        'payment_method',
        'payment_reference',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'datetime',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
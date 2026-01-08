<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoices extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_invoices';
    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'order_id',
        'uploaded_by',
        'invoice_date',
        'payment_status',
    ];

    /**
     * Relationships
     */

    // Belongs to an order
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    // Belongs to a user (uploaded by)
    public function uploader()
    {
        return $this->belongsTo(Users::class, 'uploaded_by', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherProduct extends Model
{
    use SoftDeletes;

    protected $table = 'woundmed_other_products';
    protected $primaryKey = 'other_product_id';

    protected $fillable = [
        'product_type',
        'product_name',
        'price',
        'stock',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'product_type' => 'integer',
    ];
}

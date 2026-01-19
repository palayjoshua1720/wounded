<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'woundmed_graft_sizes';
    protected $primaryKey = 'graft_size_id';
    public $timestamps = true;
    public $incrementing = true;
    protected $keyType = 'string';
    protected $fillable = [
        'graft_number',
        'brand_id',
        'size',
        'area',
        'price',
        'stock', // Ensure stock is fillable
        'graft_status',
    ];
    protected $casts = [
        'area' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'graft_status' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }
    public function orders()
    {
        return $this->hasMany(Orders::class, 'graft_id', 'graft_size_id');
    }

    public function getManufacturerAttribute()
    {
        return $this->brand->manufacturer;
    }
}

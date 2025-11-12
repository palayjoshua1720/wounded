<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GraftSize extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_graft_sizes';
    protected $primaryKey = 'graft_size_id';
    public $timestamps = true;
    protected $keyType = 'string';

    protected $fillable = [
        'graft_number',
        'clinic_id',
        'brand_id',
        'size',
        'area',
        'price',
        'stock',
        'graft_status',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'graft_status' => 'integer',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }
}

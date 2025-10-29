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
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'brand_id',
        'size',
        'area',
        'price',
        'graft_status',
    ];

    # Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'graft_id', 'graft_size_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_brands';
    protected $primaryKey = 'brand_id';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'manufacturer_id',
        'brand_name',
        'product_type',
        'mue',
        'description',
        'brand_status',
        'filepath',
        'graft_sizes',
        'logo',
    ];

    protected $casts = [
        'mue' => 'integer',
        'product_type' => 'integer',
        'brand_status' => 'integer',
        'graft_sizes' => 'array',
    ];

    # relationship - brand belongs to manufacturer
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'manufacturer_id');
    }

    // Add to your existing Brand class
    public function graftSizes()
    {
        return $this->hasMany(GraftSize::class, 'brand_id', 'brand_id')->orderBy('size');
    }
}

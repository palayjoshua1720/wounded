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
        'product_type',
        'mue',
        'description',
        'brand_status',
    ];
}

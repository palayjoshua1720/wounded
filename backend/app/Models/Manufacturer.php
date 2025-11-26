<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'woundmed_manufacturers';
    protected $primaryKey = 'manufacturer_id';

    protected $fillable = [
        'manufacturer_name',
        'primary_email',
        'secondary_email',
        'website',
        'address',
        'contact_person',
        'contact_number',
        'filepath',
        'manufacturer_status',
        'logo',
    ];

    protected $casts = [
        'manufacturer_status' => 'integer',
    ];

    public function brands()
    {
        return $this->hasMany(Brand::class, 'manufacturer_id', 'manufacturer_id');
    }

    public function ivrs()
    {
        return $this->hasMany(IVR::class, 'manufacturer_id', 'manufacturer_id');
    }
}

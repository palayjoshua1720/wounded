<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manufacturer extends Model
{
    use HasFactory;

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

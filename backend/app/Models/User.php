<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'woundmed_users';

    protected $fillable = [
        'manufacturer_id',
        'clinic_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'user_role',
        'user_status',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_status' => 'integer',
        'user_role' => 'integer',
    ];

    /**
     * Relationship with clinic
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'clinic_id');
    }

    /**
     * Relationship with manufacturer
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'manufacturer_id');
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->user_role === 0;
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->user_status === 0;
    }

    /**
     * Check if user is archived
     */
    public function isArchived()
    {
        return $this->user_status === 2;
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('user_status', 0);
    }

    /**
     * Scope for inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('user_status', 1);
    }

    /**
     * Scope for archived users
     */
    public function scopeArchived($query)
    {
        return $query->where('user_status', 2);
    }
}
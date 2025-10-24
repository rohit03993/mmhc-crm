<?php

namespace App\Models\Core;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'unique_id',
        'address',
        'date_of_birth',
        'qualification',
        'experience',
        'documents',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'documents' => 'array',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get formatted date of birth
     */
    public function getFormattedDateOfBirth()
    {
        return $this->date_of_birth ? $this->date_of_birth->format('M d, Y') : 'Not provided';
    }

    /**
     * Check if user is caregiver
     */
    public function isCaregiver()
    {
        return $this->role === 'caregiver';
    }

    /**
     * Check if user is patient
     */
    public function isPatient()
    {
        return $this->role === 'patient';
    }

    /**
     * Get user's role object
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get user's profile
     */
    public function profile()
    {
        return $this->hasOne(\App\Modules\Profiles\Models\Profile::class);
    }

    /**
     * Get user's documents
     */
    public function documents()
    {
        return $this->hasMany(\App\Modules\Profiles\Models\Document::class);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific role
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}

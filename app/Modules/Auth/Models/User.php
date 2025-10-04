<?php

namespace App\Modules\Auth\Models;

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
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to set unique ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->unique_id)) {
                $user->unique_id = static::generateUniqueId($user->role);
            }
        });
    }

    /**
     * Generate unique ID based on role
     */
    public static function generateUniqueId($role)
    {
        $prefix = match($role) {
            'caregiver' => 'C-UID',
            'patient' => 'P-UID',
            'admin' => 'M-UID',
            default => 'U-UID'
        };

        // Get the highest existing number for this role
        $maxId = static::where('role', $role)
                    ->where('unique_id', 'like', $prefix . '-%')
                    ->selectRaw('CAST(SUBSTRING(unique_id, ' . (strlen($prefix) + 2) . ') AS UNSIGNED) as id_num')
                    ->orderBy('id_num', 'desc')
                    ->first();

        $nextNumber = $maxId ? $maxId->id_num + 1 : 1;
        
        // Ensure uniqueness by checking if the ID already exists
        do {
            $uniqueId = $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = static::where('unique_id', $uniqueId)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);
        
        return $uniqueId;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
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
        return $this->belongsTo(\App\Models\Core\Role::class);
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

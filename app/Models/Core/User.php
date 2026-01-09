<?php

namespace App\Models\Core;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

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
        'plain_password',
        'role',
        'unique_id',
        'address',
        'pincode',
        'latitude',
        'longitude',
        'location', // Spatial POINT column
        'date_of_birth',
        'qualification',
        'experience',
        'documents',
        'is_active',
        'email_verified_at',
        'reward_points',
        'upi_id',
        'qr_code_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'plain_password',
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
        'reward_points' => 'integer',
        'location' => 'array', // Spatial POINT column
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
     * Check if user is nurse (licensed professional)
     */
    public function isNurse()
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if user is caregiver (general support)
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
     * Check if user is staff (nurse or caregiver)
     */
    public function isStaff()
    {
        return in_array($this->role, ['nurse', 'caregiver']);
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
     * Get referrals made by this user (as referrer)
     */
    public function referrals()
    {
        return $this->hasMany(\App\Modules\Referrals\Models\Referral::class, 'referrer_id');
    }

    /**
     * Get referral where this user was referred
     */
    public function referredBy()
    {
        return $this->hasOne(\App\Modules\Referrals\Models\Referral::class, 'referred_id');
    }

    /**
     * Get user's subscriptions
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Modules\Plans\Models\Subscription::class);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        return $this->hasOne(\App\Modules\Plans\Models\Subscription::class)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->latest();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
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

    /**
     * Get calculated reward amount in rupees.
     */
    public function getRewardAmountAttribute(): float
    {
        return (float) ($this->reward_points ?? 0) * \App\Modules\Rewards\Services\RewardService::POINT_VALUE;
    }

    /**
     * Get staff payments received
     */
    public function staffPayments()
    {
        return $this->hasMany(\App\Modules\Payments\Models\StaffPayment::class, 'staff_id');
    }

    /**
     * Get decrypted plain password (admin only - for viewing)
     * Returns null if password cannot be decrypted (old unencrypted records)
     * 
     * Usage: $user->decrypted_password
     */
    public function getDecryptedPasswordAttribute()
    {
        $plainPassword = $this->attributes['plain_password'] ?? null;
        
        if (!$plainPassword) {
            return null;
        }

        try {
            // Check if it's already encrypted (Laravel Crypt produces base64 strings with length > 60)
            // Encrypted values from Laravel's Crypt are typically long base64 strings
            if (strlen($plainPassword) > 60 && base64_decode($plainPassword, true) !== false) {
                // Try to decrypt - if it fails, it might be an old unencrypted password
                return Crypt::decryptString($plainPassword);
            } else {
                // Old unencrypted password - return as is for backward compatibility
                // Migration will encrypt these later
                return $plainPassword;
            }
        } catch (\Exception $e) {
            // If decryption fails, it might be an old unencrypted password
            // Return the original value for backward compatibility
            // Migration will encrypt these later
            \Log::debug('Could not decrypt plain_password for user ' . $this->id . ': ' . $e->getMessage());
            return $plainPassword;
        }
    }

    /**
     * Set encrypted plain password
     * Automatically encrypts when setting plain_password attribute
     */
    public function setPlainPasswordAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['plain_password'] = null;
            return;
        }

        // Only encrypt if it's not already encrypted
        // Check if it looks like an encrypted string (long and contains special chars)
        if (strlen($value) > 60 || str_contains($value, ':')) {
            // Already encrypted, store as is
            $this->attributes['plain_password'] = $value;
        } else {
            // Encrypt the password
            try {
                $this->attributes['plain_password'] = Crypt::encryptString($value);
            } catch (\Exception $e) {
                // If encryption fails, log error but don't break registration
                \Log::error('Failed to encrypt plain_password: ' . $e->getMessage());
                // Store as null to prevent plain text storage
                $this->attributes['plain_password'] = null;
            }
        }
    }
}

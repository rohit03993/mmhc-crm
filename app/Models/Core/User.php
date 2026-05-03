<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'pending_email',
        'phone',
        'pending_phone',
        'contact_update_channel',
        'contact_update_otp_hash',
        'contact_update_otp_expires_at',
        'contact_update_otp_attempts',
        'contact_update_otp_sent_to',
        'contact_update_otp_sent_at',
        'contact_update_verified_at',
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
        'experience_tier',
        'documents',
        'is_active',
        'email_verified_at',
        'reward_points',
        'upi_id',
        'qr_code_path',
        'academic_institution_id',
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
        'contact_update_otp_expires_at' => 'datetime',
        'contact_update_otp_sent_at' => 'datetime',
        'contact_update_verified_at' => 'datetime',
        // Note: 'location' is a spatial POINT column - do NOT cast it
        // Spatial columns must be handled as raw database values
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
     * Check if user has an academic module role (redirect to /academics after login).
     */
    public function hasAcademicRole(): bool
    {
        return in_array($this->role, ['super_admin', 'institution_admin', 'faculty', 'student']);
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
     * Academic institution (for institution_admin, faculty, student)
     */
    public function academicInstitution()
    {
        return $this->belongsTo(\App\Modules\Academics\Models\Institution::class, 'academic_institution_id');
    }

    /**
     * Batches this user is assigned to (as student or faculty)
     */
    public function academicBatches()
    {
        return $this->belongsToMany(\App\Modules\Academics\Models\Batch::class, 'academic_batch_users', 'user_id', 'batch_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    /**
     * Academic assignment submissions (as student)
     */
    public function academicSubmissions()
    {
        return $this->hasMany(\App\Modules\Academics\Models\Submission::class, 'user_id');
    }

    /**
     * Academic attendance records (as student)
     */
    public function academicAttendances()
    {
        return $this->hasMany(\App\Modules\Academics\Models\Attendance::class, 'user_id');
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

        if (! $plainPassword) {
            return null;
        }

        try {
            // Check if it's already encrypted (Laravel Crypt produces base64 strings with length > 60)
            // Encrypted values from Laravel's Crypt are typically long base64 strings
            if (strlen($plainPassword) > 60 && base64_decode($plainPassword, true) !== false) {
                return Crypt::decryptString($plainPassword);
            }

            // Short legacy plaintext (pre-encryption migration), still stored as-is in DB
            return $plainPassword;
        } catch (\Exception $e) {
            // Wrong APP_KEY (e.g. production DB on local), corrupt payload, or algorithm mismatch —
            // never return ciphertext to callers (would leak blob into admin UI).
            \Log::debug('Could not decrypt plain_password for user '.$this->id.': '.$e->getMessage());

            return null;
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
                \Log::error('Failed to encrypt plain_password: '.$e->getMessage());
                // Store as null to prevent plain text storage
                $this->attributes['plain_password'] = null;
            }
        }
    }
}

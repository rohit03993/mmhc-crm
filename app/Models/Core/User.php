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
        'phone_verified_at',
        'phone_verified_source',
        'login_via_phone_only',
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
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'login_via_phone_only' => 'boolean',
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
     * Mobile on the account was confirmed via OTP (profile contact flow).
     */
    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    /**
     * Self-registered accounts (after phone-login rollout) must sign in via SMS OTP, not email/password.
     */
    public function requiresPhoneLogin(): bool
    {
        return (bool) $this->login_via_phone_only;
    }

    public function usesPlaceholderEmail(): bool
    {
        return app(\App\Modules\Auth\Services\UserService::class)->isPlaceholderEmail($this->email);
    }

    /**
     * Email shown in UI — hides internal phone-placeholder addresses.
     */
    public function displayEmail(): ?string
    {
        return $this->usesPlaceholderEmail() ? null : $this->email;
    }

    /**
     * Human-readable mobile for profile UI (+91 XXXXXXXXXX).
     */
    public function displayPhone(): string
    {
        return $this->formatPhoneForDisplay((string) ($this->phone ?? ''));
    }

    /**
     * Human-readable pending mobile (+91 XXXXXXXXXX), or null if none pending.
     */
    public function displayPendingPhone(): ?string
    {
        if (! $this->hasPendingMobileContactVerification()) {
            return null;
        }

        $formatted = $this->formatPhoneForDisplay((string) $this->pending_phone);

        return $formatted === 'Not provided' ? null : $formatted;
    }

    private function formatPhoneForDisplay(string $raw): string
    {
        if ($raw === '') {
            return 'Not provided';
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+91 '.substr($digits, 2);
        }
        if (strlen($digits) === 10) {
            return '+91 '.$digits;
        }

        return $raw;
    }

    /**
     * Nurses/caregivers must verify account mobile (SMS) before earning-related actions.
     */
    public function staffMustVerifyMobileBeforeRewards(): bool
    {
        return $this->isStaff() && ! $this->hasVerifiedPhone();
    }

    /**
     * Profile edit flow: new mobile saved, awaiting SMS OTP before it becomes active.
     */
    public function hasPendingMobileContactVerification(): bool
    {
        return $this->contact_update_channel === 'mobile' && ! empty($this->pending_phone);
    }

    /**
     * Human label for how the account mobile was OTP-verified (admin reporting).
     */
    public function phoneVerificationSourceLabel(): string
    {
        if (! $this->phone_verified_at || ! $this->phone) {
            return '—';
        }

        return match ((string) $this->phone_verified_source) {
            'profile' => 'Profile contact OTP',
            'referral' => 'Staff referral OTP (mobile)',
            'patient_reward' => 'Patient reward OTP (same mobile as account)',
            default => 'Verified (legacy / unknown)',
        };
    }

    public function applyPhoneVerifiedFromProfileContactOtp(): void
    {
        $this->forceFill([
            'phone_verified_at' => now(),
            'phone_verified_source' => 'profile',
        ])->save();
    }

    /**
     * Referred user completed referral verification via SMS OTP.
     */
    public function applyPhoneVerifiedFromReferralMobileOtp(): void
    {
        if ((string) $this->phone_verified_source === 'profile') {
            return;
        }
        $this->forceFill([
            'phone_verified_at' => now(),
            'phone_verified_source' => 'referral',
        ])->save();
    }

    /**
     * Staff verified a patient reward by SMS where patient mobile matches this user's account phone.
     */
    public function applyPhoneVerifiedFromPatientRewardSelfMobileOtp(): void
    {
        if (in_array((string) $this->phone_verified_source, ['profile', 'referral'], true)) {
            return;
        }
        $this->forceFill([
            'phone_verified_at' => now(),
            'phone_verified_source' => 'patient_reward',
        ])->save();
    }

    /**
     * Check if user is staff (nurse or caregiver)
     */
    public function isStaff()
    {
        return in_array($this->role, ['nurse', 'caregiver']);
    }

    /**
     * Roles that use the /academics module (keep in sync with middleware & admin filters).
     *
     * @return list<string>
     */
    public static function academicRoleSlugs(): array
    {
        return ['super_admin', 'institution_admin', 'faculty', 'student'];
    }

    /**
     * Roles never removed by admin "delete all non-admin" bulk action (CRM + academic platform admins).
     *
     * @return list<string>
     */
    public static function protectedFromBulkUserDeletionRoleSlugs(): array
    {
        return ['admin', 'super_admin'];
    }

    /**
     * Check if user has an academic module role (redirect to /academics after login).
     */
    public function hasAcademicRole(): bool
    {
        return in_array($this->role, self::academicRoleSlugs(), true);
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
     * Patient reward submissions (caregiver/nurse patient-detail entries).
     */
    public function caregiverRewards()
    {
        return $this->hasMany(\App\Modules\Rewards\Models\CaregiverReward::class, 'user_id');
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

<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class EnrollmentApplication extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'academic_enrollment_applications';

    protected $fillable = [
        'user_id',
        'institution_id',
        'status',
        'requested_batch_ids',
        'approved_batch_ids',
        'reviewer_id',
        'reviewer_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_batch_ids' => 'array',
        'approved_batch_ids' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}

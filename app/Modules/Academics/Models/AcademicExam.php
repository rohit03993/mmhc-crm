<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicExam extends Model
{
    protected $table = 'academic_exams';

    public const AUDIENCE_SUBJECT_COHORT = 'subject_cohort';

    public const AUDIENCE_BATCH = 'batch';

    public const AUDIENCE_INSTITUTION_OPEN = 'institution_open';

    public const AUDIENCE_COMMUNITY = 'community';

    protected $fillable = [
        'institution_id',
        'created_by',
        'audience_type',
        'subject_id',
        'batch_id',
        'assignment_id',
        'title',
        'instructions',
        'duration_minutes',
        'max_attempts',
        'shuffle_questions',
        'shuffle_options',
        'is_published',
        'published_at',
        'opens_at',
        'closes_at',
        'allows_cross_institution',
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'is_published' => 'boolean',
            'allows_cross_institution' => 'boolean',
            'published_at' => 'datetime',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public static function audienceTypes(): array
    {
        return [
            self::AUDIENCE_SUBJECT_COHORT,
            self::AUDIENCE_BATCH,
            self::AUDIENCE_INSTITUTION_OPEN,
            self::AUDIENCE_COMMUNITY,
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AcademicExamQuestion::class, 'exam_id')->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(AcademicExamAttempt::class, 'exam_id');
    }

    public function isWithinSchedule(): bool
    {
        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }
        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    /** List badge: draft | upcoming | open | ended (for published exams). */
    public function scheduleListState(): string
    {
        if (! $this->is_published) {
            return 'draft';
        }
        $now = now();
        if ($this->opens_at && $now->lt($this->opens_at)) {
            return 'upcoming';
        }
        if ($this->closes_at && $now->gt($this->closes_at)) {
            return 'ended';
        }

        return 'open';
    }
}

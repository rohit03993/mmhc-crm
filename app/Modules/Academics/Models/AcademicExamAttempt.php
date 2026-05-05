<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicExamAttempt extends Model
{
    protected $table = 'academic_exam_attempts';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    protected $fillable = [
        'exam_id',
        'user_id',
        'status',
        'started_at',
        'submitted_at',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(AcademicExam::class, 'exam_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AcademicExamAttemptAnswer::class, 'attempt_id');
    }

    /** Display label when `users.name` is empty (common for draft accounts). */
    public function studentLabel(): string
    {
        $u = $this->relationLoaded('user') ? $this->user : $this->user()->first();
        if (! $u instanceof User) {
            return 'User #'.$this->user_id.' (missing)';
        }
        if (Str::of((string) ($u->name ?? ''))->trim()->isNotEmpty()) {
            return (string) $u->name;
        }
        if (Str::of((string) ($u->email ?? ''))->trim()->isNotEmpty()) {
            return (string) $u->email;
        }

        return 'Student #'.$this->user_id;
    }
}

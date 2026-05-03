<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicExamAttemptAnswer extends Model
{
    protected $table = 'academic_exam_attempt_answers';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
        'selected_option_ids',
    ];

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AcademicExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AcademicExamQuestion::class, 'question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AcademicExamOption::class, 'option_id');
    }
}

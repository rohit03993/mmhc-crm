<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicExamQuestion extends Model
{
    public const TYPE_MCQ_SINGLE = 'mcq_single';

    public const TYPE_MCQ_MULTI = 'mcq_multi';

    protected $table = 'academic_exam_questions';

    protected $fillable = [
        'exam_id',
        'body',
        'question_type',
        'sort_order',
        'points',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(AcademicExam::class, 'exam_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(AcademicExamOption::class, 'question_id')->orderBy('sort_order');
    }
}

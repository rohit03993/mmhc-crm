<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicExamOption extends Model
{
    protected $table = 'academic_exam_options';

    protected $fillable = [
        'question_id',
        'label',
        'body',
        'is_correct',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AcademicExamQuestion::class, 'question_id');
    }
}

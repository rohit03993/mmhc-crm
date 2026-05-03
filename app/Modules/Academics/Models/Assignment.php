<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    public const TYPE_FILE_UPLOAD = 'file_upload';

    public const TYPE_QUIZ = 'quiz';

    public const TYPE_PRESENTATION = 'presentation';

    public const TYPE_CARE_PLAN = 'care_plan';

    public const TYPE_CHECKLIST = 'checklist';

    public const TYPE_SEMINAR_REPORT = 'seminar_report';

    public const TYPE_MIXED = 'mixed';

    public const TYPE_OTHER = 'other';

    protected $table = 'academic_assignments';

    protected $fillable = [
        'topic_id',
        'assignment_type',
        'title',
        'description',
        'assessment_type_keys',
        'is_formative',
        'is_summative',
        'eval_includes_mcq',
        'eval_includes_practical',
        'eval_includes_viva',
        'eval_includes_checklist',
        'due_date',
        'attachments',
        'checklist_items',
    ];

    protected $casts = [
        'due_date' => 'date',
        'attachments' => 'array',
        'checklist_items' => 'array',
        'assessment_type_keys' => 'array',
        'is_formative' => 'boolean',
        'is_summative' => 'boolean',
        'eval_includes_mcq' => 'boolean',
        'eval_includes_practical' => 'boolean',
        'eval_includes_viva' => 'boolean',
        'eval_includes_checklist' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    /** Optional linked quizzes (same institution; typically one per assignment). */
    public function exams(): HasMany
    {
        return $this->hasMany(AcademicExam::class, 'assignment_id');
    }

    /** Students in the batch that has this assignment's subject (eligible to submit). */
    public function eligibleStudentIds(): array
    {
        $batch = $this->topic->subject->batch ?? null;
        if (! $batch) {
            return [];
        }

        return \DB::table('academic_batch_users')
            ->where('batch_id', $batch->id)
            ->where('type', 'student')
            ->pluck('user_id')
            ->toArray();
    }

    public function isPastDue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /** @return list<array{label: string, points: float}> */
    public function normalizedChecklistItems(): array
    {
        $raw = $this->checklist_items ?? [];
        $out = [];
        foreach ($raw as $row) {
            if (is_string($row)) {
                $label = trim($row);
                if ($label !== '') {
                    $out[] = ['label' => $label, 'points' => 1.0];
                }

                continue;
            }
            if (is_array($row)) {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $points = isset($row['points']) ? (float) $row['points'] : 1.0;
                $out[] = ['label' => $label, 'points' => max(0.0, $points)];
            }
        }

        return $out;
    }

    public function hasChecklistForStudents(): bool
    {
        return $this->normalizedChecklistItems() !== [];
    }

    public function studentMustCompleteChecklist(): bool
    {
        if ($this->assignment_type === self::TYPE_CHECKLIST) {
            return true;
        }

        return $this->assignment_type === self::TYPE_MIXED && $this->hasChecklistForStudents();
    }
}

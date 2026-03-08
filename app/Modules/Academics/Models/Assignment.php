<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'academic_assignments';

    protected $fillable = [
        'topic_id',
        'title',
        'description',
        'due_date',
        'attachments',
    ];

    protected $casts = [
        'due_date' => 'date',
        'attachments' => 'array',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }

    /** Students in the batch that has this assignment's subject (eligible to submit). */
    public function eligibleStudentIds(): array
    {
        $batch = $this->topic->subject->batch ?? null;
        if (!$batch) {
            return [];
        }
        return $batch->students()->pluck('users.id')->toArray();
    }

    public function isPastDue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }
}

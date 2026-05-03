<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'academic_submissions';

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'original_name',
        'submitted_at',
        'notes',
        'checklist_answers',
        'checklist_points_earned',
        'checklist_points_possible',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'checklist_answers' => 'array',
        'checklist_points_earned' => 'decimal:2',
        'checklist_points_possible' => 'decimal:2',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isLate(): bool
    {
        $due = $this->assignment->due_date ?? null;

        return $due && $this->submitted_at->isAfter($due->endOfDay());
    }
}

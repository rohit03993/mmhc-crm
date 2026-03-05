<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\User;

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
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
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

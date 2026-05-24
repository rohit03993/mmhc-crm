<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class SubmissionMentorReview extends Model
{
    protected $table = 'academic_submission_mentor_reviews';

    protected $fillable = [
        'submission_id',
        'mentor_id',
        'rating',
        'feedback',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}

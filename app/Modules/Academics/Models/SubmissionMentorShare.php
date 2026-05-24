<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class SubmissionMentorShare extends Model
{
    protected $table = 'academic_submission_mentor_shares';

    protected $fillable = [
        'submission_id',
        'mentor_id',
        'mentorship_id',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentorship()
    {
        return $this->belongsTo(Mentorship::class, 'mentorship_id');
    }
}

<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenClassroomMember extends Model
{
    protected $table = 'academic_open_classroom_members';

    protected $fillable = [
        'open_classroom_id',
        'user_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(OpenClassroom::class, 'open_classroom_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

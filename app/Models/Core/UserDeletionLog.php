<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeletionLog extends Model
{
    protected $fillable = [
        'admin_id',
        'target_user_id',
        'target_role',
        'target_unique_id',
        'original_phone',
        'original_email',
        'stats',
    ];

    protected $casts = [
        'stats' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

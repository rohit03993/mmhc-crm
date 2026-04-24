<?php

namespace App\Modules\Community\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_user_id',
        'actor_user_id',
        'post_id',
        'type',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }
}


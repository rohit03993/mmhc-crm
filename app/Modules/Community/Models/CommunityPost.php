<?php

namespace App\Modules\Community\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_type',
        'is_pinned',
        'is_announcement',
        'pinned_at',
        'content',
        'image_path',
        'event_title',
        'event_date',
        'event_location',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'pinned_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_announcement' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(CommunityComment::class, 'post_id')->latest();
    }

    public function reactions()
    {
        return $this->hasMany(CommunityReaction::class, 'post_id');
    }

    public function eventInterests()
    {
        return $this->hasMany(CommunityEventInterest::class, 'post_id');
    }
}


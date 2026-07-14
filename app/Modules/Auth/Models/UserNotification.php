<?php

namespace App\Modules\Auth\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'action_url',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
    ];

    public const TYPE_BOOKING_CREATED = 'booking_created';

    public const TYPE_BOOKING_CANCELLED = 'booking_cancelled';

    public const TYPE_BOOKING_ACCEPTED = 'booking_accepted';

    public const TYPE_BOOKING_REJECTED = 'booking_rejected';

    public const TYPE_STAFF_ASSIGNED = 'staff_assigned';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}

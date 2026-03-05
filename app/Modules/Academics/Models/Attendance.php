<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'academic_attendance';

    protected $fillable = [
        'batch_id',
        'date',
        'user_id',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LEAVE = 'leave';

    public static function statuses(): array
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LEAVE => 'Leave',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

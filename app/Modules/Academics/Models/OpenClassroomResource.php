<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenClassroomResource extends Model
{
    public const TYPE_FILE = 'file';

    public const TYPE_VIDEO_LINK = 'video_link';

    public const TYPE_NOTE = 'note';

    protected $table = 'academic_open_classroom_resources';

    protected $fillable = [
        'open_classroom_id',
        'created_by',
        'title',
        'description',
        'resource_type',
        'video_url',
        'file_path',
        'sort_order',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(OpenClassroom::class, 'open_classroom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

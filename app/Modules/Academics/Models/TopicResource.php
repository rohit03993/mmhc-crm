<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicResource extends Model
{
    public const TYPE_VIDEO_LINK = 'video_link';

    public const TYPE_FILE = 'file';

    public const TYPE_CHECKLIST = 'checklist';

    protected $table = 'academic_topic_resources';

    protected $fillable = [
        'topic_id',
        'title',
        'description',
        'resource_type',
        'video_url',
        'file_path',
        'sort_order',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }
}

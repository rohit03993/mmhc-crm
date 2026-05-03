<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsceStation extends Model
{
    protected $table = 'academic_osce_stations';

    protected $fillable = [
        'osce_session_id',
        'sort_order',
        'name',
        'instructions',
        'time_limit_seconds',
        'checklist_items',
    ];

    protected $casts = [
        'checklist_items' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(OsceSession::class, 'osce_session_id');
    }
}

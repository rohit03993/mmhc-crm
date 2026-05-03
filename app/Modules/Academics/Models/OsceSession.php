<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsceSession extends Model
{
    protected $table = 'academic_osce_sessions';

    protected $fillable = [
        'institution_id',
        'batch_id',
        'title',
        'description',
        'starts_at',
        'duration_minutes',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stations(): HasMany
    {
        return $this->hasMany(OsceStation::class, 'osce_session_id')->orderBy('sort_order');
    }
}

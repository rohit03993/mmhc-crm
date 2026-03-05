<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Topic extends Model
{
    protected $table = 'academic_topics';

    protected $fillable = [
        'subject_id',
        'name',
        'sort_order',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'topic_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }
}

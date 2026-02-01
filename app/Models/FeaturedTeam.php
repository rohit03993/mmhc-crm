<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FeaturedTeam extends Model
{
    protected $table = 'featured_team';

    protected $fillable = [
        'name',
        'image_path',
        'title',
        'rating',
        'reviews_count',
        'bio',
        'skills',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getSkillsArrayAttribute(): array
    {
        if (empty($this->skills)) {
            return [];
        }
        return array_map('trim', explode(',', $this->skills));
    }
}

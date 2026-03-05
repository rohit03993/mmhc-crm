<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Core\User;

class Subject extends Model
{
    protected $table = 'academic_subjects';

    protected $fillable = [
        'batch_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function faculty()
    {
        return $this->belongsToMany(User::class, 'academic_subject_faculty', 'subject_id', 'user_id')
            ->withTimestamps();
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'subject_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

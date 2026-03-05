<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Core\User;

class Batch extends Model
{
    protected $table = 'academic_batches';

    protected $fillable = [
        'institution_id',
        'name',
        'academic_year',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'academic_batch_users', 'batch_id', 'user_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->users()->wherePivot('type', 'student');
    }

    public function faculty()
    {
        return $this->users()->wherePivot('type', 'faculty');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'batch_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'batch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForInstitution(Builder $query, int $institutionId): Builder
    {
        return $query->where('institution_id', $institutionId);
    }
}

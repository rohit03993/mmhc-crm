<?php

namespace App\Modules\Academics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Institution extends Model
{
    protected $table = 'academic_institutions';

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function batches()
    {
        return $this->hasMany(Batch::class, 'institution_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

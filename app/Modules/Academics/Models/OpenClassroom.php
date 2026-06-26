<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OpenClassroom extends Model
{
    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_UNLISTED = 'unlisted';

    protected $table = 'academic_open_classrooms';

    protected $fillable = [
        'owner_id',
        'title',
        'slug',
        'description',
        'subject_area',
        'visibility',
        'is_active',
        'members_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'members_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (OpenClassroom $classroom) {
            if (empty($classroom->slug)) {
                $classroom->slug = static::uniqueSlug($classroom->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 80, '')) ?: 'classroom';
        $slug = $base;
        $n = 1;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'academic_open_classroom_members', 'open_classroom_id', 'user_id')
            ->withPivot(['joined_at'])
            ->withTimestamps();
    }

    public function memberRows(): HasMany
    {
        return $this->hasMany(OpenClassroomMember::class, 'open_classroom_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(OpenClassroomResource::class, 'open_classroom_id')->orderBy('sort_order')->orderBy('title');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(OpenClassroomAssignment::class, 'open_classroom_id')->orderByDesc('due_date')->orderBy('title');
    }

    public function scopeBrowsable($query)
    {
        return $query->where('is_active', true)->where('visibility', self::VISIBILITY_PUBLIC);
    }

    public function isOwner(User $user): bool
    {
        return (int) $this->owner_id === (int) $user->id;
    }
}

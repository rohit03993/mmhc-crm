<?php

namespace App\Modules\Academics\Models;

use App\Models\Core\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpenClassroomAssignment extends Model
{
    protected $table = 'academic_open_classroom_assignments';

    protected $fillable = [
        'open_classroom_id',
        'created_by',
        'title',
        'description',
        'due_date',
        'attachments',
        'checklist_items',
        'is_published',
    ];

    protected $casts = [
        'due_date' => 'date',
        'attachments' => 'array',
        'checklist_items' => 'array',
        'is_published' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(OpenClassroom::class, 'open_classroom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(OpenClassroomSubmission::class, 'assignment_id');
    }

    public function isPastDue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /** @return list<array{label: string, points: float}> */
    public function normalizedChecklistItems(): array
    {
        $raw = $this->checklist_items ?? [];
        $out = [];
        foreach ($raw as $row) {
            if (is_string($row)) {
                $label = trim($row);
                if ($label !== '') {
                    $out[] = ['label' => $label, 'points' => 1.0];
                }

                continue;
            }
            if (is_array($row)) {
                $label = trim((string) ($row['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $points = isset($row['points']) ? (float) $row['points'] : 1.0;
                $out[] = ['label' => $label, 'points' => max(0.0, $points)];
            }
        }

        return $out;
    }

    public function hasChecklist(): bool
    {
        return $this->normalizedChecklistItems() !== [];
    }

    public function eligibleMemberIds(): array
    {
        return OpenClassroomMember::query()
            ->where('open_classroom_id', $this->open_classroom_id)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

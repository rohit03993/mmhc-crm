<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\OpenClassroom;
use App\Modules\Academics\Models\OpenClassroomMember;
use Illuminate\Support\Facades\DB;

class OpenClassroomService
{
    public function canManage(User $user, OpenClassroom $classroom): bool
    {
        return $classroom->isOwner($user);
    }

    public function canView(User $user, OpenClassroom $classroom): bool
    {
        if (! $classroom->is_active && ! $this->canManage($user, $classroom)) {
            return false;
        }

        if ($this->canManage($user, $classroom)) {
            return true;
        }

        if ($classroom->visibility === OpenClassroom::VISIBILITY_PUBLIC) {
            return true;
        }

        return $this->isMember($user, $classroom);
    }

    public function isMember(User $user, OpenClassroom $classroom): bool
    {
        return OpenClassroomMember::query()
            ->where('open_classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function join(User $user, OpenClassroom $classroom): void
    {
        if ($classroom->isOwner($user)) {
            return;
        }

        if (! in_array($user->role, ['student', 'nurse', 'caregiver'], true)) {
            abort(403, 'Only learners can join open classrooms.');
        }

        if (! $classroom->is_active) {
            abort(403, 'This classroom is not accepting members.');
        }

        if ($this->isMember($user, $classroom)) {
            return;
        }

        DB::transaction(function () use ($user, $classroom) {
            OpenClassroomMember::create([
                'open_classroom_id' => $classroom->id,
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);
            $classroom->increment('members_count');
        });
    }

    public function leave(User $user, OpenClassroom $classroom): void
    {
        if ($classroom->isOwner($user)) {
            abort(403, 'Owners cannot leave their own classroom.');
        }

        $deleted = OpenClassroomMember::query()
            ->where('open_classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            $classroom->decrement('members_count');
        }
    }

    /** @return list<array{label: string, points: float}> */
    public function parseChecklistFromRaw(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $points = 1.0;
            $label = $line;
            if (preg_match('/^(.+?)\s*\|\s*([\d.]+)\s*$/u', $line, $m)) {
                $label = trim($m[1]);
                $points = (float) $m[2];
            }
            if ($label !== '') {
                $out[] = ['label' => $label, 'points' => max(0.0, $points)];
            }
        }

        return $out;
    }
}

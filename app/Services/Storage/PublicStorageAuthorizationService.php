<?php

namespace App\Services\Storage;

use App\Models\Core\User;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\TopicResource;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Profiles\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Authorizes access to files served via /media-file (public disk).
 */
class PublicStorageAuthorizationService
{
    /** Paths safe for guests (landing / marketing CMS). */
    private const PUBLIC_PREFIXES = [
        'testimonials/',
        'featured-team/',
        'achievement-media/',
        'page-content/',
        'site-settings/',
        'pwa-icons/',
        'community/posts/',
    ];

    public function canAccess(?User $user, string $path): bool
    {
        $path = trim($path, '/');
        if ($path === '') {
            return false;
        }

        foreach (self::PUBLIC_PREFIXES as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                return true;
            }
        }

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return match (true) {
            Str::startsWith($path, 'documents/') => $this->canAccessDocument($user, $path),
            Str::startsWith($path, 'avatars/') => $this->canAccessAvatar($user, $path),
            Str::startsWith($path, 'subscriptions/') => $this->canAccessSubscriptionFile($user, $path),
            Str::startsWith($path, 'payment-screenshots/') => false,
            Str::startsWith($path, 'staff-qr-codes/') => $this->canAccessStaffQr($user, $path),
            Str::startsWith($path, 'academic/submissions/') => $this->canAccessAcademicSubmission($user, $path),
            Str::startsWith($path, 'academic/topic-resources/') => $this->canAccessAcademicTopicResource($user, $path),
            default => false,
        };
    }

    private function canAccessDocument(User $user, string $path): bool
    {
        $document = Document::query()->where('file_path', $path)->first();
        if (! $document) {
            return false;
        }

        if ((int) $document->user_id === (int) $user->id) {
            return true;
        }

        $owner = User::find($document->user_id);
        if (! $owner || $owner->role !== 'student') {
            return false;
        }

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return DB::table('academic_batch_users')
                ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
                ->where('academic_batch_users.user_id', $owner->id)
                ->where('academic_batch_users.type', 'student')
                ->where('academic_batches.institution_id', $user->academic_institution_id)
                ->exists();
        }

        if ($user->role === 'faculty') {
            $facultyBatchIds = DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');

            return DB::table('academic_batch_users')
                ->where('user_id', $owner->id)
                ->where('type', 'student')
                ->whereIn('batch_id', $facultyBatchIds)
                ->exists();
        }

        return false;
    }

    /** Profile photos are visible to signed-in CRM users (staff listings, community, etc.). */
    private function canAccessAvatar(User $user, string $path): bool
    {
        if (! preg_match('#^avatars/(\d+)/#', $path, $matches)) {
            return false;
        }

        return User::query()->whereKey((int) $matches[1])->exists();
    }

    private function canAccessSubscriptionFile(User $user, string $path): bool
    {
        $subscription = Subscription::query()
            ->where('payment_screenshot', $path)
            ->first();

        if ($subscription) {
            return (int) $subscription->user_id === (int) $user->id;
        }

        if (preg_match('#^subscriptions/(\d+)_#', $path, $matches)) {
            $subscription = Subscription::find((int) $matches[1]);
            if ($subscription && (int) $subscription->user_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }

    private function canAccessStaffQr(User $user, string $path): bool
    {
        $owner = User::query()->where('qr_code_path', $path)->first();
        if (! $owner) {
            return false;
        }

        return (int) $owner->id === (int) $user->id;
    }

    private function canAccessAcademicSubmission(User $user, string $path): bool
    {
        if (! in_array($user->role, ['student', 'faculty', 'institution_admin', 'admin'], true)) {
            return false;
        }

        $submission = Submission::query()->where('file_path', $path)->first();
        if (! $submission) {
            return false;
        }

        if ((int) $submission->user_id === (int) $user->id) {
            return true;
        }

        if ($user->role === 'faculty') {
            return DB::table('academic_batch_users as faculty')
                ->join('academic_batch_users as students', function ($join) {
                    $join->on('students.batch_id', '=', 'faculty.batch_id')
                        ->where('students.type', '=', 'student');
                })
                ->where('faculty.user_id', $user->id)
                ->where('faculty.type', 'faculty')
                ->where('students.user_id', $submission->user_id)
                ->exists();
        }

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return DB::table('academic_batch_users')
                ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
                ->where('academic_batch_users.user_id', $submission->user_id)
                ->where('academic_batch_users.type', 'student')
                ->where('academic_batches.institution_id', $user->academic_institution_id)
                ->exists();
        }

        return false;
    }

    private function canAccessAcademicTopicResource(User $user, string $path): bool
    {
        if (! in_array($user->role, ['student', 'faculty', 'institution_admin', 'admin'], true)) {
            return false;
        }

        return TopicResource::query()->where('file_path', $path)->exists();
    }
}

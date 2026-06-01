<?php

namespace App\Services\AccountDeletion;

use App\Models\Core\User;
use App\Models\Core\UserDeletionLog;
use App\Modules\Academics\Models\AcademicExam;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Models\EnrollmentApplication;
use App\Modules\Academics\Models\Mentorship;
use App\Modules\Academics\Models\Submission;
use App\Modules\Community\Models\CommunityComment;
use App\Modules\Community\Models\CommunityEventInterest;
use App\Modules\Community\Models\CommunityNotification;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use App\Modules\Incentives\Models\IncentiveLedger;
use App\Modules\Payments\Models\StaffPayment;
use App\Modules\Plans\Models\Payment;
use App\Modules\Plans\Models\Subscription;
use App\Modules\Profiles\Models\Document;
use App\Modules\Profiles\Models\Profile;
use App\Modules\Referrals\Models\Referral;
use App\Modules\Rewards\Models\CaregiverReward;
use App\Modules\Services\Models\DailyService;
use App\Modules\Services\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AccountDeletionService
{
    public function __construct(
        private DeletionPolicy $policy,
        private UserContactRelease $contactRelease,
    ) {}

    public function delete(User $target, User $actor): DeletionResult
    {
        $this->policy->assertDeletable($target, $actor);
        $this->assertDeletionSchemaReady();

        $stats = [];

        try {
            DB::transaction(function () use ($target, $actor, &$stats) {
                $this->snapshotReferralIdentities($target);
                $this->snapshotSubscriptionSubscriber($target);
                $stats = $this->purgeRelatedData($target);
                $this->clearAdminReferences($target);
                $released = $this->contactRelease->tombstoneContacts($target);

                $target->forceFill([
                    'deleted_by_admin_id' => $actor->id,
                ])->save();

                $target->delete();

                if (Schema::hasTable('user_deletion_logs')) {
                    UserDeletionLog::create([
                        'admin_id' => $actor->id,
                        'target_user_id' => $target->id,
                        'target_role' => $target->role,
                        'target_unique_id' => $target->unique_id,
                        'original_phone' => $released['phone'],
                        'original_email' => $released['email'],
                        'stats' => $stats,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Account deletion failed', [
                'target_user_id' => $target->id,
                'admin_id' => $actor->id,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'user' => 'Account could not be deleted: '.$e->getMessage(),
            ]);
        }

        return new DeletionResult(true, 'Account removed successfully.', $stats);
    }

    /**
     * @param  list<int>  $userIds
     */
    public function deleteMany(array $userIds, User $actor, ?int $max = null): BulkDeletionResult
    {
        $max = $max ?? max(User::adminListPerPageOptions());
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $userIds = array_slice($userIds, 0, $max);

        $deleted = 0;
        $skipped = 0;
        $failed = 0;
        $rows = [];

        foreach ($userIds as $userId) {
            $target = User::withTrashed()->find($userId);
            if (! $target) {
                $failed++;
                $rows[] = ['user_id' => $userId, 'name' => '#'.$userId, 'success' => false, 'message' => 'User not found.'];

                continue;
            }

            if (! $this->policy->canSelectForBulkDelete($target, $actor)) {
                $skipped++;
                $rows[] = ['user_id' => $userId, 'name' => $target->name, 'success' => false, 'message' => 'Protected or not deletable.'];

                continue;
            }

            try {
                $displayName = $target->name;
                $this->delete($target, $actor);
                $deleted++;
                $rows[] = ['user_id' => $userId, 'name' => $displayName, 'success' => true, 'message' => 'Removed.'];
            } catch (ValidationException $e) {
                $failed++;
                $rows[] = [
                    'user_id' => $userId,
                    'name' => $target->name,
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first() ?? 'Failed.',
                ];
            }
        }

        return new BulkDeletionResult($deleted, $skipped, $failed, $rows);
    }

    /**
     * @return array<string, int>
     */
    private function purgeRelatedData(User $user): array
    {
        $stats = [];
        $id = $user->id;

        if (Schema::hasTable('sessions')) {
            $stats['sessions'] = DB::table('sessions')->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('community_notifications')) {
            $stats['community_notifications'] = CommunityNotification::query()
                ->where(fn ($q) => $q->where('recipient_user_id', $id)->orWhere('actor_user_id', $id))
                ->delete();
        }

        if (Schema::hasTable('community_posts')) {
            $stats['community_posts'] = CommunityPost::query()->where('user_id', $id)->delete();
        }
        if (Schema::hasTable('community_comments')) {
            $stats['community_comments'] = CommunityComment::query()->where('user_id', $id)->delete();
        }
        if (Schema::hasTable('community_reactions')) {
            $stats['community_reactions'] = CommunityReaction::query()->where('user_id', $id)->delete();
        }
        if (Schema::hasTable('community_event_interests')) {
            $stats['community_event_interests'] = CommunityEventInterest::query()->where('user_id', $id)->delete();
        }

        $stats['files_removed'] = $this->deleteUserFiles($user);

        if (Schema::hasTable('academic_submissions')) {
            Submission::query()->where('user_id', $id)->each(fn (Submission $s) => $this->deleteSubmissionFile($s));
            $stats['academic_submissions'] = Submission::query()->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_attendance')) {
            $stats['academic_attendance'] = Attendance::query()->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_batch_users')) {
            $stats['academic_batch_users'] = DB::table('academic_batch_users')->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_subject_faculty')) {
            $stats['academic_subject_faculty'] = DB::table('academic_subject_faculty')->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_enrollment_applications')) {
            $stats['academic_enrollment_applications'] = EnrollmentApplication::query()->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_mentorships')) {
            $stats['academic_mentorships'] = Mentorship::query()
                ->where(fn ($q) => $q->where('mentee_id', $id)->orWhere('mentor_id', $id))
                ->delete();
        }

        if (Schema::hasTable('academic_exam_attempts')) {
            $stats['academic_exam_attempts'] = AcademicExamAttempt::query()->where('user_id', $id)->delete();
        }

        if (Schema::hasTable('academic_exams') && $user->role === 'faculty') {
            $this->reassignOrRemoveFacultyExams($user);
        }

        $stats['daily_services_staff'] = DailyService::query()->where('staff_id', $id)->delete();

        $patientRequestIds = ServiceRequest::query()->where('patient_id', $id)->pluck('id');
        if ($patientRequestIds->isNotEmpty()) {
            if (Schema::hasTable('incentive_ledger')) {
                IncentiveLedger::query()
                    ->where('source_type', IncentiveLedger::SOURCE_SERVICE_REQUEST)
                    ->whereIn('source_id', $patientRequestIds)
                    ->delete();
            }
            DailyService::query()->whereIn('service_request_id', $patientRequestIds)->delete();
        }
        $stats['service_requests_patient'] = ServiceRequest::query()->where('patient_id', $id)->delete();

        ServiceRequest::query()->where('assigned_staff_id', $id)->update(['assigned_staff_id' => null]);
        ServiceRequest::query()->where('preferred_staff_id', $id)->update(['preferred_staff_id' => null]);

        $stats['caregiver_rewards'] = CaregiverReward::query()->where('user_id', $id)->delete();
        CaregiverReward::query()->where('patient_user_id', $id)->update(['patient_user_id' => null]);

        $stats['incentive_ledger'] = IncentiveLedger::query()->where('staff_id', $id)->delete();
        $stats['staff_payments'] = StaffPayment::query()->where('staff_id', $id)->delete();

        $subscriptionIds = Subscription::query()->where('user_id', $id)->pluck('id');
        if ($subscriptionIds->isNotEmpty()) {
            if (Schema::hasTable('incentive_ledger')) {
                IncentiveLedger::query()
                    ->where('source_type', IncentiveLedger::SOURCE_SUBSCRIPTION_SALE)
                    ->whereIn('source_id', $subscriptionIds)
                    ->delete();
            }
            $stats['subscription_payments'] = Payment::query()->whereIn('subscription_id', $subscriptionIds)->delete();
        }
        $stats['subscriptions'] = Subscription::query()->where('user_id', $id)->delete();

        Subscription::query()->where('referrer_id', $id)->update(['referrer_id' => null]);

        $stats['documents'] = Document::query()->where('user_id', $id)->delete();
        $stats['profiles'] = Profile::query()->where('user_id', $id)->delete();

        return $stats;
    }

    private function assertDeletionSchemaReady(): void
    {
        if (! Schema::hasColumn('users', 'deleted_at')) {
            throw ValidationException::withMessages([
                'user' => 'Database not ready: run php artisan migrate on the server (missing users.deleted_at for account deletion).',
            ]);
        }
    }

    private function snapshotReferralIdentities(User $user): void
    {
        if (! Schema::hasColumn('referrals', 'referrer_name_snapshot')) {
            return;
        }

        Referral::query()->where('referrer_id', $user->id)->each(function (Referral $referral) use ($user) {
            $referral->update([
                'referrer_name_snapshot' => $referral->referrer_name_snapshot ?: $user->name,
                'referrer_unique_id_snapshot' => $referral->referrer_unique_id_snapshot ?: $user->unique_id,
            ]);
        });

        Referral::query()->where('referred_id', $user->id)->each(function (Referral $referral) use ($user) {
            $referral->update([
                'referred_name_snapshot' => $referral->referred_name_snapshot ?: $user->name,
                'referred_unique_id_snapshot' => $referral->referred_unique_id_snapshot ?: $user->unique_id,
            ]);
        });
    }

    private function snapshotSubscriptionSubscriber(User $user): void
    {
        if (! Schema::hasColumn('subscriptions', 'subscriber_name_snapshot')) {
            return;
        }

        Subscription::query()->where('user_id', $user->id)->update([
            'subscriber_name_snapshot' => $user->name,
            'subscriber_unique_id_snapshot' => $user->unique_id,
        ]);
    }

    private function clearAdminReferences(User $user): void
    {
        User::query()->where('phone_verified_by_admin_id', $user->id)->update([
            'phone_verified_by_admin_id' => null,
        ]);
    }

    private function reassignOrRemoveFacultyExams(User $user): void
    {
        $replacementId = User::query()
            ->where('academic_institution_id', $user->academic_institution_id)
            ->where('role', 'institution_admin')
            ->whereNull('deleted_at')
            ->where('id', '!=', $user->id)
            ->value('id');

        if (! $replacementId) {
            $replacementId = User::query()
                ->where('role', 'super_admin')
                ->whereNull('deleted_at')
                ->value('id');
        }

        $examQuery = AcademicExam::query()->where('created_by', $user->id);
        if ($replacementId) {
            $examQuery->update(['created_by' => $replacementId]);
        } else {
            $examQuery->delete();
        }
    }

    private function deleteUserFiles(User $user): int
    {
        $count = 0;

        if ($user->qr_code_path && Storage::disk('public')->exists($user->qr_code_path)) {
            Storage::disk('public')->delete($user->qr_code_path);
            $count++;
        }

        $user->loadMissing(['profile']);

        if ($user->profile?->avatar_path && Storage::disk('public')->exists($user->profile->avatar_path)) {
            Storage::disk('public')->delete($user->profile->avatar_path);
            $count++;
        }

        Document::query()->where('user_id', $user->id)->each(function (Document $document) use (&$count) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
                $count++;
            }
        });

        $legacyDocs = $user->getAttributes()['documents'] ?? null;
        if (is_string($legacyDocs)) {
            $legacyDocs = json_decode($legacyDocs, true);
        }
        if (is_array($legacyDocs)) {
            foreach ($legacyDocs as $doc) {
                $path = is_array($doc) ? ($doc['path'] ?? $doc['file_path'] ?? null) : null;
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                    $count++;
                }
            }
        }

        if (Schema::hasTable('community_posts')) {
            CommunityPost::query()->where('user_id', $user->id)->whereNotNull('image_path')->each(function (CommunityPost $post) use (&$count) {
            if ($post->image_path && Storage::disk('public')->exists($post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
                $count++;
            }
            });
        }

        return $count;
    }

    private function deleteSubmissionFile(Submission $submission): void
    {
        if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
            Storage::disk('public')->delete($submission->file_path);
        }
    }
}

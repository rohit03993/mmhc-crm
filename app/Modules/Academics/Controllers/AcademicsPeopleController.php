<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Services\AcademicScoreService;
use App\Modules\Academics\Services\MentorshipService;
use App\Modules\Academics\Support\AcademicsAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Academic-scoped profile: students redirect to full report; faculty/institution_admin see a summary.
 */
class AcademicsPeopleController extends Controller
{
    public function show(User $user): RedirectResponse|View
    {
        $viewer = auth()->user();
        $this->authorizeAcademicViewer($viewer, $user);

        if ($user->role === 'student') {
            return redirect()->route('academics.reports.student', $user);
        }

        $batches = $user->academicBatches()->with('institution')->get();
        $subjectsTeaching = collect();
        if ($user->role === 'faculty') {
            $subjectIds = DB::table('academic_subject_faculty')
                ->where('user_id', $user->id)
                ->pluck('subject_id');
            $subjectsTeaching = Subject::query()
                ->whereIn('id', $subjectIds)
                ->with('batch.institution')
                ->orderBy('name')
                ->get();
        }

        return view('academics::people.show', [
            'person' => $user,
            'batches' => $batches,
            'subjectsTeaching' => $subjectsTeaching,
            'meiBreakdown' => $user->role === 'faculty' ? AcademicScoreService::getFpiBreakdown($user) : null,
            'mentorCount' => $user->isMenteeEligible() ? app(MentorshipService::class)->activeMentorCountFor($user) : 0,
        ]);
    }

    protected function authorizeAcademicViewer(User $viewer, User $target): void
    {
        if (! in_array($target->role, ['student', 'faculty', 'institution_admin'], true)) {
            abort(404);
        }

        if (AcademicsAccess::isPlatformProvisioner($viewer)) {
            return;
        }

        if (! in_array($viewer->role, ['institution_admin', 'faculty'], true)) {
            abort(403);
        }

        if ((int) ($viewer->academic_institution_id ?? 0) !== (int) ($target->academic_institution_id ?? 0)) {
            abort(403, 'You can only view people from your institution.');
        }

        if ($viewer->role === 'institution_admin') {
            return;
        }

        if ($viewer->role === 'faculty' && $target->role === 'student') {
            $facultyBatchIds = DB::table('academic_batch_users')
                ->where('user_id', $viewer->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            $allowed = DB::table('academic_batch_users')
                ->where('user_id', $target->id)
                ->where('type', 'student')
                ->whereIn('batch_id', $facultyBatchIds)
                ->exists();
            if (! $allowed) {
                abort(403, 'You can only view students in your batches.');
            }
        }
    }
}

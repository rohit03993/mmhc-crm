<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Services\AcademicScoreService;
use Illuminate\Support\Facades\Auth;

class AcademicsDashboardController extends Controller
{
    /**
     * Academics landing page (role-based content later).
     */
    public function index()
    {
        $user = Auth::user();
        $institutionsCount = \App\Modules\Academics\Models\Institution::count();
        $batchesQuery = \App\Modules\Academics\Models\Batch::query();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $batchesQuery->forInstitution((int) $user->academic_institution_id);
        }
        $batchesCount = $batchesQuery->count();
        $subjectsQuery = \App\Modules\Academics\Models\Subject::query();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $subjectsQuery->whereHas('batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id));
        }
        $subjectsCount = $subjectsQuery->count();
        $topicsQuery = \App\Modules\Academics\Models\Topic::query();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $topicsQuery->whereHas('subject.batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id));
        } elseif ($user->role === 'faculty') {
            $topicsQuery->whereHas('subject.faculty', fn ($q) => $q->where('user_id', $user->id));
        }
        $topicsCount = $topicsQuery->count();
        $assignmentsQuery = \App\Modules\Academics\Models\Assignment::query()->whereHas('topic.subject');
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $assignmentsQuery->whereHas('topic.subject.batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id));
        } elseif ($user->role === 'faculty') {
            $assignmentsQuery->whereHas('topic.subject.faculty', fn ($q) => $q->where('user_id', $user->id));
        }
        $assignmentsCount = $assignmentsQuery->count();
        $myAssignmentsCount = 0;
        $myPendingCount = 0;
        $spi = 0;
        $fpi = 0;
        $icr = 0;
        $institutionsWithIcr = collect();

        if ($user->role === 'student') {
            $myAssignmentsQuery = \App\Modules\Academics\Models\Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id));
            $myAssignmentsCount = $myAssignmentsQuery->count();
            $submittedIds = \App\Modules\Academics\Models\Submission::where('user_id', $user->id)->pluck('assignment_id')->toArray();
            $myPendingCount = $myAssignmentsQuery->whereNotIn('id', $submittedIds)->count();
            $spi = AcademicScoreService::getSpi($user);
        } elseif ($user->role === 'faculty') {
            $fpi = AcademicScoreService::getFpi($user);
        } elseif ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institution = \App\Modules\Academics\Models\Institution::find($user->academic_institution_id);
            if ($institution) {
                $icr = AcademicScoreService::getIcr($institution);
            }
        } elseif ($user->role === 'super_admin') {
            $institutionsWithIcr = \App\Modules\Academics\Models\Institution::all()->map(function ($inst) {
                return ['id' => $inst->id, 'name' => $inst->name, 'icr' => AcademicScoreService::getIcr($inst)];
            });
        }

        return view('academics::dashboard', [
            'user' => $user,
            'institutionsCount' => $institutionsCount,
            'batchesCount' => $batchesCount,
            'subjectsCount' => $subjectsCount,
            'topicsCount' => $topicsCount,
            'assignmentsCount' => $assignmentsCount,
            'myAssignmentsCount' => $myAssignmentsCount,
            'myPendingCount' => $myPendingCount,
            'spi' => $spi,
            'fpi' => $fpi,
            'icr' => $icr,
            'institutionsWithIcr' => $institutionsWithIcr,
        ]);
    }
}

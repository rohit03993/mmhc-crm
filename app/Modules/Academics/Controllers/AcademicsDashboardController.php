<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Services\AcademicScoreService;
use App\Modules\Academics\Services\EnrollmentService;
use App\Modules\Academics\Services\MentorshipService;
use App\Modules\Profiles\Models\Document;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicsDashboardController extends Controller
{
    /**
     * Academics dashboard – role-based counts, student numbers, report links.
     */
    public function index()
    {
        $user = Auth::user();
        $institutionsCount = Institution::count();
        $batchesQuery = Batch::query();
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
        $assignmentsQuery = Assignment::query()->whereHas('topic.subject');
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
        $institutionsWithIcrPaginator = null;
        $totalStudentsCount = 0;
        $institutionStudentsCount = 0;
        $myStudentsCount = 0;

        $spiBreakdown = null;
        $meiBreakdown = null;
        $fpiBreakdown = null;
        $mei = 0;

        if ($user->role === 'student') {
            $myAssignmentsQuery = Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id));
            $myAssignmentsCount = $myAssignmentsQuery->count();
            $submittedIds = Submission::where('user_id', $user->id)->pluck('assignment_id')->toArray();
            $myPendingCount = $myAssignmentsQuery->whereNotIn('id', $submittedIds)->count();
            $spiBreakdown = AcademicScoreService::getSpiBreakdown($user);
            $spi = $spiBreakdown['percent'];
        } elseif ($user->role === 'faculty') {
            $fpiBreakdown = AcademicScoreService::getFpiBreakdown($user);
            $fpi = $fpiBreakdown['percent'];
            $meiBreakdown = $fpiBreakdown;
            $mei = $fpiBreakdown['mentorship_percent'];
            $myStudentsCount = $this->facultyStudentsCount($user->id);
        } elseif ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institution = Institution::find($user->academic_institution_id);
            if ($institution) {
                $icr = AcademicScoreService::getIcr($institution);
                $institutionStudentsCount = $this->institutionStudentsCount($institution->id);
            }
        } elseif (in_array($user->role, ['super_admin', 'admin'], true)) {
            $totalStudentsCount = $this->totalStudentsCount();
            $institutionRows = $this->institutionRowsWithStatsForDashboard();
            $instPage = max(1, (int) request()->get('inst_page', 1));
            $perInstPage = 8;
            $institutionsWithIcrPaginator = new LengthAwarePaginator(
                $institutionRows->forPage($instPage, $perInstPage)->values()->all(),
                $institutionRows->count(),
                $perInstPage,
                $instPage,
                ['path' => request()->url(), 'pageName' => 'inst_page']
            );
            $institutionsWithIcrPaginator->withQueryString();
        }

        $insights = $this->dashboardInsights($user);
        $enrollmentPendingCount = 0;
        $mentorCount = 0;
        $menteeCount = 0;

        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $enrollmentPendingCount = app(EnrollmentService::class)->pendingCountForInstitution((int) $user->academic_institution_id);
        } elseif (in_array($user->role, ['super_admin', 'admin'], true)) {
            $enrollmentPendingCount = \App\Modules\Academics\Models\EnrollmentApplication::query()
                ->where('status', 'pending')
                ->count();
        }

        if ($user->isMenteeEligible()) {
            $mentorCount = app(MentorshipService::class)->activeMentorCountFor($user);
        }
        if ($user->role === 'faculty') {
            $menteeCount = app(MentorshipService::class)->activeMenteeCountFor($user);
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
            'institutionsWithIcrPaginator' => $institutionsWithIcrPaginator,
            'totalStudentsCount' => $totalStudentsCount,
            'institutionStudentsCount' => $institutionStudentsCount,
            'myStudentsCount' => $myStudentsCount,
            'insights' => $insights,
            'enrollmentPendingCount' => $enrollmentPendingCount,
            'mentorCount' => $mentorCount,
            'menteeCount' => $menteeCount,
            'spiBreakdown' => $spiBreakdown,
            'meiBreakdown' => $meiBreakdown,
            'fpiBreakdown' => $user->role === 'faculty' ? ($fpiBreakdown ?? null) : null,
            'mei' => $mei,
        ]);
    }

    /**
     * Role-aware lists: profile documents for college roles only; attendance / homework / submissions by role.
     *
     * @return array{show_documents_panel: bool, documents: \Illuminate\Support\Collection, student: ?array, faculty: ?array, institution: ?array}
     */
    protected function dashboardInsights(User $user): array
    {
        $showDocumentsPanel = ! in_array($user->role, ['super_admin', 'admin'], true);

        $out = [
            'show_documents_panel' => $showDocumentsPanel,
            'documents' => collect(),
            'student' => null,
            'faculty' => null,
            'institution' => null,
        ];

        if ($showDocumentsPanel && Schema::hasTable('documents')) {
            $out['documents'] = Document::query()
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->limit(8)
                ->get();
        }

        if ($user->role === 'student' && Schema::hasTable('academic_submissions')) {
            $out['student'] = $this->studentDashboardInsights($user);
        } elseif ($user->role === 'faculty' && Schema::hasTable('academic_submissions')) {
            $out['faculty'] = $this->facultyDashboardInsights($user);
        } elseif ($user->role === 'institution_admin' && $user->academic_institution_id && Schema::hasTable('academic_submissions')) {
            $out['institution'] = $this->institutionDashboardInsights((int) $user->academic_institution_id);
        }

        return $out;
    }

    protected function studentDashboardInsights(User $user): array
    {
        $submittedIds = Submission::query()
            ->where('user_id', $user->id)
            ->pluck('assignment_id');

        $pendingBase = Assignment::query()
            ->whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id))
            ->whereNotIn('id', $submittedIds);

        $homeworkDueCount = (clone $pendingBase)->count();
        $homeworkDue = (clone $pendingBase)
            ->with(['topic.subject'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $recentSubmissions = Submission::query()
            ->where('user_id', $user->id)
            ->with(['assignment.topic.subject'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $recentAttendance = collect();
        if (Schema::hasTable('academic_attendance')) {
            $recentAttendance = Attendance::query()
                ->where('user_id', $user->id)
                ->with('batch')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        return [
            'homeworkDue' => $homeworkDue,
            'homeworkDueCount' => $homeworkDueCount,
            'recentSubmissions' => $recentSubmissions,
            'recentAttendance' => $recentAttendance,
        ];
    }

    protected function facultyDashboardInsights(User $user): array
    {
        $recentStudentSubmissions = Submission::query()
            ->whereHas('assignment', function ($q) use ($user) {
                $q->whereHas('topic.subject.faculty', fn ($f) => $f->where('user_id', $user->id));
            })
            ->with(['user', 'assignment.topic.subject'])
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $recentAssignments = Assignment::query()
            ->whereHas('topic.subject.faculty', fn ($f) => $f->where('user_id', $user->id))
            ->with(['topic.subject'])
            ->latest('id')
            ->limit(6)
            ->get();

        return [
            'recentStudentSubmissions' => $recentStudentSubmissions,
            'recentAssignments' => $recentAssignments,
        ];
    }

    protected function institutionDashboardInsights(int $institutionId): array
    {
        $batchIds = Batch::query()->where('institution_id', $institutionId)->pluck('id');

        $recentAttendance = collect();
        if ($batchIds->isNotEmpty() && Schema::hasTable('academic_attendance')) {
            $recentAttendance = Attendance::query()
                ->whereIn('batch_id', $batchIds)
                ->with(['user', 'batch'])
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        $recentSubmissions = Submission::query()
            ->whereHas('assignment.topic.subject.batch', fn ($b) => $b->where('institution_id', $institutionId))
            ->with(['user', 'assignment.topic.subject'])
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        return [
            'recentAttendance' => $recentAttendance,
            'recentSubmissions' => $recentSubmissions,
        ];
    }

    /**
     * Super/admin institution table: one grouped query for ICR + students (avoids N+1 per college).
     *
     * @return Collection<int, array{id: int, name: string, icr: int, students: int}>
     */
    protected function institutionRowsWithStatsForDashboard(): Collection
    {
        if (! Schema::hasTable('academic_topics')) {
            return Institution::query()->orderBy('name')->get()->map(fn ($inst) => [
                'id' => $inst->id,
                'name' => $inst->name,
                'icr' => 0,
                'students' => $this->institutionStudentsCount($inst->id),
            ]);
        }

        $icrStats = DB::table('academic_topics as t')
            ->join('academic_subjects as s', 's.id', '=', 't.subject_id')
            ->join('academic_batches as b', 'b.id', '=', 's.batch_id')
            ->selectRaw('b.institution_id, COUNT(t.id) as topic_total, SUM(CASE WHEN t.is_completed THEN 1 ELSE 0 END) as topic_done')
            ->groupBy('b.institution_id')
            ->get()
            ->keyBy('institution_id');

        $studentsByInst = DB::table('academic_batch_users as bu')
            ->join('academic_batches as b', 'b.id', '=', 'bu.batch_id')
            ->where('bu.type', 'student')
            ->selectRaw('b.institution_id, COUNT(DISTINCT bu.user_id) as c')
            ->groupBy('b.institution_id')
            ->pluck('c', 'institution_id');

        return Institution::query()->orderBy('name')->get()->map(function ($inst) use ($icrStats, $studentsByInst) {
            $row = $icrStats->get($inst->id);
            $total = $row ? (int) $row->topic_total : 0;
            $done = $row ? (int) $row->topic_done : 0;
            $icr = $total > 0 ? (int) round(($done / $total) * 100) : 0;

            return [
                'id' => $inst->id,
                'name' => $inst->name,
                'icr' => $icr,
                'students' => (int) ($studentsByInst[$inst->id] ?? 0),
            ];
        });
    }

    protected function totalStudentsCount(): int
    {
        return (int) DB::table('academic_batch_users')
            ->where('type', 'student')
            ->selectRaw('COUNT(DISTINCT user_id) as c')
            ->value('c');
    }

    protected function institutionStudentsCount(int $institutionId): int
    {
        $batchIds = Batch::where('institution_id', $institutionId)->pluck('id');
        if ($batchIds->isEmpty()) {
            return 0;
        }

        return (int) DB::table('academic_batch_users')
            ->where('type', 'student')
            ->whereIn('batch_id', $batchIds)
            ->selectRaw('COUNT(DISTINCT user_id) as c')
            ->value('c');
    }

    protected function facultyStudentsCount(int $facultyUserId): int
    {
        $batchIds = DB::table('academic_batch_users')
            ->where('user_id', $facultyUserId)
            ->where('type', 'faculty')
            ->pluck('batch_id');
        if ($batchIds->isEmpty()) {
            return 0;
        }

        return (int) DB::table('academic_batch_users')
            ->where('type', 'student')
            ->whereIn('batch_id', $batchIds)
            ->selectRaw('COUNT(DISTINCT user_id) as c')
            ->value('c');
    }
}

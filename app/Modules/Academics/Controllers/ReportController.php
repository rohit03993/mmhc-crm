<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Services\AcademicScoreService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Full report for one student: institute, batch, attendance (with date period), assignments.
     * Query: period = this_month | last_month | all (default: this_month).
     */
    public function studentReport(Request $request, User $user)
    {
        if ($user->role !== 'student') {
            abort(404, 'Not a student.');
        }
        $currentUser = auth()->user();
        if ($currentUser->role === 'institution_admin' && $currentUser->academic_institution_id) {
            $allowed = \DB::table('academic_batch_users')
                ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
                ->where('academic_batch_users.user_id', $user->id)
                ->where('academic_batch_users.type', 'student')
                ->where('academic_batches.institution_id', $currentUser->academic_institution_id)
                ->exists();
            if (!$allowed) {
                abort(403, 'You can only view students from your institution.');
            }
        } elseif ($currentUser->role === 'faculty') {
            $facultyBatchIds = \DB::table('academic_batch_users')
                ->where('user_id', $currentUser->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            $allowed = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'student')
                ->whereIn('batch_id', $facultyBatchIds)
                ->exists();
            if (!$allowed) {
                abort(403, 'You can only view students in your batches.');
            }
        }

        $period = $request->get('period', 'this_month');
        if (!in_array($period, ['this_month', 'last_month', 'all'], true)) {
            $period = 'this_month';
        }
        $dateRange = $this->attendanceDateRange($period);
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        $totalDays = $start->diffInDays($end) + 1;

        $attendanceQuery = Attendance::where('user_id', $user->id)->with('batch')->orderByDesc('date');
        $attendanceQuery->whereBetween('date', [$dateRange['start'], $dateRange['end']]);
        $attendanceRows = $attendanceQuery->get();

        // One status per day: present if any record present; else leave if any leave; else absent (incl. no record)
        $byDay = $attendanceRows->groupBy(fn ($r) => $r->date->format('Y-m-d'));
        $presentDays = 0;
        $leaveDays = 0;
        for ($i = 0; $i <= $start->diffInDays($end); $i++) {
            $day = $start->copy()->addDays($i)->format('Y-m-d');
            $records = $byDay->get($day, collect());
            if ($records->contains('status', Attendance::STATUS_PRESENT)) {
                $presentDays++;
            } elseif ($records->contains('status', Attendance::STATUS_LEAVE)) {
                $leaveDays++;
            }
        }
        $absentDays = $totalDays - $presentDays - $leaveDays;

        $attendanceStats = [
            'total' => $totalDays,
            'present' => $presentDays,
            'absent' => $absentDays,
            'leave' => $leaveDays,
        ];
        $attendanceStats['percentage'] = $totalDays > 0
            ? (int) round(($presentDays / $totalDays) * 100)
            : 0;

        $batches = $user->academicBatches()->with('institution')->get();
        $institution = $batches->first()?->institution;
        $eligibleAssignments = Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id))
            ->with(['topic.subject.batch'])
            ->orderBy('due_date')
            ->get();
        $submissionsByAssignment = Submission::where('user_id', $user->id)
            ->whereIn('assignment_id', $eligibleAssignments->pluck('id'))
            ->get()
            ->keyBy('assignment_id');
        $spi = AcademicScoreService::getSpi($user);

        $periodLabel = $dateRange['label'];

        return view('academics::reports.student', [
            'student' => $user,
            'institution' => $institution,
            'batches' => $batches,
            'attendanceStats' => $attendanceStats,
            'attendanceRows' => $attendanceRows,
            'eligibleAssignments' => $eligibleAssignments,
            'submissionsByAssignment' => $submissionsByAssignment,
            'spi' => $spi,
            'currentPeriod' => $period,
            'periodLabel' => $periodLabel,
        ]);
    }

    /**
     * Date range for daily attendance. Always returns start/end/label.
     * this_month = 1st to today; last_month = full month; all = 12 months to today.
     *
     * @return array{start: string, end: string, label: string}
     */
    protected function attendanceDateRange(string $period): array
    {
        $now = now();
        if ($period === 'this_month') {
            return [
                'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $now->copy()->format('Y-m-d'),
                'label' => $now->format('F Y') . ' (this month)',
            ];
        }
        if ($period === 'last_month') {
            $last = $now->copy()->subMonth();
            return [
                'start' => $last->startOfMonth()->format('Y-m-d'),
                'end' => $last->endOfMonth()->format('Y-m-d'),
                'label' => $last->format('F Y') . ' (last month)',
            ];
        }
        $end = $now->format('Y-m-d');
        $start = $now->copy()->subYear()->startOfMonth()->format('Y-m-d');
        return [
            'start' => $start,
            'end' => $end,
            'label' => 'Last 12 months',
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $institutions = collect();
        $batches = collect();
        $subjects = collect();
        if ($user->role === 'super_admin') {
            $institutions = Institution::orderBy('name')->get();
            $batches = Batch::with('institution')->orderBy('name')->get();
            $subjects = Subject::with('batch.institution')->orderBy('name')->get();
        } elseif ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institutions = Institution::where('id', $user->academic_institution_id)->get();
            $batches = Batch::with('institution')->forInstitution((int) $user->academic_institution_id)->orderBy('name')->get();
            $subjects = Subject::with('batch.institution')->whereHas('batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id))->orderBy('name')->get();
        } elseif ($user->role === 'faculty') {
            $batchIds = \DB::table('academic_subject_faculty')
                ->join('academic_subjects', 'academic_subjects.id', '=', 'academic_subject_faculty.subject_id')
                ->where('academic_subject_faculty.user_id', $user->id)
                ->pluck('academic_subjects.batch_id')->unique();
            $batches = Batch::with('institution')->whereIn('id', $batchIds)->orderBy('name')->get();
            $institutions = Institution::whereIn('id', $batches->pluck('institution_id'))->orderBy('name')->get();
            $subjects = Subject::with('batch.institution')->whereIn('batch_id', $batchIds)->orderBy('name')->get();
        }
        return view('academics::reports.index', compact('institutions', 'batches', 'subjects'));
    }

    public function show(Request $request)
    {
        $type = $request->get('type', 'batch_progress');
        $institutionId = $request->get('institution_id');
        $batchId = $request->get('batch_id');
        $subjectId = $request->get('subject_id');
        $user = auth()->user();
        $this->applyScope($user, $institutionId, $batchId);
        $this->applySubjectScope($user, $subjectId);
        $this->ensureSubjectBelongsToBatch($batchId, $subjectId);

        $data = $this->buildReportData($type, $institutionId, $batchId, $subjectId, $request, $user);
        $viewData = array_merge($data, ['reportType' => $type]);
        if ($type === 'student_submission') {
            $viewData['reportInstitutions'] = $this->reportFilterInstitutions($user);
            $viewData['reportBatches'] = $this->reportFilterBatches($user);
        }
        return view('academics::reports.show', $viewData);
    }

    public function download(Request $request): StreamedResponse
    {
        $type = $request->get('type', 'batch_progress');
        $institutionId = $request->get('institution_id');
        $batchId = $request->get('batch_id');
        $subjectId = $request->get('subject_id');
        $user = auth()->user();
        $this->applyScope($user, $institutionId, $batchId);
        $this->applySubjectScope($user, $subjectId);
        $this->ensureSubjectBelongsToBatch($batchId, $subjectId);

        $data = $this->buildReportData($type, $institutionId, $batchId, $subjectId, null, $user);
        $filename = 'academics_' . $type . '_' . date('Y-m-d') . '.csv';
        return response()->streamDownload(function () use ($data, $type) {
            $out = fopen('php://output', 'w');
            $this->writeCsv($out, $type, $data);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function applyScope($user, &$institutionId, &$batchId): void
    {
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institutionId = $user->academic_institution_id;
            return;
        }
        if ($user->role === 'faculty') {
            $facultyBatchIds = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id')
                ->all();
            if (empty($facultyBatchIds)) {
                $institutionId = null;
                $batchId = null;
                return;
            }
            $allowedInstitutionIds = Batch::whereIn('id', $facultyBatchIds)->distinct()->pluck('institution_id')->all();
            if ($batchId !== null && $batchId !== '' && !in_array((int) $batchId, array_map('intval', $facultyBatchIds), true)) {
                $batchId = null;
            }
            if ($institutionId !== null && $institutionId !== '' && !in_array((int) $institutionId, array_map('intval', $allowedInstitutionIds), true)) {
                $institutionId = null;
            }
        }
    }

    /** For faculty: ensure subject_id belongs to one of their batches. */
    protected function applySubjectScope($user, &$subjectId): void
    {
        if ($user->role !== 'faculty' || !$subjectId) {
            return;
        }
        $facultyBatchIds = \DB::table('academic_batch_users')
            ->where('user_id', $user->id)
            ->where('type', 'faculty')
            ->pluck('batch_id')
            ->all();
        $subject = Subject::find($subjectId);
        if (!$subject || !in_array((int) $subject->batch_id, array_map('intval', $facultyBatchIds), true)) {
            $subjectId = null;
        }
    }

    /** Institutions to show in Student Submission report filter (scoped by role). */
    protected function reportFilterInstitutions($user): \Illuminate\Support\Collection
    {
        if ($user->role === 'super_admin') {
            return Institution::orderBy('name')->get();
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return Institution::where('id', $user->academic_institution_id)->orderBy('name')->get();
        }
        if ($user->role === 'faculty') {
            $batchIds = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            return Institution::whereIn('id', Batch::whereIn('id', $batchIds)->pluck('institution_id'))->orderBy('name')->get();
        }
        return collect();
    }

    /** Batches to show in Student Submission report filter (scoped by role). */
    protected function reportFilterBatches($user): \Illuminate\Support\Collection
    {
        if ($user->role === 'super_admin') {
            return Batch::with('institution')->orderBy('name')->get();
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return Batch::with('institution')->forInstitution((int) $user->academic_institution_id)->orderBy('name')->get();
        }
        if ($user->role === 'faculty') {
            $batchIds = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            return Batch::with('institution')->whereIn('id', $batchIds)->orderBy('name')->get();
        }
        return collect();
    }

    /** When batch and subject are both set, subject must belong to that batch; otherwise clear subject to avoid wrong data. */
    protected function ensureSubjectBelongsToBatch($batchId, &$subjectId): void
    {
        if (!$batchId || !$subjectId) {
            return;
        }
        $subject = Subject::find($subjectId);
        if (!$subject || (int) $subject->batch_id !== (int) $batchId) {
            $subjectId = null;
        }
    }

    protected function buildReportData(string $type, $institutionId, $batchId, $subjectId = null, ?Request $request = null, $user = null): array
    {
        $user = $user ?? auth()->user();
        $facultyBatchIds = null;
        if ($user && $user->role === 'faculty') {
            $facultyBatchIds = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id')
                ->all();
            if (empty($facultyBatchIds)) {
                return ['title' => 'Report', 'rows' => collect([]), 'headers' => []];
            }
        }

        $baseBatchQuery = Batch::with('institution');
        if ($facultyBatchIds !== null) {
            $baseBatchQuery->whereIn('id', $facultyBatchIds);
        }
        if ($institutionId) {
            $baseBatchQuery->where('institution_id', $institutionId);
        }
        if ($batchId) {
            $baseBatchQuery->where('id', $batchId);
        }
        $batches = $baseBatchQuery->orderBy('name')->get();

        switch ($type) {
            case 'faculty_performance':
                $facultyIdsQuery = \DB::table('academic_subject_faculty')->distinct()->select('user_id');
                if ($facultyBatchIds !== null) {
                    $facultyIdsQuery->join('academic_subjects', 'academic_subjects.id', '=', 'academic_subject_faculty.subject_id')
                        ->whereIn('academic_subjects.batch_id', $facultyBatchIds);
                }
                $facultyIds = $facultyIdsQuery->pluck('user_id');
                $faculty = User::whereIn('id', $facultyIds)->where('role', 'faculty')->orderBy('name')->get();
                $rows = $faculty->map(function ($f) use ($institutionId, $batchId, $facultyBatchIds) {
                    $topicQuery = Topic::whereHas('subject.faculty', fn ($q) => $q->where('user_id', $f->id));
                    if ($facultyBatchIds !== null) {
                        $topicQuery->whereHas('subject', fn ($q) => $q->whereIn('batch_id', $facultyBatchIds));
                    }
                    if ($institutionId) {
                        $topicQuery->whereHas('subject.batch', fn ($q) => $q->where('institution_id', $institutionId));
                    }
                    if ($batchId) {
                        $topicQuery->whereHas('subject', fn ($q) => $q->where('batch_id', $batchId));
                    }
                    $total = $topicQuery->count();
                    $completed = (clone $topicQuery)->where('is_completed', true)->count();
                    $fpi = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
                    return [$f->name, $f->email, $total, $completed, $fpi];
                });
                return ['title' => 'Faculty Performance Report', 'rows' => $rows, 'headers' => ['Faculty', 'Email', 'Topics total', 'Topics completed', 'FPI %']];

            case 'topic_completion':
                $topicQuery = Topic::with('subject.batch.institution');
                if ($facultyBatchIds !== null) {
                    $topicQuery->whereHas('subject', fn ($q) => $q->whereIn('batch_id', $facultyBatchIds));
                }
                if ($institutionId) {
                    $topicQuery->whereHas('subject.batch', fn ($q) => $q->where('institution_id', $institutionId));
                }
                if ($batchId) {
                    $topicQuery->whereHas('subject', fn ($q) => $q->where('batch_id', $batchId));
                }
                $topics = $topicQuery->orderBy('subject_id')->orderBy('sort_order')->get();
                $rows = $topics->map(fn ($t) => [
                    $t->subject->batch->institution->name ?? '—',
                    $t->subject->batch->name ?? '—',
                    $t->subject->name ?? '—',
                    $t->name,
                    $t->is_completed ? 'Completed' : 'Pending',
                ]);
                return ['title' => 'Topic Completion Report', 'rows' => $rows, 'headers' => ['Institution', 'Batch', 'Subject', 'Topic', 'Status']];

            case 'student_submission':
                $studentIdsQuery = \DB::table('academic_batch_users')
                    ->join('academic_batches', 'academic_batches.id', '=', 'academic_batch_users.batch_id')
                    ->where('academic_batch_users.type', 'student')
                    ->distinct()
                    ->select('academic_batch_users.user_id');
                if ($facultyBatchIds !== null) {
                    $studentIdsQuery->whereIn('academic_batches.id', $facultyBatchIds);
                }
                if ($institutionId) {
                    $studentIdsQuery->where('academic_batches.institution_id', $institutionId);
                }
                if ($batchId) {
                    $studentIdsQuery->where('academic_batches.id', $batchId);
                }
                $studentIds = $studentIdsQuery->pluck('user_id');
                $studentsQuery = User::whereIn('id', $studentIds)->where('role', 'student')->orderBy('name');
                $paginator = null;
                if ($request) {
                    $perPage = (int) $request->get('per_page', 25);
                    $perPage = max(1, min(100, $perPage));
                    $students = $studentsQuery->paginate($perPage)->withQueryString();
                    $paginator = $students;
                } else {
                    $students = $studentsQuery->get();
                }
                $rows = [];
                foreach ($students as $s) {
                    $batchQuery = $s->academicBatches()->with('institution');
                    if ($institutionId) {
                        $batchQuery->where('academic_batches.institution_id', $institutionId);
                    }
                    if ($batchId) {
                        $batchQuery->where('academic_batches.id', $batchId);
                    }
                    $studentBatches = $batchQuery->get();
                    $institutionNames = $studentBatches->pluck('institution.name')->unique()->filter()->join(', ') ?: '—';
                    $batchNames = $studentBatches->pluck('name')->join(', ') ?: '—';

                    $eligibleIds = Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $s->id))->pluck('id')->toArray();
                    if ($institutionId) {
                        $eligibleIds = Assignment::whereIn('id', $eligibleIds)->whereHas('topic.subject.batch', fn ($q) => $q->where('institution_id', $institutionId))->pluck('id')->toArray();
                    }
                    if ($batchId) {
                        $eligibleIds = Assignment::whereIn('id', $eligibleIds)->whereHas('topic.subject', fn ($q) => $q->where('batch_id', $batchId))->pluck('id')->toArray();
                    }
                    $submitted = empty($eligibleIds) ? 0 : Submission::where('user_id', $s->id)->whereIn('assignment_id', $eligibleIds)->count();
                    $total = count($eligibleIds);
                    $spi = $total > 0 ? (int) round(($submitted / $total) * 100) : 0;
                    $rows[] = [$s->name, $s->email, $institutionNames, $batchNames, $total, $submitted, $spi, $s->id];
                }
                $result = ['title' => 'Student Submission Report', 'rows' => collect($rows), 'headers' => ['Student', 'Email', 'College', 'Batch(es)', 'Assignments total', 'Submitted', 'SPI %', 'Full report']];
                if ($paginator !== null) {
                    $result['paginator'] = $paginator;
                }
                return $result;

            case 'batch_progress':
            default:
                $rows = $batches->map(function ($b) {
                    $topicQuery = Topic::whereHas('subject', fn ($q) => $q->where('batch_id', $b->id));
                    $total = $topicQuery->count();
                    $completed = (clone $topicQuery)->where('is_completed', true)->count();
                    $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
                    return [
                        $b->institution->name ?? '—',
                        $b->name,
                        $total,
                        $completed,
                        $pct,
                    ];
                });
                return ['title' => 'Batch Progress Report', 'rows' => $rows, 'headers' => ['Institution', 'Batch', 'Topics total', 'Topics completed', 'Progress %']];
        }
    }

    protected function writeCsv($out, string $type, array $data): void
    {
        $headers = $data['headers'] ?? [];
        $rows = $data['rows'] ?? [];
        if ($type === 'student_submission' && !empty($headers)) {
            $headers = array_slice($headers, 0, -1);
        }
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            $arr = is_array($row) ? $row : (array) $row;
            if ($type === 'student_submission' && count($arr) > 5) {
                $arr = array_slice($arr, 0, -1);
            }
            fputcsv($out, $arr);
        }
    }
}

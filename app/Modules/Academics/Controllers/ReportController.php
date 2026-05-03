<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Services\StudentAcademicReportDataService;
use App\Modules\Academics\Support\AcademicsTaxonomy;
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
            if (! $allowed) {
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
            if (! $allowed) {
                abort(403, 'You can only view students in your batches.');
            }
        }

        return view(
            'academics::reports.student',
            app(StudentAcademicReportDataService::class)->build($request, $user)
        );
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $institutions = collect();
        $batches = collect();
        $subjects = collect();
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
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

        $taxonomyFilters = [
            'teaching_methods' => AcademicsTaxonomy::teachingMethods(),
            'assessment_types' => AcademicsTaxonomy::assessmentTypes(),
            'assignment_types' => AcademicsTaxonomy::assignmentTypes(),
        ];

        return view('academics::reports.index', compact('institutions', 'batches', 'subjects', 'taxonomyFilters'));
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

        $data = $this->buildReportData($type, $institutionId, $batchId, $subjectId, $request, $user);
        $filename = 'academics_'.$type.'_'.date('Y-m-d').'.csv';

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
            if ($batchId !== null && $batchId !== '' && ! in_array((int) $batchId, array_map('intval', $facultyBatchIds), true)) {
                $batchId = null;
            }
            if ($institutionId !== null && $institutionId !== '' && ! in_array((int) $institutionId, array_map('intval', $allowedInstitutionIds), true)) {
                $institutionId = null;
            }
        }
    }

    /** For faculty: ensure subject_id belongs to one of their batches. */
    protected function applySubjectScope($user, &$subjectId): void
    {
        if ($user->role !== 'faculty' || ! $subjectId) {
            return;
        }
        $facultyBatchIds = \DB::table('academic_batch_users')
            ->where('user_id', $user->id)
            ->where('type', 'faculty')
            ->pluck('batch_id')
            ->all();
        $subject = Subject::find($subjectId);
        if (! $subject || ! in_array((int) $subject->batch_id, array_map('intval', $facultyBatchIds), true)) {
            $subjectId = null;
        }
    }

    /** Institutions to show in Student Submission report filter (scoped by role). */
    protected function reportFilterInstitutions($user): \Illuminate\Support\Collection
    {
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
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
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
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
        if (! $batchId || ! $subjectId) {
            return;
        }
        $subject = Subject::find($subjectId);
        if (! $subject || (int) $subject->batch_id !== (int) $batchId) {
            $subjectId = null;
        }
    }

    /**
     * @return array{teaching_method_key: ?string, assignment_type: ?string, assessment_type_key: ?string, summative_only: bool}
     */
    protected function normalizeReportTaxonomyRequest(?Request $request): array
    {
        $tm = $request?->get('teaching_method_key');
        if ($tm !== null && $tm !== '' && ! array_key_exists($tm, AcademicsTaxonomy::teachingMethods())) {
            $tm = null;
        }
        $at = $request?->get('assignment_type');
        if ($at !== null && $at !== '' && ! array_key_exists($at, AcademicsTaxonomy::assignmentTypes())) {
            $at = null;
        }
        $ask = $request?->get('assessment_type_key');
        if ($ask !== null && $ask !== '' && ! array_key_exists($ask, AcademicsTaxonomy::assessmentTypes())) {
            $ask = null;
        }

        return [
            'teaching_method_key' => $tm !== null && $tm !== '' ? $tm : null,
            'assignment_type' => $at !== null && $at !== '' ? $at : null,
            'assessment_type_key' => $ask !== null && $ask !== '' ? $ask : null,
            'summative_only' => (bool) $request?->boolean('summative_only'),
        ];
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

        $tax = $this->normalizeReportTaxonomyRequest($request);

        switch ($type) {
            case 'faculty_performance':
                $facultyIdsQuery = \DB::table('academic_subject_faculty')->distinct()->select('user_id');
                if ($facultyBatchIds !== null) {
                    $facultyIdsQuery->join('academic_subjects', 'academic_subjects.id', '=', 'academic_subject_faculty.subject_id')
                        ->whereIn('academic_subjects.batch_id', $facultyBatchIds);
                }
                $facultyIds = $facultyIdsQuery->pluck('user_id');
                $faculty = User::whereIn('id', $facultyIds)->where('role', 'faculty')->orderBy('name')->get();
                $rows = $faculty->map(function ($f) use ($institutionId, $batchId, $facultyBatchIds, $tax) {
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
                    if ($tax['teaching_method_key']) {
                        $topicQuery->whereJsonContains('teaching_method_keys', $tax['teaching_method_key']);
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
                if ($tax['teaching_method_key']) {
                    $topicQuery->whereJsonContains('teaching_method_keys', $tax['teaching_method_key']);
                }
                $topics = $topicQuery->orderBy('subject_id')->orderBy('sort_order')->get();
                $rows = $topics->map(fn ($t) => [
                    $t->subject->batch->institution->name ?? '—',
                    $t->subject->batch->name ?? '—',
                    $t->subject->name ?? '—',
                    $t->name,
                    AcademicsTaxonomy::teachingMethodLabels($t->teaching_method_keys),
                    $t->is_completed ? 'Completed' : 'Pending',
                ]);

                return ['title' => 'Topic Completion Report', 'rows' => $rows, 'headers' => ['Institution', 'Batch', 'Subject', 'Topic', 'Teaching methods', 'Status']];

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
                    $perPage = (int) $request->get('per_page', 10);
                    $perPage = max(1, min(10, $perPage));
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
                    $assignFilter = Assignment::query()->whereIn('id', $eligibleIds);
                    if ($tax['assignment_type']) {
                        $assignFilter->where('assignment_type', $tax['assignment_type']);
                    }
                    if ($tax['assessment_type_key']) {
                        $assignFilter->whereJsonContains('assessment_type_keys', $tax['assessment_type_key']);
                    }
                    if ($tax['summative_only']) {
                        $assignFilter->where('is_summative', true);
                    }
                    $eligibleIds = $assignFilter->pluck('id')->toArray();
                    $assignmentsForStudent = Assignment::with('exams')->whereIn('id', $eligibleIds)->get();
                    $total = $assignmentsForStudent->count();
                    $submitted = 0;
                    foreach ($assignmentsForStudent as $a) {
                        if (Submission::where('user_id', $s->id)->where('assignment_id', $a->id)->exists()) {
                            $submitted++;

                            continue;
                        }
                        if ($a->assignment_type === Assignment::TYPE_QUIZ && $a->exams->isNotEmpty()) {
                            $eids = $a->exams->pluck('id');
                            if (AcademicExamAttempt::whereIn('exam_id', $eids)
                                ->where('user_id', $s->id)
                                ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
                                ->exists()) {
                                $submitted++;
                            }
                        }
                    }
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
        if ($type === 'student_submission' && ! empty($headers)) {
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

<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Services\AcademicScoreService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin,institution_admin,faculty');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $institutions = collect();
        $batches = collect();
        if ($user->role === 'super_admin') {
            $institutions = Institution::orderBy('name')->get();
            $batches = Batch::with('institution')->orderBy('name')->get();
        } elseif ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institutions = Institution::where('id', $user->academic_institution_id)->get();
            $batches = Batch::with('institution')->forInstitution((int) $user->academic_institution_id)->orderBy('name')->get();
        } elseif ($user->role === 'faculty') {
            $batchIds = \DB::table('academic_subject_faculty')
                ->join('academic_subjects', 'academic_subjects.id', '=', 'academic_subject_faculty.subject_id')
                ->where('academic_subject_faculty.user_id', $user->id)
                ->pluck('academic_subjects.batch_id')->unique();
            $batches = Batch::with('institution')->whereIn('id', $batchIds)->orderBy('name')->get();
            $institutions = Institution::whereIn('id', $batches->pluck('institution_id'))->orderBy('name')->get();
        }
        return view('academics::reports.index', compact('institutions', 'batches'));
    }

    public function show(Request $request)
    {
        $type = $request->get('type', 'batch_progress');
        $institutionId = $request->get('institution_id');
        $batchId = $request->get('batch_id');
        $user = auth()->user();
        $this->applyScope($user, $institutionId, $batchId);

        $data = $this->buildReportData($type, $institutionId, $batchId);
        return view('academics::reports.show', array_merge($data, ['reportType' => $type]));
    }

    public function download(Request $request): StreamedResponse
    {
        $type = $request->get('type', 'batch_progress');
        $institutionId = $request->get('institution_id');
        $batchId = $request->get('batch_id');
        $user = auth()->user();
        $this->applyScope($user, $institutionId, $batchId);

        $data = $this->buildReportData($type, $institutionId, $batchId);
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
        }
    }

    protected function buildReportData(string $type, $institutionId, $batchId): array
    {
        $baseBatchQuery = Batch::with('institution');
        if ($institutionId) {
            $baseBatchQuery->where('institution_id', $institutionId);
        }
        if ($batchId) {
            $baseBatchQuery->where('id', $batchId);
        }
        $batches = $baseBatchQuery->orderBy('name')->get();

        switch ($type) {
            case 'faculty_performance':
                $facultyIds = \DB::table('academic_subject_faculty')->distinct()->pluck('user_id');
                $faculty = User::whereIn('id', $facultyIds)->where('role', 'faculty')->orderBy('name')->get();
                $rows = $faculty->map(function ($f) use ($institutionId, $batchId) {
                    $topicQuery = Topic::whereHas('subject.faculty', fn ($q) => $q->where('user_id', $f->id));
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
                $studentIds = \DB::table('academic_batch_users')->where('type', 'student')->distinct()->pluck('user_id');
                $students = User::whereIn('id', $studentIds)->where('role', 'student')->orderBy('name')->get();
                $rows = [];
                foreach ($students as $s) {
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
                    $rows[] = [$s->name, $s->email, $total, $submitted, $spi];
                }
                return ['title' => 'Student Submission Report', 'rows' => collect($rows), 'headers' => ['Student', 'Email', 'Assignments total', 'Submitted', 'SPI %']];

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
        fputcsv($out, $headers);
        foreach ($data['rows'] ?? [] as $row) {
            fputcsv($out, is_array($row) ? $row : (array) $row);
        }
    }
}

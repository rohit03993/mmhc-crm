<?php

namespace App\Modules\Academics\Services;

use App\Models\Core\User;
use App\Modules\Academics\Models\AcademicExamAttempt;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Models\Submission;
use App\Modules\Profiles\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentAcademicReportDataService
{
    /**
     * Dataset for the full student report (documents, SPI, attendance ledger, assignments).
     * Caller must enforce authorization and student role.
     *
     * @return array<string, mixed>
     */
    public function build(Request $request, User $user): array
    {
        $period = $request->get('period', 'this_month');
        if (! in_array($period, ['this_month', 'last_month', 'all'], true)) {
            $period = 'this_month';
        }
        $dateRange = $this->attendanceDateRange($period);
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        $totalDays = $start->diffInDays($end) + 1;

        $allAttendanceInPeriod = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->with('batch')
            ->get();

        $byDay = $allAttendanceInPeriod->groupBy(fn ($r) => $r->date->format('Y-m-d'));
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

        $assignmentsPaginator = Assignment::whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $user->id))
            ->with(['topic.subject.batch'])
            ->orderBy('due_date')
            ->paginate(10, ['*'], 'apage')
            ->withQueryString();

        $submissionsByAssignment = Submission::where('user_id', $user->id)
            ->whereIn('assignment_id', $assignmentsPaginator->pluck('id'))
            ->get()
            ->keyBy('assignment_id');

        $attendanceLedger = [];
        $daySpan = $start->diffInDays($end);
        for ($offset = 0; $offset <= $daySpan; $offset++) {
            $d = $end->copy()->subDays($offset);
            $dayKey = $d->format('Y-m-d');
            $records = $byDay->get($dayKey, collect());
            if ($records->contains('status', Attendance::STATUS_PRESENT)) {
                $status = Attendance::STATUS_PRESENT;
            } elseif ($records->contains('status', Attendance::STATUS_LEAVE)) {
                $status = Attendance::STATUS_LEAVE;
            } else {
                $status = Attendance::STATUS_ABSENT;
            }
            $batchLabel = $records->isEmpty()
                ? '—'
                : $records->map(fn ($r) => $r->batch->name ?? '—')->unique()->filter()->implode(', ');
            $attendanceLedger[] = [
                'date' => $d->copy()->startOfDay(),
                'status' => $status,
                'batch_label' => $batchLabel ?: '—',
                'inferred' => $records->isEmpty(),
            ];
        }

        $attPerPage = 14;
        $attPage = max(1, (int) $request->get('attpage', 1));
        $attendanceLedgerPaginator = new LengthAwarePaginator(
            array_slice($attendanceLedger, ($attPage - 1) * $attPerPage, $attPerPage),
            count($attendanceLedger),
            $attPerPage,
            $attPage,
            [
                'path' => $request->url(),
                'pageName' => 'attpage',
            ]
        );
        $attendanceLedgerPaginator->withQueryString();

        $documentsPaginator = Document::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'docpage')
            ->withQueryString();

        $spi = AcademicScoreService::getSpi($user);
        $periodLabel = $dateRange['label'];

        $quizAttemptsPaginator = AcademicExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', AcademicExamAttempt::STATUS_SUBMITTED)
            ->with(['exam' => fn ($q) => $q->select('id', 'title')->with('questions:id,exam_id,points')])
            ->orderByDesc('submitted_at')
            ->paginate(8, ['*'], 'expage')
            ->withQueryString();

        return [
            'student' => $user,
            'institution' => $institution,
            'batches' => $batches,
            'attendanceStats' => $attendanceStats,
            'attendanceLedgerPaginator' => $attendanceLedgerPaginator,
            'assignmentsPaginator' => $assignmentsPaginator,
            'documentsPaginator' => $documentsPaginator,
            'quizAttemptsPaginator' => $quizAttemptsPaginator,
            'submissionsByAssignment' => $submissionsByAssignment,
            'spi' => $spi,
            'currentPeriod' => $period,
            'periodLabel' => $periodLabel,
        ];
    }

    /**
     * @return array{start: string, end: string, label: string}
     */
    public function attendanceDateRange(string $period): array
    {
        $now = now();
        if ($period === 'this_month') {
            return [
                'start' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'end' => $now->copy()->format('Y-m-d'),
                'label' => $now->format('F Y').' (this month)',
            ];
        }
        if ($period === 'last_month') {
            $last = $now->copy()->subMonth();

            return [
                'start' => $last->startOfMonth()->format('Y-m-d'),
                'end' => $last->endOfMonth()->format('Y-m-d'),
                'label' => $last->format('F Y').' (last month)',
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
}

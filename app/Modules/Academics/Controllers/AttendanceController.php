<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Models\Batch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /** Batches the current user can mark attendance for (institution_admin: their inst; faculty: assigned batches). */
    protected function scopeBatches()
    {
        $user = auth()->user();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return Batch::with('institution')->forInstitution((int) $user->academic_institution_id)->active()->orderBy('name');
        }
        if ($user->role === 'faculty') {
            $batchIds = \DB::table('academic_batch_users')
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->pluck('batch_id');
            return Batch::with('institution')->whereIn('id', $batchIds)->active()->orderBy('name');
        }
        return Batch::with('institution')->whereRaw('1 = 0');
    }

    /** Check batch is within scope for marking attendance. */
    protected function authorizeBatch(Batch $batch): void
    {
        $user = auth()->user();
        if ($user->role === 'institution_admin' && (int) $user->academic_institution_id === (int) $batch->institution_id) {
            return;
        }
        if ($user->role === 'faculty') {
            $assigned = \DB::table('academic_batch_users')
                ->where('batch_id', $batch->id)
                ->where('user_id', $user->id)
                ->where('type', 'faculty')
                ->exists();
            if ($assigned) {
                return;
            }
        }
        abort(403, 'You cannot mark attendance for this batch.');
    }

    /** Select batch + date to mark attendance (faculty / admin). */
    public function index(Request $request)
    {
        $batches = $this->scopeBatches()->get();
        $batchId = $request->get('batch_id');
        $date = $request->get('date');
        $batch = null;
        if ($batchId && $date) {
            $batch = Batch::find($batchId);
            if ($batch) {
                $this->authorizeBatch($batch);
            }
        }
        return view('academics::attendance.index', compact('batches', 'batch', 'batchId', 'date'));
    }

    /** Form to mark attendance for a batch on a date. */
    public function mark(Request $request)
    {
        $batchId = $request->validate(['batch_id' => 'required|exists:academic_batches,id', 'date' => 'required|date'])['batch_id'];
        $date = $request->get('date');
        $batch = Batch::with('students')->findOrFail($batchId);
        $this->authorizeBatch($batch);
        $students = $batch->students()->orderBy('name')->get();
        $existing = Attendance::where('batch_id', $batch->id)->where('date', $date)->get()->keyBy('user_id');
        return view('academics::attendance.mark', [
            'batch' => $batch,
            'date' => $date,
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    /** Save attendance for batch + date. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:academic_batches,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent,leave',
        ]);
        $batch = Batch::findOrFail($validated['batch_id']);
        $this->authorizeBatch($batch);
        $date = $validated['date'];
        $studentIds = \DB::table('academic_batch_users')
            ->where('batch_id', $batch->id)
            ->where('type', 'student')
            ->pluck('user_id')
            ->toArray();
        Attendance::where('batch_id', $batch->id)->where('date', $date)->delete();
        foreach ($validated['attendance'] as $userId => $status) {
            if (! in_array((int) $userId, $studentIds, true)) {
                continue;
            }
            Attendance::create([
                'batch_id' => $batch->id,
                'date' => $date,
                'user_id' => $userId,
                'status' => $status,
            ]);
        }
        return redirect()
            ->route('academics.attendance.index')
            ->with('success', 'Attendance saved for ' . $date . '.');
    }

    /** Student: view my attendance. Daily-wise stats for selected period. */
    public function myAttendance(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'this_month');
        if (! in_array($period, ['this_month', 'last_month', 'all'], true)) {
            $period = 'this_month';
        }
        $dateRange = $this->attendanceDateRange($period);
        $start = Carbon::parse($dateRange['start']);
        $end = Carbon::parse($dateRange['end']);
        $totalDays = $start->diffInDays($end) + 1;

        $attendanceQuery = Attendance::where('user_id', $user->id)
            ->with('batch')
            ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->orderByDesc('date');
        $attendanceRows = $attendanceQuery->get();
        $page = (int) $request->get('page', 1);
        $perPage = 20;
        $attendances = new \Illuminate\Pagination\LengthAwarePaginator(
            $attendanceRows->forPage($page, $perPage)->values(),
            $attendanceRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

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
        $stats = [
            'total' => $totalDays,
            'present' => $presentDays,
            'absent' => $absentDays,
            'leave' => $leaveDays,
            'percentage' => $totalDays > 0 ? (int) round(($presentDays / $totalDays) * 100) : 0,
        ];

        return view('academics::attendance.my', [
            'attendances' => $attendances,
            'stats' => $stats,
            'currentPeriod' => $period,
            'periodLabel' => $dateRange['label'],
        ]);
    }

    /** @return array{start: string, end: string, label: string} */
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
        return ['start' => $start, 'end' => $end, 'label' => 'Last 12 months'];
    }
}

<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Attendance;
use App\Modules\Academics\Models\Batch;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /** Batches the current user can mark attendance for (super_admin: all; institution_admin: their inst; faculty: assigned batches). */
    protected function scopeBatches()
    {
        $user = auth()->user();
        if ($user->role === 'super_admin') {
            return Batch::with('institution')->active()->orderBy('name');
        }
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
        if ($user->role === 'super_admin') {
            return;
        }
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
        $studentIds = $batch->students()->pluck('id')->toArray();
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

    /** Student: view my attendance. */
    public function myAttendance()
    {
        $user = auth()->user();
        $attendances = Attendance::where('user_id', $user->id)
            ->with('batch')
            ->orderByDesc('date')
            ->paginate(20);
        $stats = $this->studentAttendanceStats($user->id);
        return view('academics::attendance.my', compact('attendances', 'stats'));
    }

    protected function studentAttendanceStats(int $userId): array
    {
        $rows = Attendance::where('user_id', $userId)->get();
        $total = $rows->count();
        $present = $rows->where('status', Attendance::STATUS_PRESENT)->count();
        $absent = $rows->where('status', Attendance::STATUS_ABSENT)->count();
        $leave = $rows->where('status', Attendance::STATUS_LEAVE)->count();
        $pct = $total > 0 ? (int) round(($present / $total) * 100) : 0;
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'percentage' => $pct,
        ];
    }
}

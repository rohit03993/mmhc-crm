<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Models\OsceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OsceSessionController extends Controller
{
    /** @return list<int>|null null = all institutions */
    protected function scopeInstitutionIdsForUser($user): ?array
    {
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return null;
        }
        if (in_array($user->role, ['institution_admin', 'faculty'], true) && $user->academic_institution_id) {
            return [(int) $user->academic_institution_id];
        }

        return [];
    }

    /** @return list<int> */
    protected function facultyBatchIds(int $userId): array
    {
        return DB::table('academic_subject_faculty')
            ->join('academic_subjects', 'academic_subjects.id', '=', 'academic_subject_faculty.subject_id')
            ->where('academic_subject_faculty.user_id', $userId)
            ->pluck('academic_subjects.batch_id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function baseSessionQuery()
    {
        return OsceSession::with(['institution', 'batch', 'stations'])->orderByDesc('starts_at')->orderByDesc('id');
    }

    public function index()
    {
        $user = auth()->user();
        $query = $this->baseSessionQuery();
        $instIds = $this->scopeInstitutionIdsForUser($user);
        if (is_array($instIds)) {
            if ($instIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('institution_id', $instIds);
            }
        }
        if ($user->role === 'faculty') {
            $batchIds = $this->facultyBatchIds($user->id);
            $query->where(function ($q) use ($batchIds, $user) {
                $q->whereIn('batch_id', $batchIds);
                if ($user->academic_institution_id) {
                    $q->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('batch_id')->where('institution_id', $user->academic_institution_id);
                    });
                }
            });
        }
        $sessions = $query->limit(200)->get();

        return view('academics::osce.index', compact('sessions'));
    }

    public function studentIndex()
    {
        $user = auth()->user();
        $batchIds = DB::table('academic_batch_users')
            ->where('user_id', $user->id)
            ->where('type', 'student')
            ->pluck('batch_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        if ($batchIds === []) {
            return view('academics::osce.student-index', ['sessions' => collect()]);
        }
        $institutionIds = Batch::whereIn('id', $batchIds)->pluck('institution_id')->unique()->map(fn ($id) => (int) $id)->all();
        $sessions = OsceSession::with(['institution', 'batch', 'stations'])
            ->where(function ($q) use ($batchIds, $institutionIds) {
                $q->whereIn('batch_id', $batchIds)
                    ->orWhere(function ($q2) use ($institutionIds) {
                        $q2->whereNull('batch_id')->whereIn('institution_id', $institutionIds);
                    });
            })
            ->orderByDesc('starts_at')->orderByDesc('id')
            ->limit(100)->get();

        return view('academics::osce.student-index', compact('sessions'));
    }

    public function create()
    {
        $user = auth()->user();
        $institutions = $this->institutionsForForm($user);
        $batches = $this->batchesForForm($user);

        return view('academics::osce.create', compact('institutions', 'batches'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $institutions = $this->institutionsForForm($user);
        $allowedInstIds = $institutions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $validated = $request->validate([
            'institution_id' => ['required', 'integer', Rule::in($allowedInstIds)],
            'batch_id' => ['nullable', 'integer', 'exists:academic_batches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
        ]);
        $this->validateBatchForInstitution((int) $validated['institution_id'], $validated['batch_id'] ?? null, $user);
        OsceSession::create([
            'institution_id' => $validated['institution_id'],
            'batch_id' => $validated['batch_id'] ?: null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? 120),
            'created_by' => $user->id,
        ]);

        return redirect()->route('academics.osce.index')->with('success', 'OSCE session created. Add stations next.');
    }

    public function show(OsceSession $session)
    {
        $this->authorizeStaffSession($session);
        $session->load(['institution', 'batch', 'stations']);
        $readOnly = false;

        return view('academics::osce.show', compact('session', 'readOnly'));
    }

    public function studentShow(OsceSession $session)
    {
        $user = auth()->user();
        $batchIds = DB::table('academic_batch_users')
            ->where('user_id', $user->id)
            ->where('type', 'student')
            ->pluck('batch_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $institutionIds = Batch::whereIn('id', $batchIds)->pluck('institution_id')->unique()->map(fn ($id) => (int) $id)->all();
        $allowed = ($session->batch_id && in_array((int) $session->batch_id, $batchIds, true))
            || (! $session->batch_id && in_array((int) $session->institution_id, $institutionIds, true));
        if (! $allowed) {
            abort(403);
        }
        $session->load(['institution', 'batch', 'stations']);
        $readOnly = true;

        return view('academics::osce.show', compact('session', 'readOnly'));
    }

    public function edit(OsceSession $session)
    {
        $this->authorizeStaffSession($session);
        $user = auth()->user();
        $institutions = $this->institutionsForForm($user);
        $batches = $this->batchesForForm($user, (int) $session->institution_id);
        $session->load(['institution', 'batch', 'stations']);

        return view('academics::osce.edit', compact('session', 'institutions', 'batches'));
    }

    public function update(Request $request, OsceSession $session)
    {
        $this->authorizeStaffSession($session);
        $user = auth()->user();
        $institutions = $this->institutionsForForm($user);
        $allowedInstIds = $institutions->pluck('id')->map(fn ($id) => (int) $id)->all();
        $validated = $request->validate([
            'institution_id' => ['required', 'integer', Rule::in($allowedInstIds)],
            'batch_id' => ['nullable', 'integer', 'exists:academic_batches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
        ]);
        $this->validateBatchForInstitution((int) $validated['institution_id'], $validated['batch_id'] ?? null, $user);
        $session->update([
            'institution_id' => $validated['institution_id'],
            'batch_id' => $validated['batch_id'] ?: null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? 120),
        ]);

        return redirect()->route('academics.osce.show', $session)->with('success', 'Session updated.');
    }

    public function destroy(OsceSession $session)
    {
        $this->authorizeStaffSession($session);
        $session->delete();

        return redirect()->route('academics.osce.index')->with('success', 'OSCE session removed.');
    }

    public function authorizeStaffSession(OsceSession $session): void
    {
        $user = auth()->user();
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return;
        }
        if ($user->role === 'institution_admin' && (int) $session->institution_id === (int) $user->academic_institution_id) {
            return;
        }
        if ($user->role === 'faculty') {
            if ((int) $session->institution_id !== (int) $user->academic_institution_id) {
                abort(403);
            }
            if ($session->batch_id) {
                $batchIds = $this->facultyBatchIds($user->id);
                if (! in_array((int) $session->batch_id, $batchIds, true)) {
                    abort(403);
                }
            }

            return;
        }
        abort(403);
    }

    protected function validateBatchForInstitution(int $institutionId, ?int $batchId, $user): void
    {
        if (! $batchId) {
            return;
        }
        $batch = Batch::find($batchId);
        if (! $batch || (int) $batch->institution_id !== $institutionId) {
            abort(422, 'Batch does not belong to the selected institution.');
        }
        if ($user->role === 'faculty') {
            $batchIds = $this->facultyBatchIds($user->id);
            if (! in_array($batchId, $batchIds, true)) {
                abort(403);
            }
        }
    }

    protected function institutionsForForm($user): \Illuminate\Support\Collection
    {
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return Institution::orderBy('name')->get();
        }
        if ($user->academic_institution_id) {
            return Institution::where('id', $user->academic_institution_id)->orderBy('name')->get();
        }

        return collect();
    }

    protected function batchesForForm($user, ?int $institutionId = null): \Illuminate\Support\Collection
    {
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            $q = Batch::with('institution')->orderBy('name');
            if ($institutionId) {
                $q->where('institution_id', $institutionId);
            }

            return $q->get();
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return Batch::with('institution')->forInstitution((int) $user->academic_institution_id)->orderBy('name')->get();
        }
        if ($user->role === 'faculty') {
            $ids = $this->facultyBatchIds($user->id);

            return Batch::with('institution')->whereIn('id', $ids)->orderBy('name')->get();
        }

        return collect();
    }
}

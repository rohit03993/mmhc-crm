<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Core\User;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Institution;
use App\Modules\Academics\Services\AcademicMembershipSyncService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    protected function scopeBatches()
    {
        $user = auth()->user();
        $q = Batch::with('institution')->orderBy('name');
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return $q;
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return $q->forInstitution((int) $user->academic_institution_id);
        }

        return $q->whereRaw('1 = 0');
    }

    public function index()
    {
        $table = (new Batch)->getTable();
        $pivot = 'academic_batch_users';
        $batches = $this->scopeBatches()
            ->selectRaw("{$table}.*, (SELECT COUNT(*) FROM {$pivot} WHERE {$pivot}.batch_id = {$table}.id AND {$pivot}.type = ?) AS students_count, (SELECT COUNT(*) FROM {$pivot} WHERE {$pivot}.batch_id = {$table}.id AND {$pivot}.type = ?) AS faculty_count", ['student', 'faculty'])
            ->orderBy('name')
            ->paginate(10);

        return view('academics::batches.index', compact('batches'));
    }

    public function create()
    {
        $institutions = Institution::active()->orderBy('name')->get();
        $user = auth()->user();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $institutions = $institutions->where('id', $user->academic_institution_id)->values();
        }

        return view('academics::batches.create', compact('institutions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:academic_institutions,id',
            'name' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);
        $user = auth()->user();
        if ($user->role === 'institution_admin') {
            $validated['institution_id'] = $user->academic_institution_id;
        }
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            $allowed = Institution::active()->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! in_array((int) $validated['institution_id'], $allowed, true)) {
                abort(403, 'Invalid institution.');
            }
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        Batch::create($validated);

        return redirect()->route('academics.batches.index')->with('success', 'Batch created successfully.');
    }

    public function edit(Batch $batch)
    {
        $this->authorizeBatch($batch);
        $batch->load(['institution', 'students', 'faculty']);
        $institutions = Institution::active()->orderBy('name')->get();
        $user = auth()->user();
        if ($user->role === 'institution_admin') {
            $institutions = $institutions->where('id', $user->academic_institution_id)->values();
        }
        $studentsAvailable = User::where('role', 'student')->orderBy('name')->get();
        $facultyAvailable = User::where('role', 'faculty')->orderBy('name')->get();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $facultyAvailable = $facultyAvailable->where('academic_institution_id', $user->academic_institution_id);
        }

        return view('academics::batches.edit', compact('batch', 'institutions', 'studentsAvailable', 'facultyAvailable'));
    }

    public function update(Request $request, Batch $batch)
    {
        $this->authorizeBatch($batch);
        $validated = $request->validate([
            'institution_id' => 'required|exists:academic_institutions,id',
            'name' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);
        $user = auth()->user();
        if ($user->role === 'institution_admin') {
            $validated['institution_id'] = $user->academic_institution_id;
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $batch->update($validated);
        $batch->refresh();
        app(AcademicMembershipSyncService::class)->syncInstitutionForBatchMembers($batch);

        return redirect()->route('academics.batches.index')->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        $this->authorizeBatch($batch);
        $batch->delete();

        return redirect()->route('academics.batches.index')->with('success', 'Batch deleted successfully.');
    }

    public function updateAssignments(Request $request, Batch $batch, AcademicMembershipSyncService $membershipSync)
    {
        $this->authorizeBatch($batch);
        $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
            'faculty_ids' => 'nullable|array',
            'faculty_ids.*' => 'exists:users,id',
        ]);
        $studentIds = $request->input('student_ids', []);
        $facultyIds = $request->input('faculty_ids', []);
        $sync = [];
        foreach ($studentIds as $id) {
            $sync[$id] = ['type' => 'student'];
        }
        foreach ($facultyIds as $id) {
            $sync[$id] = ['type' => 'faculty'];
        }
        $batch->users()->sync($sync);
        $batch->refresh();
        $membershipSync->syncInstitutionForBatchMembers($batch);

        return redirect()->route('academics.batches.edit', $batch)->with('success', 'Assignments updated.');
    }

    protected function authorizeBatch(Batch $batch): void
    {
        $user = auth()->user();
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return;
        }
        if ($user->role === 'institution_admin' && (int) $user->academic_institution_id !== (int) $batch->institution_id) {
            abort(403, 'You can only manage batches of your institution.');
        }
        if ($user->role !== 'institution_admin') {
            abort(403, 'You cannot manage batches.');
        }
    }
}

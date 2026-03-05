<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Batch;
use App\Modules\Academics\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    protected function scopeBatches()
    {
        $user = auth()->user();
        if ($user->role === 'super_admin') {
            return Batch::with('institution');
        }
        return Batch::with('institution')->forInstitution((int) $user->academic_institution_id);
    }

    public function index(Request $request)
    {
        $batchId = $request->get('batch_id');
        $query = Subject::with(['batch.institution', 'faculty']);
        $user = auth()->user();
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            $query->whereHas('batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id));
        }
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }
        $subjects = $query->orderBy('name')->paginate(15)->withQueryString();
        $batches = $this->scopeBatches()->orderBy('name')->get();
        return view('academics::subjects.index', compact('subjects', 'batches'));
    }

    public function create()
    {
        $batches = $this->scopeBatches()->active()->orderBy('name')->get();
        return view('academics::subjects.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:academic_batches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);
        $this->authorizeBatch($validated['batch_id']);
        $validated['is_active'] = $request->boolean('is_active', true);
        Subject::create($validated);
        return redirect()->route('academics.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $this->authorizeBatch($subject->batch_id);
        $subject->load(['batch.institution', 'faculty']);
        $batches = $this->scopeBatches()->active()->orderBy('name')->get();
        $facultyAvailable = $subject->batch->faculty()->orderBy('name')->get();
        return view('academics::subjects.edit', compact('subject', 'batches', 'facultyAvailable'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeBatch($subject->batch_id);
        $validated = $request->validate([
            'batch_id' => 'required|exists:academic_batches,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);
        $this->authorizeBatch($validated['batch_id']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $subject->update($validated);
        return redirect()->route('academics.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorizeBatch($subject->batch_id);
        $subject->delete();
        return redirect()->route('academics.subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function updateFaculty(Request $request, Subject $subject)
    {
        $this->authorizeBatch($subject->batch_id);
        $request->validate([
            'faculty_ids' => 'nullable|array',
            'faculty_ids.*' => 'exists:users,id',
        ]);
        $facultyIds = $request->input('faculty_ids', []);
        $subject->faculty()->sync($facultyIds);
        return redirect()->route('academics.subjects.edit', $subject)->with('success', 'Faculty assignment updated.');
    }

    protected function authorizeBatch(int $batchId): void
    {
        $batch = Batch::findOrFail($batchId);
        $user = auth()->user();
        if ($user->role === 'institution_admin' && (int) $user->academic_institution_id !== (int) $batch->institution_id) {
            abort(403, 'You can only manage subjects of your institution.');
        }
    }
}

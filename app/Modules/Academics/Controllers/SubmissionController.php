<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Submission;
use App\Modules\Academics\Services\TopicCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /** Assignments the current student is eligible for (in batch for that subject). */
    protected function myAssignmentsQuery()
    {
        $userId = auth()->id();

        return Assignment::with(['topic.subject.batch', 'submissions' => fn ($q) => $q->where('user_id', $userId)])
            ->whereHas('topic.subject.batch.students', fn ($q) => $q->where('users.id', $userId));
    }

    public function index()
    {
        $assignments = $this->myAssignmentsQuery()
            ->orderBy('due_date')
            ->orderBy('title')
            ->paginate(10);

        return view('academics::submissions.my-assignments', compact('assignments'));
    }

    public function create(Assignment $assignment)
    {
        $eligibleIds = $assignment->eligibleStudentIds();
        if (! in_array(auth()->id(), $eligibleIds)) {
            abort(403, 'You are not eligible to submit this assignment.');
        }
        $assignment->load('topic.subject.batch');
        $existing = Submission::where('assignment_id', $assignment->id)->where('user_id', auth()->id())->first();

        return view('academics::submissions.submit', compact('assignment', 'existing'));
    }

    public function store(Request $request, Assignment $assignment)
    {
        $eligibleIds = $assignment->eligibleStudentIds();
        if (! in_array(auth()->id(), $eligibleIds)) {
            abort(403, 'You are not eligible to submit this assignment.');
        }
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'notes' => 'nullable|string|max:1000',
        ]);
        $file = $request->file('file');
        $dir = 'academic/submissions/'.$assignment->id;
        $path = $file->storeAs($dir, auth()->id().'_'.time().'_'.$file->getClientOriginalName(), 'public');
        $existing = Submission::where('assignment_id', $assignment->id)->where('user_id', auth()->id())->first();
        if ($existing) {
            if ($existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->update([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
                'notes' => $request->input('notes'),
            ]);
        } else {
            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id' => auth()->id(),
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'submitted_at' => now(),
                'notes' => $request->input('notes'),
            ]);
        }
        TopicCompletionService::checkAndCompleteTopic($assignment->fresh());

        return redirect()->route('academics.my-assignments')->with('success', 'Submission saved successfully.');
    }

    public function download(Submission $submission)
    {
        $user = auth()->user();
        if ($user->id === $submission->user_id || in_array($user->role, ['super_admin', 'institution_admin', 'faculty'])) {
            if ($submission->assignment && in_array($user->role, ['super_admin', 'institution_admin', 'faculty'])) {
                // Admin/faculty: ensure they can see this assignment
            } elseif ($user->id !== $submission->user_id) {
                $this->authorizeFacultyOrAdminForAssignment($submission->assignment_id);
            }
        } else {
            abort(403);
        }
        if (! Storage::disk('public')->exists($submission->file_path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($submission->file_path),
            $submission->original_name ?? 'submission'
        );
    }

    /** Submissions list for an assignment (faculty/admin). */
    public function forAssignment(Assignment $assignment)
    {
        $this->authorizeFacultyOrAdminForAssignment($assignment->id);
        $assignment->load('topic.subject.batch');
        $eligibleIds = $assignment->eligibleStudentIds();
        $submissions = Submission::with('user')->where('assignment_id', $assignment->id)->get()->keyBy('user_id');
        $students = \App\Models\Core\User::whereIn('id', $eligibleIds)->orderBy('name')->get();

        return view('academics::submissions.for-assignment', compact('assignment', 'submissions', 'students'));
    }

    protected function authorizeFacultyOrAdminForAssignment(int $assignmentId): void
    {
        $assignment = Assignment::with('topic.subject')->findOrFail($assignmentId);
        $user = auth()->user();
        if ($user->role === 'super_admin') {
            return;
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            if ((int) $assignment->topic->subject->batch->institution_id !== (int) $user->academic_institution_id) {
                abort(403);
            }

            return;
        }
        if ($user->role === 'faculty') {
            $isFaculty = $assignment->topic->subject->faculty()->where('user_id', $user->id)->exists();
            if (! $isFaculty) {
                abort(403);
            }

            return;
        }
        abort(403);
    }
}

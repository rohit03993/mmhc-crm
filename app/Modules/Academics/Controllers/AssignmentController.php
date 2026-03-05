<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Assignment;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    protected function scopeSubjects()
    {
        $user = auth()->user();
        if ($user->role === 'super_admin') {
            return Subject::with('batch.institution')->active();
        }
        if ($user->role === 'institution_admin' && $user->academic_institution_id) {
            return Subject::with('batch.institution')->active()
                ->whereHas('batch', fn ($q) => $q->where('institution_id', $user->academic_institution_id));
        }
        if ($user->role === 'faculty') {
            return Subject::with('batch.institution')->active()
                ->whereHas('faculty', fn ($q) => $q->where('user_id', $user->id));
        }
        return Subject::with('batch.institution')->whereRaw('1 = 0');
    }

    protected function scopeTopics()
    {
        $subjectIds = $this->scopeSubjects()->pluck('id');
        return Topic::with(['subject.batch.institution'])->whereIn('subject_id', $subjectIds);
    }

    protected function scopeAssignments()
    {
        $topicIds = $this->scopeTopics()->pluck('id');
        return Assignment::with(['topic.subject.batch.institution'])->whereIn('topic_id', $topicIds);
    }

    protected function authorizeTopic(int $topicId): void
    {
        $allowedIds = $this->scopeTopics()->pluck('id')->toArray();
        if (!in_array($topicId, $allowedIds)) {
            abort(403, 'You cannot manage assignments for this topic.');
        }
    }

    public function index(Request $request)
    {
        $topicId = $request->get('topic_id');
        $query = $this->scopeAssignments();
        if ($topicId) {
            $query->where('topic_id', $topicId);
        }
        $assignments = $query->orderBy('due_date')->orderBy('title')->paginate(15)->withQueryString();
        $topics = $this->scopeTopics()->orderBy('name')->get();
        return view('academics::assignments.index', compact('assignments', 'topics'));
    }

    public function create()
    {
        $topics = $this->scopeTopics()->orderBy('name')->get();
        return view('academics::assignments.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:academic_topics,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB
        ]);
        $this->authorizeTopic((int) $validated['topic_id']);
        $assignment = Assignment::create([
            'topic_id' => $validated['topic_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);
        $this->storeAttachments($assignment, $request->file('attachments'));
        return redirect()->route('academics.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function show(Assignment $assignment)
    {
        $this->authorizeTopic($assignment->topic_id);
        $assignment->load('topic.subject.batch.institution');
        return view('academics::assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        $this->authorizeTopic($assignment->topic_id);
        $assignment->load('topic.subject.batch.institution');
        $topics = $this->scopeTopics()->orderBy('name')->get();
        return view('academics::assignments.edit', compact('assignment', 'topics'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeTopic($assignment->topic_id);
        $validated = $request->validate([
            'topic_id' => 'required|exists:academic_topics,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240',
        ]);
        $this->authorizeTopic((int) $validated['topic_id']);
        $assignment->update([
            'topic_id' => $validated['topic_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);
        $this->storeAttachments($assignment, $request->file('attachments'));
        return redirect()->route('academics.assignments.index')->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorizeTopic($assignment->topic_id);
        $this->deleteAttachments($assignment);
        $assignment->delete();
        return redirect()->route('academics.assignments.index')->with('success', 'Assignment deleted successfully.');
    }

    public function downloadAttachment(Assignment $assignment, int $index)
    {
        $this->authorizeTopic($assignment->topic_id);
        $attachments = $assignment->attachments ?? [];
        $file = $attachments[$index] ?? null;
        if (!$file || !Storage::disk('public')->exists($file['path'] ?? '')) {
            abort(404);
        }
        $path = Storage::disk('public')->path($file['path']);
        return response()->download($path, $file['name'] ?? 'attachment');
    }

    public function removeAttachment(Request $request, Assignment $assignment)
    {
        $this->authorizeTopic($assignment->topic_id);
        $index = (int) $request->input('index');
        $attachments = $assignment->attachments ?? [];
        $file = $attachments[$index] ?? null;
        if ($file && isset($file['path']) && Storage::disk('public')->exists($file['path'])) {
            Storage::disk('public')->delete($file['path']);
        }
        $newAttachments = array_values(array_filter($attachments, fn ($_, $i) => $i !== $index, ARRAY_FILTER_USE_BOTH));
        $assignment->update(['attachments' => $newAttachments]);
        return redirect()->route('academics.assignments.edit', $assignment)->with('success', 'Attachment removed.');
    }

    protected function storeAttachments(Assignment $assignment, ?array $files): void
    {
        if (!$files) {
            return;
        }
        $dir = 'academic/assignments/' . $assignment->id;
        $current = $assignment->attachments ?? [];
        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $path = $file->storeAs($dir, $file->getClientOriginalName(), 'public');
            $current[] = ['path' => $path, 'name' => $file->getClientOriginalName()];
        }
        $assignment->update(['attachments' => $current]);
    }

    protected function deleteAttachments(Assignment $assignment): void
    {
        $attachments = $assignment->attachments ?? [];
        foreach ($attachments as $a) {
            $path = $a['path'] ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}

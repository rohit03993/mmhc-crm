<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /** Subjects the current user can manage (create topics under). */
    protected function scopeSubjects()
    {
        $user = auth()->user();
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

    /** Topics the current user can see. */
    protected function scopeTopics()
    {
        $subjectIds = $this->scopeSubjects()->pluck('id');
        return Topic::with(['subject.batch.institution'])->whereIn('subject_id', $subjectIds);
    }

    public function index(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $query = $this->scopeTopics();
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        $topics = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();
        $subjects = $this->scopeSubjects()->orderBy('name')->get();
        return view('academics::topics.index', compact('topics', 'subjects'));
    }

    public function create()
    {
        $subjects = $this->scopeSubjects()->orderBy('name')->get();
        return view('academics::topics.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:academic_subjects,id',
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $this->authorizeSubject($validated['subject_id']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        Topic::create($validated);
        return redirect()->route('academics.topics.index')->with('success', 'Topic created successfully.');
    }

    public function edit(Topic $topic)
    {
        $this->authorizeSubject($topic->subject_id);
        $topic->load('subject.batch.institution');
        $subjects = $this->scopeSubjects()->orderBy('name')->get();
        return view('academics::topics.edit', compact('topic', 'subjects'));
    }

    public function update(Request $request, Topic $topic)
    {
        $this->authorizeSubject($topic->subject_id);
        $validated = $request->validate([
            'subject_id' => 'required|exists:academic_subjects,id',
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $this->authorizeSubject($validated['subject_id']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $topic->update($validated);
        return redirect()->route('academics.topics.index')->with('success', 'Topic updated successfully.');
    }

    public function destroy(Topic $topic)
    {
        $this->authorizeSubject($topic->subject_id);
        $topic->delete();
        return redirect()->route('academics.topics.index')->with('success', 'Topic deleted successfully.');
    }

    protected function authorizeSubject(int $subjectId): void
    {
        $allowedIds = $this->scopeSubjects()->pluck('id')->toArray();
        if (!in_array($subjectId, $allowedIds)) {
            abort(403, 'You cannot manage topics for this subject.');
        }
    }
}

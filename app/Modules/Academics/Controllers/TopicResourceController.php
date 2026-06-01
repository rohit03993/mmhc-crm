<?php

namespace App\Modules\Academics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academics\Models\Subject;
use App\Modules\Academics\Models\Topic;
use App\Modules\Academics\Models\TopicResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TopicResourceController extends Controller
{
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

    protected function authorizeTopic(Topic $topic): void
    {
        $ids = $this->scopeSubjects()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array((int) $topic->subject_id, $ids, true)) {
            abort(403, 'You cannot manage resources for this topic.');
        }
    }

    protected function authorizeStudentTopic(Topic $topic): void
    {
        $user = auth()->user();
        $batchId = $topic->subject->batch_id;
        $ok = DB::table('academic_batch_users')
            ->where('batch_id', $batchId)
            ->where('user_id', $user->id)
            ->where('type', 'student')
            ->exists();
        if (! $ok) {
            abort(403);
        }
    }

    /** Topics for the student’s batches — entry point to per-topic resource libraries (Phase B). */
    public function studentTopicsIndex()
    {
        $user = auth()->user();
        if ($user->role !== 'student') {
            abort(403);
        }
        $batchIds = DB::table('academic_batch_users')
            ->where('user_id', $user->id)
            ->where('type', 'student')
            ->pluck('batch_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        if ($batchIds === []) {
            return view('academics::topic-resources.student-topics-index', ['topics' => collect()]);
        }
        $topics = Topic::query()
            ->whereHas('subject', fn ($q) => $q->whereIn('batch_id', $batchIds))
            ->with(['subject.batch.institution'])
            ->orderBy('subject_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('academics::topic-resources.student-topics-index', compact('topics'));
    }

    public function index(Topic $topic)
    {
        $this->authorizeTopic($topic);
        $topic->load(['subject.batch.institution', 'resources']);

        return view('academics::topic-resources.index', compact('topic'));
    }

    public function studentLibrary(Topic $topic)
    {
        $this->authorizeStudentTopic($topic);
        $topic->load(['subject.batch.institution', 'resources']);

        return view('academics::topic-resources.student', compact('topic'));
    }

    public function create(Topic $topic)
    {
        $this->authorizeTopic($topic);
        $topic->load('subject.batch.institution');

        return view('academics::topic-resources.create', compact('topic'));
    }

    public function store(Request $request, Topic $topic)
    {
        $this->authorizeTopic($topic);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type' => ['required', Rule::in([
                TopicResource::TYPE_VIDEO_LINK,
                TopicResource::TYPE_FILE,
                TopicResource::TYPE_CHECKLIST,
            ])],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'max:20480'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);
        $filePath = null;
        if ($validated['resource_type'] === TopicResource::TYPE_VIDEO_LINK) {
            $request->validate(['video_url' => ['required', 'url', 'max:2000']]);
        } else {
            $request->validate(['file' => ['required', 'file', 'max:20480']]);
            $dir = 'academic/topic-resources/'.$topic->id;
            $filePath = $request->file('file')->store($dir, 'public');
        }
        TopicResource::create([
            'topic_id' => $topic->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'video_url' => $validated['resource_type'] === TopicResource::TYPE_VIDEO_LINK ? $validated['video_url'] : null,
            'file_path' => $filePath,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('academics.topics.resources.index', $topic)->with('success', 'Resource added.');
    }

    public function edit(Topic $topic, TopicResource $resource)
    {
        $this->authorizeTopic($topic);
        if ((int) $resource->topic_id !== (int) $topic->id) {
            abort(404);
        }
        $topic->load('subject.batch.institution');

        return view('academics::topic-resources.edit', compact('topic', 'resource'));
    }

    public function update(Request $request, Topic $topic, TopicResource $resource)
    {
        $this->authorizeTopic($topic);
        if ((int) $resource->topic_id !== (int) $topic->id) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'resource_type' => ['required', Rule::in([
                TopicResource::TYPE_VIDEO_LINK,
                TopicResource::TYPE_FILE,
                TopicResource::TYPE_CHECKLIST,
            ])],
            'video_url' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'max:20480'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);
        $videoUrl = null;
        $filePath = $resource->file_path;
        if ($validated['resource_type'] === TopicResource::TYPE_VIDEO_LINK) {
            $request->validate(['video_url' => ['required', 'url', 'max:2000']]);
            $videoUrl = $validated['video_url'];
            if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $filePath = null;
        } else {
            if ($request->hasFile('file')) {
                if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                    Storage::disk('public')->delete($resource->file_path);
                }
                $dir = 'academic/topic-resources/'.$topic->id;
                $filePath = $request->file('file')->store($dir, 'public');
            }
            if (! $filePath) {
                return redirect()->back()->withErrors(['file' => 'Upload a file for this resource type.'])->withInput();
            }
        }
        $resource->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'resource_type' => $validated['resource_type'],
            'video_url' => $videoUrl,
            'file_path' => $filePath,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return redirect()->route('academics.topics.resources.index', $topic)->with('success', 'Resource updated.');
    }

    public function destroy(Topic $topic, TopicResource $resource)
    {
        $this->authorizeTopic($topic);
        if ((int) $resource->topic_id !== (int) $topic->id) {
            abort(404);
        }
        if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
            Storage::disk('public')->delete($resource->file_path);
        }
        $resource->delete();

        return redirect()->route('academics.topics.resources.index', $topic)->with('success', 'Resource removed.');
    }

    public function download(Topic $topic, TopicResource $resource)
    {
        $user = auth()->user();
        if ($user->role === 'student') {
            $this->authorizeStudentTopic($topic);
        } else {
            $this->authorizeTopic($topic);
        }
        if ((int) $resource->topic_id !== (int) $topic->id) {
            abort(404);
        }
        if (! $resource->file_path || ! Storage::disk('public')->exists($resource->file_path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($resource->file_path),
            basename($resource->file_path)
        );
    }
}

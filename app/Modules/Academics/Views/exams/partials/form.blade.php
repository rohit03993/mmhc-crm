@php
    $exam = $exam ?? null;
    $isEdit = $exam !== null;
@endphp
<form method="post" action="{{ $isEdit ? route('academics.exams.update', $exam) : route('academics.exams.store') }}" class="needs-validation">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="institution_id" value="{{ old('institution_id', $exam?->institution_id ?? $user->academic_institution_id) }}">

    <div class="mb-3">
        <label class="form-label fw-semibold">Audience <span class="text-danger">*</span></label>
        <select name="audience_type" class="form-select" id="examAudience" required>
            @foreach(\App\Modules\Academics\Models\AcademicExam::audienceTypes() as $type)
                <option value="{{ $type }}" @selected(old('audience_type', $exam?->audience_type) === $type)>{{ str_replace('_', ' ', $type) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" id="wrapSubject">
        <label class="form-label fw-semibold">Subject (subject cohort)</label>
        <select name="subject_id" class="form-select">
            <option value="">—</option>
            @foreach($subjects as $sub)
                <option value="{{ $sub->id }}" @selected((int) old('subject_id', $exam?->subject_id) === (int) $sub->id)>
                    {{ $sub->name }}@if($sub->batch) ({{ $sub->batch->name }} — {{ $sub->batch->institution->name ?? '' }})@endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" id="wrapBatch">
        <label class="form-label fw-semibold">Batch (batch scope)</label>
        <select name="batch_id" class="form-select">
            <option value="">—</option>
            @foreach($batches as $b)
                <option value="{{ $b->id }}" @selected((int) old('batch_id', $exam?->batch_id) === (int) $b->id)>
                    {{ $b->name }} — {{ $b->institution->name ?? '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3 form-check">
        <input type="hidden" name="allows_cross_institution" value="0">
        <input type="checkbox" name="allows_cross_institution" value="1" class="form-check-input" id="crossInst"
            @checked(old('allows_cross_institution', $exam?->allows_cross_institution))>
        <label class="form-check-label" for="crossInst">Community: allow any active student (cross-institution)</label>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" required maxlength="255" value="{{ old('title', $exam?->title) }}">
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Instructions</label>
        <textarea name="instructions" class="form-control" rows="3">{{ old('instructions', $exam?->instructions) }}</textarea>
    </div>

    @php $assignmentsList = $assignments ?? collect(); @endphp
    <div class="mb-3">
        <label class="form-label fw-semibold">Linked assignment <span class="text-muted fw-normal">(optional)</span></label>
        <select name="assignment_id" class="form-select @error('assignment_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach($assignmentsList as $asg)
                <option value="{{ $asg->id }}" @selected((int) old('assignment_id', $exam?->assignment_id ?? 0) === (int) $asg->id)>
                    {{ $asg->title }}@if($asg->topic) — {{ $asg->topic->name }}@endif
                </option>
            @endforeach
        </select>
        @error('assignment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($assignmentsList->isEmpty())
            <p class="form-text text-muted mb-0">No assignments loaded for this institution (super admin: ensure institution is selected above and assignments exist).</p>
        @else
            <p class="form-text text-muted mb-0">Links this quiz from the assignment detail page and from students&rsquo; My assignments when they can take it.</p>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Duration (minutes)</label>
            <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes', $exam?->duration_minutes) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Max attempts <span class="text-danger">*</span></label>
            <input type="number" name="max_attempts" class="form-control" required min="1" max="20" value="{{ old('max_attempts', $exam?->max_attempts ?? 1) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Opens at</label>
            <input type="datetime-local" name="opens_at" class="form-control" value="{{ old('opens_at', $exam?->opens_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Closes at</label>
            <input type="datetime-local" name="closes_at" class="form-control" value="{{ old('closes_at', $exam?->closes_at?->format('Y-m-d\TH:i')) }}">
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap gap-3">
        <div class="form-check">
            <input type="hidden" name="shuffle_questions" value="0">
            <input type="checkbox" name="shuffle_questions" value="1" class="form-check-input" id="sq" @checked(old('shuffle_questions', $exam?->shuffle_questions))>
            <label class="form-check-label" for="sq">Shuffle questions</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="shuffle_options" value="0">
            <input type="checkbox" name="shuffle_options" value="1" class="form-check-input" id="so" @checked(old('shuffle_options', $exam?->shuffle_options))>
            <label class="form-check-label" for="so">Shuffle options</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub" @checked(old('is_published', $exam?->is_published))>
            <label class="form-check-label" for="pub">Published</label>
        </div>
    </div>

    <button type="submit" class="btn btn-primary rounded-pill px-4">{{ $isEdit ? 'Save changes' : 'Create exam' }}</button>
</form>

<script>
(function () {
    const aud = document.getElementById('examAudience');
    if (!aud) return;
    const ws = document.getElementById('wrapSubject');
    const wb = document.getElementById('wrapBatch');
    const cross = document.getElementById('crossInst');
    function sync() {
        const v = aud.value;
        if (ws) ws.style.display = v === 'subject_cohort' ? '' : 'none';
        if (wb) wb.style.display = v === 'batch' ? '' : 'none';
        if (v !== 'community' && cross) cross.checked = false;
    }
    aud.addEventListener('change', sync);
    sync();
})();
</script>

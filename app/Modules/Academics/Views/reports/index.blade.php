@extends('auth::layout')

@section('title', 'Reports - Academics')
@section('page-title', 'Reports')

@section('content')
<div class="container-fluid py-3 py-md-4 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Reports', 'lede' => 'Batch progress, faculty performance, and student SPI.'])

    <div class="alert alert-light border mb-4 mb-md-3">
        <strong>Student submission report</strong> – SPI % reflects assignments fully credited (submitted + all shared mentors rated when applicable).
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('academics.reports.show') }}" method="GET" class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="type" class="form-label">Report type</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="batch_progress" {{ request('type') === 'batch_progress' ? 'selected' : '' }}>Batch progress</option>
                        <option value="faculty_performance" {{ request('type') === 'faculty_performance' ? 'selected' : '' }}>Faculty performance</option>
                        <option value="topic_completion" {{ request('type') === 'topic_completion' ? 'selected' : '' }}>Topic completion</option>
                        <option value="student_submission" {{ request('type') === 'student_submission' ? 'selected' : '' }}>Student submission</option>
                    </select>
                </div>
                @if($institutions->isNotEmpty())
                <div class="col-12 col-md-4">
                    <label for="institution_id" class="form-label">Institution</label>
                    <select name="institution_id" id="institution_id" class="form-select">
                        <option value="">All institutions</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($batches->isNotEmpty())
                <div class="col-12 col-md-4">
                    <label for="batch_id" class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select">
                        <option value="">All batches</option>
                        @foreach($batches as $b)
                            <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if($subjects->isNotEmpty())
                <div class="col-12 col-md-4" id="subject_filter_wrapper" style="{{ request('type') === 'student_submission' ? 'display:none' : '' }}">
                    <label for="subject_id" class="form-label">Subject</label>
                    <select name="subject_id" id="subject_id" class="form-select">
                        <option value="">All subjects</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }} ({{ $sub->batch->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-4" id="taxonomy_teaching_wrap">
                    <label for="teaching_method_key" class="form-label small">Teaching format (topic reports)</label>
                    <select name="teaching_method_key" id="teaching_method_key" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach($taxonomyFilters['teaching_methods'] as $k => $label)
                            <option value="{{ $k }}" {{ request('teaching_method_key') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4" id="taxonomy_assignment_wrap">
                    <label for="assignment_type" class="form-label small">Assignment type (SPI report)</label>
                    <select name="assignment_type" id="assignment_type" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach($taxonomyFilters['assignment_types'] as $k => $label)
                            <option value="{{ $k }}" {{ request('assignment_type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4" id="taxonomy_assessment_wrap">
                    <label for="assessment_type_key" class="form-label small">Assessment tag (SPI report)</label>
                    <select name="assessment_type_key" id="assessment_type_key" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach($taxonomyFilters['assessment_types'] as $k => $label)
                            <option value="{{ $k }}" {{ request('assessment_type_key') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4" id="taxonomy_summative_wrap">
                    <div class="form-check mt-4">
                        <input type="hidden" name="summative_only" value="0">
                        <input type="checkbox" name="summative_only" value="1" class="form-check-input" id="summative_only" @checked(request()->boolean('summative_only'))>
                        <label class="form-check-label small" for="summative_only">Summative assignments only (SPI report)</label>
                    </div>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-chart-bar me-1"></i>View report</button>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
(function() {
    var typeSel = document.getElementById('type');
    if (!typeSel) return;
    var subjectWrap = document.getElementById('subject_filter_wrapper');
    var tw = document.getElementById('taxonomy_teaching_wrap');
    var aw = document.getElementById('taxonomy_assignment_wrap');
    var asw = document.getElementById('taxonomy_assessment_wrap');
    var sw = document.getElementById('taxonomy_summative_wrap');
    function sync() {
        var t = typeSel.value;
        if (subjectWrap) {
            subjectWrap.style.display = t === 'student_submission' ? 'none' : '';
        }
        var teach = t === 'topic_completion' || t === 'faculty_performance';
        var spi = t === 'student_submission';
        if (tw) tw.style.display = teach ? '' : 'none';
        if (aw) aw.style.display = spi ? '' : 'none';
        if (asw) asw.style.display = spi ? '' : 'none';
        if (sw) sw.style.display = spi ? '' : 'none';
    }
    typeSel.addEventListener('change', sync);
    sync();
})();
</script>
@endsection

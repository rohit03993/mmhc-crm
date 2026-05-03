@extends('auth::layout')

@section('title', 'Edit exam — Academics')
@section('page-title', 'Edit exam')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm rounded-pill">View exam</a>
        <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">All exams</a>
    </div>

    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold mb-3">Settings: {{ $exam->title }}</h1>
            @include('academics::exams.partials.form', ['exam' => $exam])
        </div>
    </div>

    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-3"><i class="fas fa-list-ol me-2 text-primary"></i>Questions (MCQ)</h2>
            @if($exam->questions->isEmpty())
                <p class="text-muted small mb-3">Add at least one question before students can start.</p>
            @else
                <ul class="list-group list-group-flush border rounded-3 mb-3">
                    @foreach($exam->questions as $q)
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div class="flex-grow-1">
                                <span class="badge bg-light text-dark border me-2">#{{ $loop->iteration }}</span>
                                @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                                    <span class="badge text-bg-info me-1">Multi</span>
                                @else
                                    <span class="badge text-bg-secondary me-1">Single</span>
                                @endif
                                <span class="fw-medium">{{ Str::limit($q->body, 120) }}</span>
                                <span class="text-muted small">— {{ $q->points }} pt</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <form method="post" action="{{ route('academics.exams.questions.reorder', [$exam, $q]) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Move up" @disabled($loop->first)>↑</button>
                                </form>
                                <form method="post" action="{{ route('academics.exams.questions.reorder', [$exam, $q]) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary px-2" title="Move down" @disabled($loop->last)>↓</button>
                                </form>
                                <form method="post" action="{{ route('academics.exams.questions.destroy', [$exam, $q]) }}" onsubmit="return confirm('Remove this question?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Remove</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <h3 class="small text-uppercase text-muted fw-semibold mb-2">Add question</h3>
            <form method="post" action="{{ route('academics.exams.questions.store', $exam) }}" class="border rounded-3 p-3 bg-light bg-opacity-50" id="addQuestionForm">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Question type</label>
                        <select name="question_type" id="question_type" class="form-select form-select-sm">
                            <option value="{{ \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_SINGLE }}" @selected(old('question_type', \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_SINGLE) === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_SINGLE)>Single choice</option>
                            <option value="{{ \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI }}" @selected(old('question_type') === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)>Multiple choice (all correct)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Points</label>
                        <input type="number" name="points" class="form-control form-control-sm" value="{{ old('points', 1) }}" min="0" step="0.25">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Question</label>
                    <textarea name="body" class="form-control" rows="2" required placeholder="Question text">{{ old('body') }}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Explanation <span class="text-muted fw-normal">(optional, shown after submit)</span></label>
                    <textarea name="explanation" class="form-control form-control-sm" rows="2" placeholder="Why the correct answer(s) are right">{{ old('explanation') }}</textarea>
                </div>
                @for($i = 0; $i < 4; $i++)
                    <div class="mb-2">
                        <label class="form-label small">Option {{ chr(65+$i) }}</label>
                        <input type="text" name="options[]" class="form-control form-control-sm" value="{{ old('options.'.$i) }}" required>
                    </div>
                @endfor
                <div class="mb-2" id="single-correct-block">
                    <span class="form-label small d-block">Correct answer</span>
                    @for($i = 0; $i < 4; $i++)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="correct_option" id="c{{ $i }}" value="{{ $i }}" @checked((int) old('correct_option', 0) === $i)>
                            <label class="form-check-label" for="c{{ $i }}">{{ chr(65+$i) }}</label>
                        </div>
                    @endfor
                </div>
                <div class="mb-2 d-none" id="multi-correct-block">
                    <span class="form-label small d-block">Correct answers <span class="text-muted">(check all that apply)</span></span>
                    @php $oldMulti = array_map('intval', (array) old('correct_indices', [])); @endphp
                    @for($i = 0; $i < 4; $i++)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="correct_indices[]" id="m{{ $i }}" value="{{ $i }}" @checked(in_array($i, $oldMulti, true))>
                            <label class="form-check-label" for="m{{ $i }}">{{ chr(65+$i) }}</label>
                        </div>
                    @endfor
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill">Add question</button>
            </form>
            <script>
            (function () {
                var sel = document.getElementById('question_type');
                var single = document.getElementById('single-correct-block');
                var multi = document.getElementById('multi-correct-block');
                if (!sel || !single || !multi) return;
                function sync() {
                    var isMulti = sel.value === '{{ \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI }}';
                    single.classList.toggle('d-none', isMulti);
                    multi.classList.toggle('d-none', !isMulti);
                }
                sel.addEventListener('change', sync);
                sync();
            })();
            </script>
        </div>
    </div>

    <div class="card border border-danger border-opacity-50 shadow-sm rounded-3">
        <div class="card-body p-4">
            <h2 class="h6 text-danger fw-bold mb-2">Delete exam</h2>
            <p class="small text-muted mb-2">Removes all questions and student attempts for this quiz.</p>
            <form method="post" action="{{ route('academics.exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam permanently?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Delete exam</button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Taking: '.$exam->title)
@section('page-title', 'Quiz')

@section('head')
<link rel="stylesheet" href="{{ asset('css/academics-exam-mobile.css') }}?v=20260603">
@endsection

@section('content')
@php $questionCount = $questions->count(); @endphp

{{-- Desktop: all questions on one page --}}
<div class="d-none d-md-block container-fluid py-3 py-md-4">
    <div class="mb-3 small text-muted">
        <strong>{{ $exam->title }}</strong> · Started {{ $attempt->started_at?->format('M j, H:i') }}
        @if($exam->duration_minutes)
            · Allowance {{ $exam->duration_minutes }} min
        @endif
    </div>
    @if($attemptExpiresAt)
        <div id="examTimerBannerDesktop" class="alert alert-warning py-2 small mb-3 d-flex flex-wrap align-items-center gap-2" role="status">
            <span>Time remaining:</span>
            <strong id="examTimerLabelDesktop">—</strong>
        </div>
    @endif

    <form method="post" action="{{ route('academics.exams.submit', [$exam, $attempt]) }}" id="examTakeFormDesktop">
        @csrf
        @foreach($questions as $q)
            <div class="card border shadow-sm rounded-3 mb-3">
                <div class="card-body p-4">
                    <p class="fw-semibold mb-2">{{ $loop->iteration }}. {!! nl2br(e($q->body)) !!}</p>
                    <p class="small text-muted mb-2">{{ $q->points }} point(s)
                        @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                            · <span class="text-primary fw-semibold">Select all that apply</span>
                        @endif
                    </p>
                    @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                        @foreach($q->options as $opt)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="answers[{{ $q->id }}][]" id="desk_o{{ $opt->id }}" value="{{ $opt->id }}">
                                <label class="form-check-label" for="desk_o{{ $opt->id }}">{{ $opt->body }}</label>
                            </div>
                        @endforeach
                    @else
                        @foreach($q->options as $opt)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="desk_o{{ $opt->id }}" value="{{ $opt->id }}" required>
                                <label class="form-check-label" for="desk_o{{ $opt->id }}">{{ $opt->body }}</label>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary rounded-pill px-4 mb-4">Submit answers</button>
        <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-link btn-sm text-muted">Cancel</a>
    </form>
</div>

{{-- Mobile: one question per screen --}}
<div class="acad-exam-mobile d-md-none" id="acadExamMobile">
    <div class="acad-exam-mobile__top">
        <a href="{{ route('academics.exams.index') }}" class="acad-exam-mobile__back" aria-label="Exit exam">
            <i class="fas fa-times" aria-hidden="true"></i>
        </a>
        <div class="acad-exam-mobile__meta">
            <p class="acad-exam-mobile__title mb-0">{{ Str::limit($exam->title, 42) }}</p>
            <p class="acad-exam-mobile__sub mb-0" id="acadExamProgress">Question 1 of {{ $questionCount }}</p>
        </div>
        @if($attemptExpiresAt)
            <div class="acad-exam-mobile__timer" id="examTimerMobile" role="status">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span id="examTimerLabelMobile">—</span>
            </div>
        @endif
    </div>

    <div class="acad-exam-mobile__progress" aria-hidden="true">
        <div class="acad-exam-mobile__progress-fill" id="acadExamProgressBar" style="width: {{ $questionCount > 0 ? round(100 / $questionCount) : 0 }}%"></div>
    </div>

    <form method="post" action="{{ route('academics.exams.submit', [$exam, $attempt]) }}" id="examTakeFormMobile" class="acad-exam-mobile__form">
        @csrf
        @foreach($questions as $q)
            <section class="acad-exam-step {{ $loop->first ? 'is-active' : '' }}"
                     data-step="{{ $loop->index }}"
                     data-question-id="{{ $q->id }}"
                     data-required="{{ $q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI ? '0' : '1' }}"
                     data-multi="{{ $q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI ? '1' : '0' }}">
                <div class="acad-exam-step__card">
                    <div class="acad-exam-step__badge">
                        Q{{ $loop->iteration }} · {{ $q->points }} pt{{ $q->points == 1 ? '' : 's' }}
                        @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                            · Multi-select
                        @endif
                    </div>
                    <h2 class="acad-exam-step__question">{!! nl2br(e($q->body)) !!}</h2>
                    <div class="acad-exam-step__options">
                        @if($q->question_type === \App\Modules\Academics\Models\AcademicExamQuestion::TYPE_MCQ_MULTI)
                            @foreach($q->options as $opt)
                                <label class="acad-exam-option">
                                    <input type="checkbox" name="answers[{{ $q->id }}][]" value="{{ $opt->id }}">
                                    <span class="acad-exam-option__box" aria-hidden="true"></span>
                                    <span class="acad-exam-option__text">{{ $opt->body }}</span>
                                </label>
                            @endforeach
                        @else
                            @foreach($q->options as $opt)
                                <label class="acad-exam-option">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}">
                                    <span class="acad-exam-option__box acad-exam-option__box--radio" aria-hidden="true"></span>
                                    <span class="acad-exam-option__text">{{ $opt->body }}</span>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
        @endforeach
    </form>

    <div class="acad-exam-mobile__dock">
        <button type="button" class="acad-exam-dock-btn acad-exam-dock-btn--ghost" id="acadExamPrev" disabled>
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            <span>Back</span>
        </button>
        <button type="button" class="acad-exam-dock-btn acad-exam-dock-btn--primary" id="acadExamNext">
            <span>Next</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
        <button type="submit" form="examTakeFormMobile" class="acad-exam-dock-btn acad-exam-dock-btn--submit d-none" id="acadExamSubmit">
            <i class="fas fa-check" aria-hidden="true"></i>
            <span>Submit</span>
        </button>
    </div>
</div>

@if($attemptExpiresAt)
<script>
(function () {
    var endMs = Date.parse(@json($attemptExpiresAt->toIso8601String()));
    if (Number.isNaN(endMs)) return;
    var fired = false;
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var left = endMs - Date.now();
        var labels = [
            document.getElementById('examTimerLabelDesktop'),
            document.getElementById('examTimerLabelMobile')
        ];
        var banners = [
            document.getElementById('examTimerBannerDesktop'),
            document.getElementById('examTimerMobile')
        ];
        if (left <= 0) {
            labels.forEach(function (l) { if (l) l.textContent = '0:00'; });
            banners.forEach(function (b) { if (b) b.classList.add('is-urgent'); });
            if (!fired) {
                fired = true;
                var form = document.getElementById('examTakeFormMobile') || document.getElementById('examTakeFormDesktop');
                if (form) form.requestSubmit();
            }
            return;
        }
        var s = Math.floor(left / 1000);
        var text = Math.floor(s / 60) + ':' + pad(s % 60);
        labels.forEach(function (l) { if (l) l.textContent = text; });
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
<script src="{{ asset('js/academics-exam-mobile.js') }}?v=20260603" defer></script>
@endsection

@extends('auth::layout')

@section('title', 'Taking: '.$exam->title)
@section('page-title', 'Quiz')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="mb-3 small text-muted">
        <strong>{{ $exam->title }}</strong> · Started {{ $attempt->started_at?->format('M j, H:i') }}
        @if($exam->duration_minutes)
            · Allowance {{ $exam->duration_minutes }} min
        @endif
    </div>
    @if($attemptExpiresAt)
        <div id="examTimerBanner" class="alert alert-warning py-2 small mb-3 d-flex flex-wrap align-items-center gap-2" role="status">
            <span>Time remaining:</span>
            <strong id="examTimerLabel">—</strong>
        </div>
    @endif

    <form method="post" action="{{ route('academics.exams.submit', [$exam, $attempt]) }}" id="examTakeForm">
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
                                <input class="form-check-input" type="checkbox" name="answers[{{ $q->id }}][]" id="o{{ $opt->id }}" value="{{ $opt->id }}">
                                <label class="form-check-label" for="o{{ $opt->id }}">{{ $opt->body }}</label>
                            </div>
                        @endforeach
                    @else
                        @foreach($q->options as $opt)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="answers[{{ $q->id }}]" id="o{{ $opt->id }}" value="{{ $opt->id }}" required>
                                <label class="form-check-label" for="o{{ $opt->id }}">{{ $opt->body }}</label>
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
@if($attemptExpiresAt)
<script>
(function () {
    var endMs = Date.parse(@json($attemptExpiresAt->toIso8601String()));
    var form = document.getElementById('examTakeForm');
    var label = document.getElementById('examTimerLabel');
    var banner = document.getElementById('examTimerBanner');
    if (!form || !label || Number.isNaN(endMs)) return;
    var fired = false;
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function tick() {
        var left = endMs - Date.now();
        if (left <= 0) {
            label.textContent = '0:00';
            if (banner) banner.classList.replace('alert-warning', 'alert-danger');
            if (!fired) {
                fired = true;
                form.requestSubmit();
            }
            return;
        }
        var s = Math.floor(left / 1000);
        var m = Math.floor(s / 60);
        var r = s % 60;
        label.textContent = m + ':' + pad(r);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
@endsection

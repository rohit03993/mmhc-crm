@extends('auth::layout')

@section('title', 'Result: '.$exam->title)
@section('page-title', 'Quiz result')

@section('content')
@php
    use App\Modules\Academics\Models\AcademicExamQuestion;
@endphp
<div class="container-fluid py-3 py-md-4 acad-mobile-page" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none mb-3">
        <p class="acad-m-hero__label">Quiz result</p>
        <h2 class="acad-m-hero__title">{{ $exam->title }}</h2>
        <p class="acad-m-hero__lede mb-0">
            <span class="fw-bold text-primary">{{ number_format((float) $attempt->score, 2) }}</span>
            / {{ number_format($maxPoints, 2) }} points
            @if($maxPoints > 0)
                · {{ number_format(((float) $attempt->score / $maxPoints) * 100, 1) }}%
            @endif
        </p>
    </div>
    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning py-2 small">{{ session('warning') }}</div>
    @endif

    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold mb-2">{{ $exam->title }}</h1>
            <p class="mb-1">
                <span class="display-6 fw-bold text-primary">{{ number_format((float) $attempt->score, 2) }}</span>
                <span class="text-muted"> / {{ number_format($maxPoints, 2) }}</span>
            </p>
            @if($maxPoints > 0)
                <p class="small text-muted mb-0">{{ number_format(((float) $attempt->score / $maxPoints) * 100, 1) }}% correct by points</p>
            @endif
            @if($canStaffView)
                <p class="small mt-2 mb-0">Student: <strong>{{ $attempt->user->name }}</strong> ({{ $attempt->user->email }})</p>
            @endif
            <p class="small text-muted mt-2 mb-0">Submitted {{ $attempt->submitted_at?->format('M j, Y H:i') }}</p>
        </div>
    </div>

    <h2 class="h6 fw-bold mb-3">Review</h2>
    @foreach($attempt->exam->questions as $q)
        @php
            $ans = $attempt->answers->firstWhere('question_id', $q->id);
            $isCorrect = $examScoring->isAnswerCorrect($q, $ans);
            $correctOpts = $q->options->where('is_correct', true);
            $correctBodies = $correctOpts->pluck('body');
        @endphp
        <div class="card border rounded-3 mb-3">
            <div class="card-body p-3">
                <p class="fw-medium mb-2">{{ $loop->iteration }}. {!! nl2br(e($q->body)) !!}</p>
                @if($q->question_type === AcademicExamQuestion::TYPE_MCQ_MULTI)
                    <p class="small text-muted mb-2">Multiple select · {{ $q->points }} point(s)</p>
                @else
                    <p class="small text-muted mb-2">Single choice · {{ $q->points }} point(s)</p>
                @endif

                @if(! $ans)
                    <span class="badge text-bg-secondary">No answer</span>
                @elseif($isCorrect)
                    <span class="badge text-bg-success">Correct (+{{ $q->points }} pt)</span>
                @else
                    <span class="badge text-bg-danger">Incorrect</span>
                @endif

                @if($q->question_type === AcademicExamQuestion::TYPE_MCQ_MULTI)
                    @php
                        $selectedIds = collect($ans?->selected_option_ids ?? [])->map(fn ($id) => (int) $id)->all();
                        $selectedBodies = collect($selectedIds)
                            ->map(fn ($id) => $q->options->firstWhere('id', $id)?->body)
                            ->filter();
                    @endphp
                    <p class="small mb-1 mt-2"><span class="text-muted">Your selections:</span>
                        @if($selectedBodies->isEmpty())
                            <em class="text-muted">None</em>
                        @else
                            {{ $selectedBodies->map(fn ($b) => Str::limit($b, 120))->join('; ') }}
                        @endif
                    </p>
                    @if(! $isCorrect || ! $ans)
                        <p class="small text-success mb-0"><span class="text-muted">Correct:</span> {{ $correctBodies->map(fn ($b) => Str::limit($b, 120))->join('; ') }}</p>
                    @endif
                @else
                    @php $picked = $ans?->option; @endphp
                    @if($picked && ! $isCorrect)
                        <p class="small text-muted mb-0 mt-2">Your answer: {{ Str::limit($picked->body, 120) }}</p>
                    @endif
                    @if($ans && ! $isCorrect)
                        @php $firstCorrect = $correctOpts->first(); @endphp
                        @if($firstCorrect)
                            <p class="small text-success mb-0 mt-1">Correct: {{ Str::limit($firstCorrect->body, 120) }}</p>
                        @endif
                    @endif
                @endif

                @if(filled($q->explanation))
                    <div class="alert alert-light border mt-3 mb-0 small">
                        <strong class="d-block mb-1">Explanation</strong>
                        {!! nl2br(e($q->explanation)) !!}
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-outline-secondary rounded-pill">Back to exam</a>
        <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-primary rounded-pill">All exams</a>
    </div>
</div>
@endsection

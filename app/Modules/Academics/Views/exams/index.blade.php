@extends('auth::layout')

@section('title', 'Exams — Academics')
@section('page-title', 'Exams')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold text-dark">Quizzes &amp; exams</h1>
            <p class="small text-muted mb-0">
                @if($viewerRole === 'student')
                    Published assessments for your cohort (upcoming, open, and ended). You can start an attempt only while the time window is open.
                @elseif(in_array($viewerRole, ['super_admin', 'admin'], true))
                    All institutions — create, publish, and view results.
                @else
                    Exams for your college scope. CRM admins see every exam.
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(!empty($viewerCanCreate))
                <a href="{{ route('academics.exams.create') }}" class="btn btn-primary btn-sm rounded-pill"><i class="fas fa-plus me-1"></i>New exam</a>
            @endif
            <a href="{{ route('academics.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <form method="get" action="{{ route('academics.exams.index') }}" class="card border shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small text-muted mb-0">Search title</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Keyword…">
                </div>
                @if(isset($filterInstitutions) && $filterInstitutions->isNotEmpty())
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-0">College</label>
                    <select name="institution_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($filterInstitutions as $inst)
                            <option value="{{ $inst->id }}" @selected((string)($filters['institution_id'] ?? '') === (string) $inst->id)>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                @if(($viewerRole ?? '') !== 'student')
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-0">Publish</label>
                    <select name="publish" class="form-select form-select-sm">
                        <option value="all" @selected(($filters['publish'] ?? 'all') === 'all')>All</option>
                        <option value="published" @selected(($filters['publish'] ?? '') === 'published')>Live (published)</option>
                        <option value="draft" @selected(($filters['publish'] ?? '') === 'draft')>Draft</option>
                    </select>
                </div>
                @endif
                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-0">Time window</label>
                    <select name="window" class="form-select form-select-sm">
                        <option value="all" @selected(($filters['window'] ?? 'all') === 'all')>Any</option>
                        <option value="upcoming" @selected(($filters['window'] ?? '') === 'upcoming')>Not started</option>
                        <option value="open" @selected(($filters['window'] ?? '') === 'open')>Accepting now</option>
                        <option value="ended" @selected(($filters['window'] ?? '') === 'ended')>Ended</option>
                    </select>
                </div>
                <div class="col-6 col-md-4 col-lg-1">
                    <label class="form-label small text-muted mb-0">Per page</label>
                    <select name="per_page" class="form-select form-select-sm">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" @selected((int)($filters['per_page'] ?? 20) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill">Apply</button>
                    <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
                </div>
            </div>
            @if(($viewerRole ?? '') === 'student')
                <p class="small text-muted mb-0 mt-2">Showing published quizzes you can be eligible for; narrow by time window or search.</p>
            @endif
        </div>
    </form>

    @if($exams->isEmpty())
        <div class="alert alert-light border text-muted mb-0">
            No exams to show yet. @if(!empty($viewerCanCreate))Create one to get started.@endif
        </div>
    @else
        <div class="table-responsive card border shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Audience</th>
                        <th>College</th>
                        <th>Created by</th>
                        <th>Schedule</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exams as $exam)
                        <tr>
                            <td class="ps-3 fw-medium text-dark">
                                <a href="{{ route('academics.exams.show', $exam) }}" class="text-dark text-decoration-none">{{ $exam->title }}</a>
                                @php $sched = $exam->scheduleListState(); @endphp
                                @if($sched === 'draft')
                                    <span class="badge text-bg-secondary ms-1 small">Draft</span>
                                @elseif($sched === 'upcoming')
                                    <span class="badge text-bg-warning text-dark ms-1 small">Upcoming</span>
                                @elseif($sched === 'ended')
                                    <span class="badge text-bg-dark ms-1 small">Ended</span>
                                @else
                                    <span class="badge text-bg-success ms-1 small">Open</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                <span class="text-uppercase">{{ str_replace('_', ' ', $exam->audience_type) }}</span>
                                @if($exam->subject)
                                    <br><span class="text-dark">{{ $exam->subject->name }}</span>
                                @elseif($exam->batch)
                                    <br><span class="text-dark">Batch: {{ $exam->batch->name }}</span>
                                @endif
                            </td>
                            <td class="small">{{ $exam->institution->name ?? '—' }}</td>
                            <td class="small">
                                @if($exam->creator)
                                    @php
                                        $c = $exam->creator;
                                        $creatorLabel = \Illuminate\Support\Str::of((string) ($c->name ?? ''))->trim()->isNotEmpty()
                                            ? $c->name
                                            : (\Illuminate\Support\Str::of((string) ($c->email ?? ''))->trim()->isNotEmpty() ? $c->email : 'User #'.$c->id);
                                    @endphp
                                    @if(in_array($viewerRole, ['super_admin', 'admin', 'institution_admin', 'faculty'], true))
                                        <a href="{{ route('academics.people.show', $exam->creator) }}" class="text-dark text-decoration-none">{{ $creatorLabel }}</a>
                                    @else
                                        {{-- Students cannot open academics people profiles; show name only. --}}
                                        <span class="text-dark">{{ $creatorLabel }}</span>
                                    @endif
                                    @if($exam->creator->role === 'faculty')
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Faculty</span>
                                    @elseif($exam->creator->role === 'institution_admin')
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Admin</span>
                                    @elseif(in_array($exam->creator->role, ['super_admin', 'admin'], true))
                                        <span class="badge bg-light text-dark border ms-1" style="font-size:0.65rem;">Platform</span>
                                    @endif
                                    @if($viewerRole !== 'student' && \Illuminate\Support\Str::of((string) ($exam->creator->name ?? ''))->trim()->isNotEmpty() && \Illuminate\Support\Str::of((string) ($exam->creator->email ?? ''))->trim()->isNotEmpty())
                                        <br><span class="text-muted" style="font-size:0.8rem;">{{ $exam->creator->email }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                @if($exam->opens_at || $exam->closes_at)
                                    {{ $exam->opens_at?->format('M j, H:i') ?? '—' }}
                                    →
                                    {{ $exam->closes_at?->format('M j, H:i') ?? '—' }}
                                @else
                                    Open schedule
                                @endif
                            </td>
                            <td class="pe-3 text-nowrap">
                                <a href="{{ route('academics.exams.show', $exam) }}" class="btn btn-sm btn-outline-primary rounded-pill">Open</a>
                                @if(in_array($viewerRole, ['super_admin', 'admin'], true))
                                    <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-sm btn-outline-success rounded-pill">Results</a>
                                @elseif(in_array($viewerRole, ['institution_admin', 'faculty'], true))
                                    @if(app(\App\Modules\Academics\Services\ExamAccessService::class)->canManage(auth()->user(), $exam))
                                        <a href="{{ route('academics.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                        <a href="{{ route('academics.exams.attempts', $exam) }}" class="btn btn-sm btn-outline-success rounded-pill">Results</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-1 py-2">
            {{ $exams->onEachSide(1)->links('pagination.modern') }}
        </div>
    @endif
</div>
@endsection

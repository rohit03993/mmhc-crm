@extends('auth::layout')

@section('title', 'Academics - MeD Miracle Academic CRM')
@section('page-title', 'Academics')

@section('head')
<style>
    /* Light hero aligned with CRM (white main area, primary blue accents) */
    .academics-dash-hero {
        position: relative;
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 1rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .academics-dash-hero::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
        border-radius: 1rem 0 0 1rem;
    }
    .academics-dash-hero__inner {
        padding: 1.5rem 1.5rem 1.5rem 1.35rem;
    }
    @media (min-width: 768px) {
        .academics-dash-hero__inner { padding: 1.75rem 2rem 1.75rem 1.5rem; }
    }
    .academics-dash-hero__kicker {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .academics-dash-hero__title {
        font-size: clamp(1.35rem, 2.2vw, 1.65rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 0.75rem;
    }
    .academics-dash-hero__lede {
        font-size: 0.9375rem;
        line-height: 1.55;
        color: #475569;
        max-width: 40rem;
        margin-bottom: 0;
    }
    .academics-quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1.25rem;
    }
    .academics-quick-links .btn {
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 500;
        padding: 0.4rem 1rem;
    }
    .academics-stat-card {
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 0.875rem;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .academics-stat-card:hover {
        border-color: rgba(37, 99, 235, 0.25);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
    }
    .academics-stat-card .academics-stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .academics-stat-card .academics-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .academics-overview-card {
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
    }
    .academics-overview-card .table {
        --bs-table-border-color: rgba(148, 163, 184, 0.2);
    }
    .academics-overview-card thead th {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        border-bottom-width: 1px;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
    }
    .academics-overview-card tbody td {
        padding-top: 0.9rem;
        padding-bottom: 0.9rem;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
@php $academicsMobileShell = \App\Modules\Academics\Support\AcademicsMobileUi::enabledFor(auth()->user()); @endphp
@if($academicsMobileShell)
<div class="mobile-app-container academics-app-content acad-page-wrap" data-mmhc-ptr>
    @include('academics::partials.mobile-dashboard-header')
@endif
<div class="container-fluid py-3 py-md-4 @if($academicsMobileShell) px-0 px-md-3 @endif">
    @if(auth()->user()->role === 'student' && auth()->user()->academic_enrollment_status === 'pending')
        <div class="alert alert-warning d-none d-md-block">
            <strong>Awaiting institute approval.</strong>
            College assignments, attendance, and SPI unlock after {{ auth()->user()->academicInstitution->name ?? 'your institute' }} approves your enrollment.
            You can still browse and join <a href="{{ route('academics.open-classrooms.index') }}" class="alert-link">open classrooms</a> and request mentors from the sidebar.
        </div>
    @elseif(auth()->user()->role === 'student' && auth()->user()->academic_enrollment_status === 'rejected')
        <div class="alert alert-danger">Your enrollment request was not approved. Contact your institute admin.</div>
    @endif
    @if(($enrollmentPendingCount ?? 0) > 0 && auth()->user()->role === 'institution_admin')
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 d-none d-md-flex">
            <span><strong>{{ $enrollmentPendingCount }}</strong> student enrollment request(s) awaiting review.</span>
            <a href="{{ route('academics.enrollments.index') }}" class="btn btn-sm btn-primary">Review now</a>
        </div>
    @endif
    <div class="academics-dash-hero">
        <div class="academics-dash-hero__inner">
            <p class="academics-dash-hero__kicker mb-0">Academic workspace</p>
            <h1 class="academics-dash-hero__title">Dashboard</h1>
        @if(auth()->user()->role === 'admin')
            <p class="academics-dash-hero__lede">Provision colleges and institute admins. Open any college below for a read-only overview (no batch or curriculum edits).</p>
            <div class="academics-quick-links">
                <a href="{{ route('academics.institutions.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-university me-1"></i>Institutes &amp; codes</a>
                <a href="{{ route('admin.users', ['segment' => 'institute_admins']) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-shield me-1"></i>Institute admins</a>
                <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-alt me-1"></i>Reports (read-only)</a>
                <a href="{{ route('community.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-comments me-1"></i>Community</a>
            </div>
        @elseif(auth()->user()->role === 'institution_admin')
            <p class="academics-dash-hero__lede">Manage batches, subjects, faculty, attendance, and your institution’s students.</p>
            <div class="academics-quick-links">
                @if(auth()->user()->academic_institution_id)
                    <a href="{{ route('academics.institutions.show', auth()->user()->academic_institution_id) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-university me-1"></i>College overview</a>
                @endif
                <a href="{{ route('academics.enrollments.index') }}" class="btn btn-warning btn-sm"><i class="fas fa-user-clock me-1"></i>Pending enrollments @if(($enrollmentPendingCount ?? 0) > 0)({{ $enrollmentPendingCount }})@endif</a>
                <a href="{{ route('academics.students.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-graduate me-1"></i>Students</a>
                <a href="{{ route('academics.batches.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-layer-group me-1"></i>Batches</a>
                <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-question-circle me-1"></i>Exams</a>
                <a href="{{ route('academics.subjects.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-book me-1"></i>Subjects</a>
                <a href="{{ route('academics.faculty.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-chalkboard-teacher me-1"></i>Faculty</a>
                <a href="{{ route('academics.attendance.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-bar me-1"></i>Reports</a>
                <a href="{{ route('academics.reports.show', ['type' => 'student_submission', 'institution_id' => auth()->user()->academic_institution_id]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-graduate me-1"></i>Student report</a>
            </div>
        @elseif(auth()->user()->role === 'faculty')
            @if(auth()->user()->is_open_teacher && !auth()->user()->academic_institution_id)
                <p class="academics-dash-hero__lede">Run open classrooms for any student — share notes, set assignments, and review submissions.</p>
            @else
                <p class="academics-dash-hero__lede">Run topics and assignments, review submissions, open classrooms, and track your students.</p>
            @endif
            <div class="academics-quick-links">
                @if(($openClassroom['enabled'] ?? false) && (($openClassroom['owned_count'] ?? 0) > 0 || auth()->user()->is_open_teacher))
                    <a href="{{ route('academics.open-classrooms.create') }}" class="btn btn-success btn-sm"><i class="fas fa-door-open me-1"></i>Open classroom</a>
                    <a href="{{ route('academics.open-classrooms.index', ['tab' => 'mine']) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-chalkboard me-1"></i>My classrooms @if(($openClassroom['owned_count'] ?? 0) > 0)({{ $openClassroom['owned_count'] }})@endif</a>
                @elseif($openClassroom['enabled'] ?? false)
                    <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-outline-success btn-sm"><i class="fas fa-door-open me-1"></i>Open classrooms</a>
                @endif
                @if(auth()->user()->academic_institution_id)
                    <a href="{{ route('academics.institutions.show', auth()->user()->academic_institution_id) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-university me-1"></i>College</a>
                @endif
                @if(auth()->user()->academic_institution_id)
                <a href="{{ route('academics.topics.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-list-ul me-1"></i>Topics</a>
                <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-question-circle me-1"></i>Exams</a>
                <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-tasks me-1"></i>Assignments</a>
                <a href="{{ route('academics.attendance.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
                <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-user-graduate me-1"></i>Student report</a>
                @endif
            </div>
        @elseif(auth()->user()->role === 'student')
            <p class="academics-dash-hero__lede">College work, open classrooms, quizzes, learning resources, and SPI — all on one platform after membership.</p>
            <div class="academics-quick-links">
                @if($openClassroom['enabled'] ?? false)
                    <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-success btn-sm"><i class="fas fa-door-open me-1"></i>Open classrooms @if(($openClassroom['joined_count'] ?? 0) > 0)({{ $openClassroom['joined_count'] }} joined)@endif</a>
                @endif
                <a href="{{ route('academics.my-assignments') }}" class="btn btn-primary btn-sm"><i class="fas fa-tasks me-1"></i>My assignments</a>
                <a href="{{ route('academics.learning-resources') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-photo-video me-1"></i>Learning resources</a>
                <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-question-circle me-1"></i>Exams</a>
                <a href="{{ route('academics.attendance.my') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar me-1"></i>My attendance</a>
            </div>
        @else
            <p class="academics-dash-hero__lede text-muted mb-0">Welcome to the Academic module.</p>
        @endif
        </div>
    </div>

    @include('academics::partials.dashboard-insights', ['insights' => $insights])

    @include('academics::partials.open-classroom-dashboard', ['openClassroom' => $openClassroom ?? ['enabled' => false]])

    {{-- STUDENT --}}
    @if(auth()->user()->role === 'student')
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-primary bg-opacity-10 p-2 me-2"><i class="fas fa-chart-line text-primary"></i></span>
                        SPI (Student Progress)
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $spi }}<span class="fs-6 text-muted">%</span></p>
                    <small class="text-muted d-block">Counts toward profile when submitted + all shared mentors have rated.</small>
                    @if(isset($spiBreakdown) && $spiBreakdown['total'] > 0)
                        <ul class="small text-muted mb-0 mt-2 ps-3">
                            <li>{{ $spiBreakdown['verified'] }} fully credited</li>
                            @if($spiBreakdown['submitted_pending_mentor'] > 0)
                                <li class="text-warning">{{ $spiBreakdown['submitted_pending_mentor'] }} awaiting mentor rating</li>
                            @endif
                            @if($spiBreakdown['not_submitted'] > 0)
                                <li>{{ $spiBreakdown['not_submitted'] }} not submitted</li>
                            @endif
                        </ul>
                    @endif
                    @if(($mentorCount ?? 0) > 0)
                        <p class="small mb-0 mt-2"><a href="{{ route('academics.mentorship.index') }}">{{ $mentorCount }} active mentor(s)</a></p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-secondary bg-opacity-10 p-2 me-2"><i class="fas fa-tasks text-secondary"></i></span>
                        My Assignments
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $myAssignmentsCount }}</p>
                    @if($myPendingCount > 0)
                        <p class="small text-warning mb-2">{{ $myPendingCount }} pending</p>
                    @endif
                    <a href="{{ route('academics.my-assignments') }}" class="btn btn-primary btn-sm mt-auto align-self-start">View</a>
                </div>
            </div>
        </div>
        @if($openClassroom['enabled'] ?? false)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-success border-opacity-25">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-success bg-opacity-10 p-2 me-2"><i class="fas fa-door-open text-success"></i></span>
                        Open classrooms
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $openClassroom['joined_count'] ?? 0 }}</p>
                    @if(($openClassroom['pending_tasks'] ?? 0) > 0)
                        <p class="small text-warning mb-2">{{ $openClassroom['pending_tasks'] }} task(s) pending</p>
                    @else
                        <p class="small text-muted mb-2">Joined classrooms</p>
                    @endif
                    <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-success btn-sm mt-auto align-self-start rounded-pill">Browse</a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- FACULTY --}}
    @if(auth()->user()->role === 'faculty')
    <div class="row g-3">
        @if(auth()->user()->academic_institution_id)
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-primary bg-opacity-10 p-2 me-2"><i class="fas fa-chart-line text-primary"></i></span>
                        <span class="small">FPI (Faculty Score)</span>
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $fpi }}<span class="fs-6 text-muted">%</span></p>
                    <small class="text-muted d-block">Teaching + mentorship on profile</small>
                    @if(isset($fpiBreakdown))
                        <ul class="small text-muted mb-0 mt-2 ps-3">
                            <li>Teaching: {{ $fpiBreakdown['teaching_percent'] }}% · Mentorship: {{ $fpiBreakdown['mentorship_percent'] }}%</li>
                            <li>{{ $fpiBreakdown['active_mentees'] }} students chose you as mentor</li>
                            <li>{{ $fpiBreakdown['reviews_given'] }} ratings given (+8 pts each)</li>
                            @if($fpiBreakdown['pending_reviews'] > 0)
                                <li class="text-warning">{{ $fpiBreakdown['pending_reviews'] }} awaiting your rating</li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-info bg-opacity-10 p-2 me-2"><i class="fas fa-hands-helping text-info"></i></span>
                        <span class="small">Mentorship impact</span>
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $fpiBreakdown['mentorship_percent'] ?? 0 }}<span class="fs-6 text-muted">%</span></p>
                    <small class="text-muted">+10 pts per mentee · +8 pts per rating</small>
                    @if(isset($fpiBreakdown))
                        <ul class="small text-muted mb-0 mt-2 ps-3">
                            <li>Mentee pts: {{ $fpiBreakdown['mentee_score'] }}/50</li>
                            <li>Rating pts: {{ $fpiBreakdown['review_score'] }}/40</li>
                        </ul>
                    @endif
                    <a href="{{ route('academics.mentorship.index') }}" class="btn btn-outline-info btn-sm mt-auto align-self-start">Rate submissions</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-success bg-opacity-10 p-2 me-2"><i class="fas fa-user-graduate text-success"></i></span>
                        My Students
                    </h5>
                    <p class="card-text display-6 mb-2">{{ $myStudentsCount }}</p>
                    <p class="small text-muted mb-2">{{ $fpiBreakdown['active_mentees'] ?? 0 }} cross-institute mentees</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        <a href="{{ route('academics.students.index') }}" class="btn btn-outline-secondary btn-sm">Student list</a>
                        <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-outline-primary btn-sm">Student report</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-secondary bg-opacity-10 p-2 me-2"><i class="fas fa-list-ul text-secondary"></i></span>
                        My Topics
                    </h5>
                    <p class="card-text display-6 mb-2">{{ $topicsCount }}</p>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-outline-primary btn-sm mt-auto align-self-start">View</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-info bg-opacity-10 p-2 me-2"><i class="fas fa-tasks text-info"></i></span>
                        My Assignments
                    </h5>
                    <p class="card-text display-6 mb-2">{{ $assignmentsCount }}</p>
                    <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-primary btn-sm mt-auto align-self-start">View</a>
                </div>
            </div>
        </div>
        @endif
        @if(($openClassroom['enabled'] ?? false) && (auth()->user()->is_open_teacher || ($openClassroom['owned_count'] ?? 0) > 0))
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm border-success border-opacity-25">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-success bg-opacity-10 p-2 me-2"><i class="fas fa-door-open text-success"></i></span>
                        <span class="small">Open classrooms</span>
                    </h5>
                    <p class="card-text display-6 mb-2">{{ $openClassroom['owned_count'] ?? 0 }}</p>
                    <p class="small text-muted mb-2">{{ $openClassroom['joined_count'] ?? 0 }} joined as member</p>
                    <a href="{{ route('academics.open-classrooms.index', ['tab' => 'mine']) }}" class="btn btn-success btn-sm mt-auto align-self-start rounded-pill">Manage</a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- INSTITUTION ADMIN --}}
    @if(auth()->user()->role === 'institution_admin')
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-info bg-opacity-10 p-2 me-2"><i class="fas fa-university text-info"></i></span>
                        ICR (Institution Readiness)
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $icr }}<span class="fs-6 text-muted">%</span></p>
                    <small class="text-muted">Topic completion</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-success bg-opacity-10 p-2 me-2"><i class="fas fa-user-graduate text-success"></i></span>
                        Students
                    </h5>
                    <p class="card-text display-6 mb-2">{{ $institutionStudentsCount }}</p>
                    <a href="{{ route('academics.reports.show', ['type' => 'student_submission', 'institution_id' => auth()->user()->academic_institution_id]) }}" class="btn btn-outline-primary btn-sm mt-auto align-self-start">Student report</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Batches</p>
                    <p class="academics-stat-value mb-2">{{ $batchesCount }}</p>
                    <a href="{{ route('academics.batches.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Subjects</p>
                    <p class="academics-stat-value mb-2">{{ $subjectsCount }}</p>
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Topics</p>
                    <p class="academics-stat-value mb-2">{{ $topicsCount }}</p>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Assignments</p>
                    <p class="academics-stat-value mb-2">{{ $assignmentsCount }}</p>
                    <a href="{{ route('academics.assignments.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Manage</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- CRM ADMIN (read-only platform overview) --}}
    @if(auth()->user()->role === 'admin')
    {{-- ICR by Institution table with Students column (Change 1) --}}
    @if($institutionsWithIcrPaginator && $institutionsWithIcrPaginator->total() > 0)
    <div class="card academics-overview-card mb-4">
        <div class="card-body p-0">
            <div class="px-3 px-md-4 pt-3 pb-2 border-bottom border-light">
                <h2 class="h5 mb-0 fw-bold d-flex align-items-center text-dark">
                    <span class="rounded-3 bg-primary bg-opacity-10 p-2 me-2 text-primary"><i class="fas fa-chart-bar"></i></span>
                    Institutions – Students &amp; ICR
                </h2>
                <p class="small text-muted mb-0 mt-2">Readiness and enrollment by college.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 ps-md-4">Institution</th>
                            <th class="text-end">Students</th>
                            <th class="text-end pe-3 pe-md-4" title="Institution Clinical Readiness: share of topics marked complete for this college.">ICR %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($institutionsWithIcrPaginator as $row)
                        <tr>
                            <td class="ps-3 ps-md-4 fw-medium text-dark">
                                <a href="{{ route('academics.institutions.show', $row['id']) }}" class="text-primary text-decoration-none fw-semibold">{{ $row['name'] }}</a>
                            </td>
                            <td class="text-end text-muted">{{ $row['students'] ?? 0 }}</td>
                            <td class="text-end pe-3 pe-md-4 fw-semibold text-dark">{{ $row['icr'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-3 px-md-4 pb-1">
                {{ $institutionsWithIcrPaginator->links('pagination.modern') }}
            </div>
            <div class="px-3 px-md-4 py-3 border-top border-light bg-light bg-opacity-50 rounded-bottom">
                <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="fas fa-user-graduate me-1"></i>Student report (all)</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Metric tiles --}}
    <div class="row g-3">
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Total students</p>
                    <p class="academics-stat-value mb-2">{{ $totalStudentsCount }}</p>
                    <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Report</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Institutions</p>
                    <p class="academics-stat-value mb-2">{{ $institutionsCount }}</p>
                    <a href="{{ route('academics.institutions.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Batches</p>
                    <p class="academics-stat-value mb-2">{{ $batchesCount }}</p>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Reports</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Subjects</p>
                    <p class="academics-stat-value mb-2">{{ $subjectsCount }}</p>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Reports</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Topics</p>
                    <p class="academics-stat-value mb-2">{{ $topicsCount }}</p>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Reports</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card academics-stat-card h-100 shadow-none bg-white">
                <div class="card-body text-center py-4 px-2 d-flex flex-column">
                    <p class="academics-stat-label mb-2">Assignments</p>
                    <p class="academics-stat-value mb-2">{{ $assignmentsCount }}</p>
                    <a href="{{ route('academics.reports.index') }}" class="btn btn-link btn-sm p-0 mt-auto fw-semibold">Reports</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@if($academicsMobileShell)
</div>
@endif
@endsection

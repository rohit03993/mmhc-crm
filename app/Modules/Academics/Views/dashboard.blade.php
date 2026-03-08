@extends('auth::layout')

@section('title', 'Academics - MeD Miracle Academic CRM')
@section('page-title', 'Academics')

@section('content')
<div class="container-fluid py-3 py-md-4">
    {{-- Role-specific welcome (Change 5) --}}
    <div class="mb-4">
        <h2 class="h4 mb-2">Academic Dashboard</h2>
        @if(auth()->user()->role === 'super_admin')
            <p class="text-muted mb-0">Manage institutions and view all colleges’ progress and students.</p>
        @elseif(auth()->user()->role === 'institution_admin')
            <p class="text-muted mb-0">Manage batches, subjects, and view your institution’s students and reports.</p>
        @elseif(auth()->user()->role === 'faculty')
            <p class="text-muted mb-0">Manage your topics, assignments, and view your students and their reports.</p>
        @elseif(auth()->user()->role === 'student')
            <p class="text-muted mb-0">View your assignments, submit work, and track your progress.</p>
        @else
            <p class="text-muted mb-0">Welcome to the Academic module.</p>
        @endif
    </div>

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
                    <small class="text-muted">Assignments submitted</small>
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
    </div>
    @endif

    {{-- FACULTY --}}
    @if(auth()->user()->role === 'faculty')
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title d-flex align-items-center mb-2">
                        <span class="rounded-circle bg-primary bg-opacity-10 p-2 me-2"><i class="fas fa-chart-line text-primary"></i></span>
                        <span class="small">FPI</span>
                    </h5>
                    <p class="card-text display-6 mb-1">{{ $fpi }}<span class="fs-6 text-muted">%</span></p>
                    <small class="text-muted">Topic completion</small>
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
                    <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-outline-primary btn-sm mt-auto align-self-start">Student report</a>
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
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Batches</p>
                    <p class="h4 mb-0">{{ $batchesCount }}</p>
                    <a href="{{ route('academics.batches.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Subjects</p>
                    <p class="h4 mb-0">{{ $subjectsCount }}</p>
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Topics</p>
                    <p class="h4 mb-0">{{ $topicsCount }}</p>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Assignments</p>
                    <p class="h4 mb-0">{{ $assignmentsCount }}</p>
                    <a href="{{ route('academics.assignments.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- SUPER ADMIN --}}
    @if(auth()->user()->role === 'super_admin')
    {{-- ICR by Institution table with Students column (Change 1) --}}
    @if($institutionsWithIcr->isNotEmpty())
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title d-flex align-items-center mb-3">
                <span class="rounded-circle bg-info bg-opacity-10 p-2 me-2"><i class="fas fa-chart-bar text-info"></i></span>
                Institutions – Students &amp; ICR
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Institution</th>
                            <th class="text-end">Students</th>
                            <th class="text-end">ICR %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($institutionsWithIcr as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-end">{{ $row['students'] ?? 0 }}</td>
                            <td class="text-end">{{ $row['icr'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-primary btn-sm">Student report (all)</a>
            </div>
        </div>
    </div>
    @endif

    {{-- Cards: Total Students, Institutions, Batches, etc. --}}
    <div class="row g-3">
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Total students</p>
                    <p class="h4 mb-0">{{ $totalStudentsCount }}</p>
                    <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-link btn-sm p-0 mt-1">Report</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Institutions</p>
                    <p class="h4 mb-0">{{ $institutionsCount }}</p>
                    <a href="{{ route('academics.institutions.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Batches</p>
                    <p class="h4 mb-0">{{ $batchesCount }}</p>
                    <a href="{{ route('academics.batches.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Subjects</p>
                    <p class="h4 mb-0">{{ $subjectsCount }}</p>
                    <a href="{{ route('academics.subjects.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Topics</p>
                    <p class="h4 mb-0">{{ $topicsCount }}</p>
                    <a href="{{ route('academics.topics.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-2">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center py-3">
                    <p class="mb-1 small text-muted">Assignments</p>
                    <p class="h4 mb-0">{{ $assignmentsCount }}</p>
                    <a href="{{ route('academics.assignments.index') }}" class="btn btn-link btn-sm p-0 mt-1">Manage</a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

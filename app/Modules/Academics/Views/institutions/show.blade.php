@extends('auth::layout')

@section('title', $institution->name.' — Overview')
@section('page-title', 'Institution')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('academics.dashboard') }}">Academics</a></li>
            @if(auth()->user()->role === 'admin')
                <li class="breadcrumb-item"><a href="{{ route('academics.institutions.index') }}">Institutions</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $institution->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-bold text-dark">{{ $institution->name }}</h1>
            @if($institution->code)
                <p class="small text-muted mb-0 font-monospace">Code: {{ $institution->code }}</p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('academics.reports.show', ['type' => 'student_submission', 'institution_id' => $institution->id]) }}" class="btn btn-primary btn-sm rounded-pill">
                <i class="fas fa-user-graduate me-1"></i>Student report
            </a>
            @if(auth()->user()->role === 'admin')
                <span class="badge bg-light text-muted border align-self-center">Read-only overview</span>
            @endif
            @if(auth()->user()->role === 'institution_admin')
                <a href="{{ route('academics.batches.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">Batches</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase fw-semibold">Students</div>
                    <div class="fs-4 fw-bold text-dark">{{ $studentCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase fw-semibold">Faculty (accounts)</div>
                    <div class="fs-4 fw-bold text-dark">{{ $facultyCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase fw-semibold">ICR</div>
                    <div class="fs-4 fw-bold text-dark">{{ $icr }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-sm h-100 rounded-3">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase fw-semibold">Batches</div>
                    <div class="fs-4 fw-bold text-dark">{{ $batchesPaginator->total() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card academics-overview-card mb-4 rounded-3">
        <div class="card-body p-0">
            <div class="px-3 px-md-4 pt-3 pb-2 border-bottom">
                <h2 class="h6 mb-0 fw-bold">Batches</h2>
                <p class="small text-muted mb-0">Cohorts in this college.</p>
            </div>
            @if($batchesPaginator->total() === 0)
                <p class="text-muted p-4 mb-0">No batches yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 ps-md-4">Name</th>
                                <th class="text-end">Students</th>
                                <th class="text-end">Faculty</th>
                                <th class="text-end pe-3 pe-md-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchesPaginator as $batch)
                                <tr>
                                    <td class="ps-3 ps-md-4 fw-medium">
                                        @if(auth()->user()->role === 'institution_admin')
                                            <a href="{{ route('academics.batches.edit', $batch) }}" class="text-primary text-decoration-none">{{ $batch->name }}</a>
                                        @else
                                            <a href="{{ route('academics.reports.show', ['type' => 'student_submission', 'institution_id' => $institution->id, 'batch_id' => $batch->id]) }}" class="text-primary text-decoration-none">{{ $batch->name }}</a>
                                        @endif
                                    </td>
                                    <td class="text-end text-muted">{{ $batch->students_count ?? 0 }}</td>
                                    <td class="text-end text-muted">{{ $batch->faculty_count ?? 0 }}</td>
                                    <td class="text-end pe-3 pe-md-4">
                                        @if(auth()->user()->role === 'institution_admin')
                                            <a href="{{ route('academics.batches.edit', $batch) }}" class="btn btn-sm btn-outline-primary py-0 rounded-pill">Manage</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 px-md-4 pb-2">
                    {{ $batchesPaginator->links('pagination.modern') }}
                </div>
            @endif
        </div>
    </div>

    <div class="card academics-overview-card rounded-3">
        <div class="card-body p-0">
            <div class="px-3 px-md-4 pt-3 pb-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="h6 mb-0 fw-bold">People</h2>
                    <p class="small text-muted mb-0">Students &amp; faculty — open for academic record.</p>
                </div>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.users', ['segment' => 'academics']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Institute admins</a>
                @endif
            </div>
            @if($peoplePaginator->total() === 0)
                <p class="text-muted p-4 mb-0">No academic users linked to this institution yet.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($peoplePaginator as $p)
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 px-md-4 py-3">
                            <div class="min-w-0">
                                <a href="{{ route('academics.people.show', $p) }}" class="fw-semibold text-primary text-decoration-none">{{ $p->name }}</a>
                                <span class="badge rounded-pill {{ $p->role === 'faculty' ? 'bg-primary bg-opacity-10 text-primary' : 'bg-light text-dark border' }} ms-1">{{ $p->role }}</span>
                                <div class="small text-muted text-truncate" style="max-width: 20rem;">{{ $p->email }}</div>
                            </div>
                            <a href="{{ route('academics.people.show', $p) }}" class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0">View</a>
                        </li>
                    @endforeach
                </ul>
                <div class="px-3 px-md-4 pb-2">
                    {{ $peoplePaginator->links('pagination.modern') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

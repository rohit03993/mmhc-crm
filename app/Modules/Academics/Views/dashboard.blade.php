@extends('auth::layout')

@section('title', 'Academics - MeD Miracle Academic CRM')
@section('page-title', 'Academics')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <h2 class="h4 mb-4">Academic Dashboard</h2>
            <p class="text-muted">Welcome to the Academic module. Role-based content will be added as we build Batch, Subject, Topic, and Assignments.</p>
            @if(auth()->user()->role === 'faculty')
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line text-primary me-2"></i>FPI (Faculty Performance)</h5>
                            <p class="card-text display-6 mb-0">{{ $fpi }}<span class="fs-6 text-muted">%</span></p>
                            <small class="text-muted">Topic completion in your subjects</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-list-ul text-secondary me-2"></i>My Topics</h5>
                            <p class="card-text display-6">{{ $topicsCount }}</p>
                            <a href="{{ route('academics.topics.index') }}" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks me-2"></i>My Assignments</h5>
                            <p class="card-text display-6">{{ $assignmentsCount }}</p>
                            <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(auth()->user()->role === 'student')
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-line text-primary me-2"></i>SPI (Student Progress)</h5>
                            <p class="card-text display-6 mb-0">{{ $spi }}<span class="fs-6 text-muted">%</span></p>
                            <small class="text-muted">Assignments submitted</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks me-2"></i>My Assignments</h5>
                            <p class="card-text display-6">{{ $myAssignmentsCount }}</p>
                            @if($myPendingCount > 0)
                                <p class="small text-warning mb-1">{{ $myPendingCount }} pending</p>
                            @endif
                            <a href="{{ route('academics.my-assignments') }}" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(auth()->user()->role === 'institution_admin' && isset($icr))
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 border-info">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-university text-info me-2"></i>ICR (Institution Readiness)</h5>
                            <p class="card-text display-6 mb-0">{{ $icr }}<span class="fs-6 text-muted">%</span></p>
                            <small class="text-muted">Topic completion across your institution</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(in_array(auth()->user()->role, ['super_admin', 'institution_admin']))
            <div class="row g-3">
                @if(auth()->user()->role === 'super_admin' && $institutionsWithIcr->isNotEmpty())
                <div class="col-12 mb-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-chart-bar text-info me-2"></i>ICR by Institution</h5>
                            <div class="table-responsive mb-0">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Institution</th><th class="text-end">ICR %</th></tr></thead>
                                    <tbody>
                                        @foreach($institutionsWithIcr as $row)
                                        <tr><td>{{ $row['name'] }}</td><td class="text-end">{{ $row['icr'] }}%</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if(auth()->user()->role === 'super_admin')
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-university text-primary me-2"></i>Institutions</h5>
                            <p class="card-text display-6">{{ $institutionsCount }}</p>
                            <a href="{{ route('academics.institutions.index') }}" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-layer-group text-info me-2"></i>Batches</h5>
                            <p class="card-text display-6">{{ $batchesCount }}</p>
                            <a href="{{ route('academics.batches.index') }}" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-book text-success me-2"></i>Subjects</h5>
                            <p class="card-text display-6">{{ $subjectsCount }}</p>
                            <a href="{{ route('academics.subjects.index') }}" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-list-ul text-secondary me-2"></i>Topics</h5>
                            <p class="card-text display-6">{{ $topicsCount }}</p>
                            <a href="{{ route('academics.topics.index') }}" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-tasks me-2"></i>Assignments</h5>
                            <p class="card-text display-6">{{ $assignmentsCount }}</p>
                            <a href="{{ route('academics.assignments.index') }}" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

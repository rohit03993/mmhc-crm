@extends('auth::layout')

@section('title', $student->name . ' – Student Report - Academics')
@section('page-title', 'Student report')

@php
    $canManageAcademics = in_array(auth()->user()->role, ['super_admin', 'admin', 'institution_admin', 'faculty'], true);
@endphp

@section('head')
    @include('academics::reports.partials.student-report-styles')
@endsection

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h2 class="h5 mb-1">
                Full report:
                @if($canManageAcademics)
                    <a href="{{ route('academics.people.show', $student) }}" class="text-dark text-decoration-none border-bottom border-primary border-2">{{ $student->name }}</a>
                @else
                    <span class="text-dark">{{ $student->name }}</span>
                @endif
            </h2>
            <p class="small text-muted mb-0">Profile documents, attendance (by day), assignments, and SPI in one place.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.profiles.view', $student) }}" class="btn btn-outline-primary btn-sm rounded-pill"><i class="fas fa-id-card me-1"></i>CRM profile</a>
            @endif
            <a href="{{ route('academics.reports.show', ['type' => 'student_submission']) }}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>Back to list</a>
        </div>
    </div>

    @include('academics::reports.partials.student-report-body', [
        'student' => $student,
        'institution' => $institution,
        'batches' => $batches,
        'documentsPaginator' => $documentsPaginator,
        'spi' => $spi,
        'periodLabel' => $periodLabel,
        'currentPeriod' => $currentPeriod,
        'attendanceStats' => $attendanceStats,
        'attendanceLedgerPaginator' => $attendanceLedgerPaginator,
        'assignmentsPaginator' => $assignmentsPaginator,
        'quizAttemptsPaginator' => $quizAttemptsPaginator,
        'submissionsByAssignment' => $submissionsByAssignment,
    ])
</div>
@endsection

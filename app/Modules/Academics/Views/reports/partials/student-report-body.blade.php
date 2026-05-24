@php
    $canManageAcademics = in_array(auth()->user()->role, ['super_admin', 'admin', 'institution_admin', 'faculty'], true);
    $canOpenAssignmentAdmin = in_array(auth()->user()->role, ['institution_admin', 'faculty'], true);
@endphp

{{-- Institute & batch --}}
<div class="card shadow-sm border mb-4 rounded-3">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 fw-bold"><i class="fas fa-university text-primary me-2"></i>Institute &amp; batch</h5>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block">Institution</span>
                @if($institution && $canManageAcademics)
                    <a href="{{ route('academics.institutions.show', $institution) }}" class="fw-semibold text-primary text-decoration-none">{{ $institution->name }}</a>
                @else
                    <p class="mb-0 fw-medium">{{ $institution->name ?? '—' }}</p>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block">Current batch(es)</span>
                <p class="mb-0 fw-medium">{{ $batches->pluck('name')->join(', ') ?: '—' }}</p>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block">Email</span>
                <p class="mb-0">{{ $student->email }}</p>
            </div>
            <div class="col-12 col-md-6">
                <span class="text-muted small d-block">Unique ID</span>
                <p class="mb-0 font-monospace small">{{ $student->unique_id ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Uploaded documents (CRM / profile) --}}
<div class="card shadow-sm border mb-4 rounded-3">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 fw-bold"><i class="fas fa-folder-open text-warning me-2"></i>Uploaded documents</h5>
        <p class="small text-muted mb-3">Files attached to this user&rsquo;s CRM profile (ID proofs, certificates, etc.).</p>
        @if($documentsPaginator->total() === 0)
            <p class="text-muted mb-0">No documents uploaded yet.</p>
        @else
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentsPaginator as $doc)
                        <tr>
                            <td class="ps-3">
                                <i class="{{ $doc->file_icon }} me-1"></i>
                                <span class="fw-medium">{{ $doc->document_name ?: $doc->original_name }}</span>
                            </td>
                            <td><span class="small text-muted">{{ $doc->document_type_display }}</span></td>
                            <td><span class="badge rounded-pill bg-light text-dark border">{{ $doc->status_display }}</span></td>
                            <td class="text-muted small">{{ $doc->created_at?->format('M d, Y H:i') }}</td>
                            <td class="pe-3 text-end text-nowrap">
                                <a href="{{ route('documents.view', $doc->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1" target="_blank" rel="noopener">View</a>
                                <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">Download</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-1">
                {{ $documentsPaginator->links('pagination.modern') }}
            </div>
        @endif
    </div>
</div>

{{-- SPI (overall) --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border-primary border-opacity-25 rounded-3">
            <div class="card-body text-center py-3">
                <p class="small text-muted mb-1 text-uppercase fw-semibold" style="letter-spacing: .04em;">SPI (Profile credit)</p>
                <p class="h4 mb-0 text-primary fw-bold">{{ $spi }}%</p>
                <p class="small text-muted mb-0 mt-1">Submitted + all shared mentors rated</p>
            </div>
        </div>
    </div>
    @if(isset($spiBreakdown))
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm rounded-3">
            <div class="card-body text-center py-3">
                <p class="small text-muted mb-1">Fully credited</p>
                <p class="h4 mb-0 fw-bold">{{ $spiBreakdown['verified'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm rounded-3">
            <div class="card-body text-center py-3">
                <p class="small text-muted mb-1">Awaiting mentor</p>
                <p class="h4 mb-0 fw-bold text-warning">{{ $spiBreakdown['submitted_pending_mentor'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm rounded-3">
            <div class="card-body text-center py-3">
                <p class="small text-muted mb-1">Active mentors</p>
                <p class="h4 mb-0 fw-bold">{{ $activeMentorCount ?? 0 }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

@isset($quizAttemptsPaginator)
{{-- Quiz / exam results (submitted attempts) --}}
<div class="card shadow-sm border mb-4 rounded-3">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 fw-bold"><i class="fas fa-question-circle text-primary me-2"></i>Quiz &amp; exam results</h5>
        <p class="small text-muted mb-3">Submitted MCQ attempts and scores. Admins and faculty can open the full breakdown.</p>
        @if($quizAttemptsPaginator->total() === 0)
            <p class="text-muted mb-0">No submitted quizzes yet.</p>
        @else
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Exam</th>
                            <th>Score</th>
                            <th class="pe-3">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quizAttemptsPaginator as $qatt)
                            @php $maxQ = $qatt->exam->questions->sum('points'); @endphp
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('academics.exams.result', [$qatt->exam_id, $qatt->id]) }}" class="fw-medium text-primary text-decoration-none">{{ $qatt->exam->title }}</a>
                                </td>
                                <td>
                                    <strong>{{ number_format((float) $qatt->score, 2) }}</strong>
                                    <span class="text-muted small">/ {{ number_format((float) $maxQ, 2) }}</span>
                                </td>
                                <td class="pe-3 text-muted small">{{ $qatt->submitted_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-1">
                {{ $quizAttemptsPaginator->links('pagination.modern') }}
            </div>
        @endif
    </div>
</div>
@endisset

{{-- Attendance --}}
<div class="card shadow-sm border mb-4 rounded-3">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-3">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-calendar-check text-info me-2"></i>Attendance <span class="fw-normal text-muted small">({{ $periodLabel ?? 'All time' }})</span></h5>
            <div class="btn-group btn-group-sm shadow-sm" role="group">
                <a href="{{ request()->fullUrlWithQuery(['period' => 'this_month', 'attpage' => 1, 'apage' => 1, 'docpage' => 1, 'expage' => 1]) }}" class="btn {{ ($currentPeriod ?? 'this_month') === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This month</a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'last_month', 'attpage' => 1, 'apage' => 1, 'docpage' => 1, 'expage' => 1]) }}" class="btn {{ ($currentPeriod ?? '') === 'last_month' ? 'btn-primary' : 'btn-outline-secondary' }}">Last month</a>
                <a href="{{ request()->fullUrlWithQuery(['period' => 'all', 'attpage' => 1, 'apage' => 1, 'docpage' => 1, 'expage' => 1]) }}" class="btn {{ ($currentPeriod ?? '') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">All time</a>
            </div>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-4 col-md-2">
                <div class="mmhc-kpi-tint mmhc-kpi-tint--neutral">
                    <p class="mmhc-kpi-tint__label">Days</p>
                    <p class="mmhc-kpi-tint__value">{{ $attendanceStats['total'] }}</p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="mmhc-kpi-tint mmhc-kpi-tint--present">
                    <p class="mmhc-kpi-tint__label">Present</p>
                    <p class="mmhc-kpi-tint__value">{{ $attendanceStats['present'] }}</p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="mmhc-kpi-tint mmhc-kpi-tint--absent">
                    <p class="mmhc-kpi-tint__label">Absent</p>
                    <p class="mmhc-kpi-tint__value">{{ $attendanceStats['absent'] }}</p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="mmhc-kpi-tint mmhc-kpi-tint--leave">
                    <p class="mmhc-kpi-tint__label">Leave</p>
                    <p class="mmhc-kpi-tint__value">{{ $attendanceStats['leave'] }}</p>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <div class="mmhc-kpi-tint mmhc-kpi-tint--pct">
                    <p class="mmhc-kpi-tint__label">Attendance %</p>
                    <p class="mmhc-kpi-tint__value">{{ $attendanceStats['percentage'] }}%</p>
                </div>
            </div>
        </div>
        @if($attendanceLedgerPaginator->total() === 0)
            <p class="text-muted mb-0">No days in this period.</p>
        @else
            <p class="small text-muted mb-2">Daily roll-up for the selected range (matches the summary above). Days without a mark count as absent.</p>
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Batch</th>
                            <th class="pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendanceLedgerPaginator as $row)
                        <tr>
                            <td class="ps-3">{{ $row['date']->format('M d, Y') }}</td>
                            <td class="text-muted small">{{ $row['batch_label'] }}</td>
                            <td class="pe-3">
                                @if($row['status'] === \App\Modules\Academics\Models\Attendance::STATUS_PRESENT)
                                    <span class="badge rounded-pill bg-success">Present</span>
                                @elseif($row['status'] === \App\Modules\Academics\Models\Attendance::STATUS_ABSENT)
                                    <span class="badge rounded-pill bg-danger">Absent</span>
                                    @if(!empty($row['inferred']))
                                        <span class="text-muted small ms-1">(no mark)</span>
                                    @endif
                                @else
                                    <span class="badge rounded-pill bg-warning text-dark">Leave</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-1">
                {{ $attendanceLedgerPaginator->links('pagination.modern') }}
            </div>
        @endif
    </div>
</div>

{{-- Assignments --}}
@php
    use App\Modules\Academics\Support\AcademicsTaxonomy;
@endphp
<div class="card shadow-sm border rounded-3">
    <div class="card-body p-4">
        <h5 class="card-title mb-3 fw-bold"><i class="fas fa-tasks text-secondary me-2"></i>Assignments</h5>
        @if($assignmentsPaginator->total() === 0)
            <p class="text-muted mb-0">No assignments assigned yet.</p>
        @else
            <div class="table-responsive rounded-3 border">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Assignment</th>
                            <th>Type</th>
                            <th>Subject / Topic</th>
                            <th>Due date</th>
                            <th>Status</th>
                            <th>Mentor</th>
                            <th class="pe-3">Submitted at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignmentsPaginator as $a)
                        @php $sub = $submissionsByAssignment->get($a->id); @endphp
                        <tr>
                            <td class="ps-3">
                                @if($canOpenAssignmentAdmin)
                                    <a href="{{ route('academics.assignments.show', $a) }}" class="fw-medium text-primary text-decoration-none">{{ $a->title }}</a>
                                @elseif(auth()->id() === $student->id && ! $sub)
                                    <a href="{{ route('academics.submit.form', $a) }}" class="fw-medium text-primary text-decoration-none">{{ $a->title }}</a>
                                @else
                                    <span class="fw-medium">{{ $a->title }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border small">{{ AcademicsTaxonomy::assignmentTypeLabel($a->assignment_type) }}</span></td>
                            <td><span class="text-muted small">{{ $a->topic->subject->name ?? '—' }}</span> / {{ $a->topic->name ?? '—' }}</td>
                            <td>{{ $a->due_date ? $a->due_date->format('M d, Y') : '—' }}</td>
                            <td>
                                @if($sub)
                                    <span class="badge rounded-pill bg-success">Submitted</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary">Pending</span>
                                @endif
                            </td>
                            <td>
                                @php $mStatus = $mentorStatusByAssignment[$a->id] ?? 'not_submitted'; @endphp
                                @if($mStatus === 'verified')
                                    <span class="badge rounded-pill bg-success">Mentor OK</span>
                                @elseif($mStatus === 'pending')
                                    <span class="badge rounded-pill bg-warning text-dark">Mentor pending</span>
                                @elseif($mStatus === 'none' && $sub)
                                    <span class="text-muted small">—</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="pe-3 text-muted small">{{ $sub && $sub->submitted_at ? $sub->submitted_at->format('M d, Y H:i') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-1">
                {{ $assignmentsPaginator->links('pagination.modern') }}
            </div>
        @endif
    </div>
</div>

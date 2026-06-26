@php
    $showDocsPanel = $insights['show_documents_panel'] ?? true;
    $hasDocs = $insights['documents']->isNotEmpty();
    $hasStudent = !empty($insights['student']);
    $hasFaculty = !empty($insights['faculty']);
    $hasInstitution = !empty($insights['institution']);
    $showRightColumn = $hasStudent || $hasFaculty || $hasInstitution;
    $showInsightsRow = $showDocsPanel || $showRightColumn;
@endphp

@if($showInsightsRow)
<div class="row g-3 mb-4 academics-dashboard-insights">
    @if($showDocsPanel)
    <div class="col-12 {{ $showRightColumn ? 'col-xl-4' : '' }}">
        <div class="card academics-overview-card h-100">
            <div class="card-body p-0">
                <div class="px-3 px-md-4 pt-3 pb-2 border-bottom border-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="h6 mb-0 fw-bold text-dark d-flex align-items-center">
                            <span class="rounded-3 bg-primary bg-opacity-10 p-2 me-2 text-primary"><i class="fas fa-folder-open"></i></span>
                            Your documents
                        </h2>
                        <p class="small text-muted mb-0 mt-1">Profile uploads (ID, certificates, etc.)</p>
                    </div>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">All documents</a>
                </div>
                @if($hasDocs)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 ps-md-4">Type</th>
                                    <th>File</th>
                                    <th class="text-end pe-3 pe-md-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insights['documents'] as $doc)
                                    <tr>
                                        <td class="ps-3 ps-md-4 small text-muted">{{ $doc->document_type_display }}</td>
                                        <td class="small text-dark text-truncate" style="max-width: 10rem;" title="{{ $doc->original_name ?? $doc->document_name }}">{{ $doc->original_name ?? $doc->document_name }}</td>
                                        <td class="text-end pe-3 pe-md-4">
                                            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-link btn-sm p-0 fw-semibold">Download</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-3 px-md-4 py-4 text-center text-muted small">
                        <p class="mb-2 mb-0">No documents uploaded yet.</p>
                        <a href="{{ route('documents.index') }}" class="fw-semibold">Upload documents</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($hasStudent)
        @php $s = $insights['student']; @endphp
        <div class="col-12 col-xl-8">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-primary me-2"></i>Recent attendance</h3>
                            </div>
                            @if($s['recentAttendance']->isNotEmpty())
                                <ul class="list-group list-group-flush small">
                                    @foreach($s['recentAttendance'] as $row)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                            <span class="text-muted">{{ $row->date?->format('M j, Y') }}</span>
                                            <span class="badge rounded-pill {{ $row->status === 'present' ? 'bg-success' : ($row->status === 'absent' ? 'bg-danger' : 'bg-secondary') }}">
                                                {{ \App\Modules\Academics\Models\Attendance::statuses()[$row->status] ?? ucfirst($row->status) }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="px-3 py-2 border-top bg-light bg-opacity-50">
                                    <a href="{{ route('academics.attendance.my') }}" class="small fw-semibold">Full attendance</a>
                                </div>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">No attendance records yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-start">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-hourglass-half text-warning me-2"></i>Homework due</h3>
                                @if(($s['homeworkDueCount'] ?? 0) > 0)
                                    <span class="badge bg-warning text-dark rounded-pill">{{ $s['homeworkDueCount'] }}</span>
                                @endif
                            </div>
                            @if($s['homeworkDue']->isNotEmpty())
                                <ul class="list-group list-group-flush small">
                                    @foreach($s['homeworkDue'] as $asg)
                                        <li class="list-group-item px-3 py-2">
                                            <div class="fw-medium text-dark text-truncate" title="{{ $asg->title }}">{{ $asg->title }}</div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="text-muted">{{ $asg->due_date ? $asg->due_date->format('M j') : 'No due date' }}</span>
                                                <a href="{{ route('academics.submit.form', $asg) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Submit</a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="px-3 py-2 border-top bg-light bg-opacity-50">
                                    <a href="{{ route('academics.my-assignments') }}" class="small fw-semibold">All assignments</a>
                                </div>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">You’re caught up — no pending homework.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-paper-plane text-info me-2"></i>Recent submissions</h3>
                            </div>
                            @if($s['recentSubmissions']->isNotEmpty())
                                <ul class="list-group list-group-flush small">
                                    @foreach($s['recentSubmissions'] as $sub)
                                        <li class="list-group-item px-3 py-2">
                                            <div class="fw-medium text-dark text-truncate" title="{{ $sub->assignment->title ?? '' }}">{{ $sub->assignment->title ?? 'Assignment' }}</div>
                                            <div class="text-muted mt-1">{{ $sub->submitted_at?->format('M j, g:i a') }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="px-3 py-2 border-top bg-light bg-opacity-50">
                                    <a href="{{ route('academics.my-assignments') }}" class="small fw-semibold">My assignments</a>
                                </div>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">No submissions yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasFaculty)
        @php $f = $insights['faculty']; @endphp
        <div class="col-12 col-xl-8">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-inbox text-primary me-2"></i>Student work in</h3>
                                <a href="{{ route('academics.assignments.index') }}" class="small fw-semibold">Assignments</a>
                            </div>
                            @if($f['recentStudentSubmissions']->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Student</th>
                                                <th>Assignment</th>
                                                <th class="text-nowrap">When</th>
                                                <th class="text-end pe-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($f['recentStudentSubmissions'] as $sub)
                                                <tr>
                                                    <td class="ps-3 text-truncate" style="max-width: 6rem;">
                                                        @if($sub->user)
                                                            <a href="{{ route('academics.people.show', $sub->user) }}" class="text-dark fw-medium text-decoration-none">{{ $sub->user->name }}</a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="text-truncate" style="max-width: 7rem;" title="{{ $sub->assignment->title ?? '' }}">{{ $sub->assignment->title ?? '—' }}</td>
                                                    <td class="text-muted text-nowrap">{{ $sub->submitted_at?->diffForHumans() }}</td>
                                                    <td class="text-end pe-3">
                                                        @if($sub->assignment)
                                                            <a href="{{ route('academics.assignments.submissions', $sub->assignment) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Submissions</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">No recent submissions for your subjects.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-tasks text-secondary me-2"></i>Recent homework set</h3>
                                <a href="{{ route('academics.assignments.create') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0">New</a>
                            </div>
                            @if($f['recentAssignments']->isNotEmpty())
                                <ul class="list-group list-group-flush small">
                                    @foreach($f['recentAssignments'] as $asg)
                                        <li class="list-group-item px-3 py-2">
                                            <a href="{{ route('academics.assignments.show', $asg) }}" class="fw-medium text-dark text-decoration-none">{{ $asg->title }}</a>
                                            <div class="text-muted mt-1">
                                                {{ $asg->topic->subject->name ?? 'Subject' }}
                                                @if($asg->due_date)
                                                    · Due {{ $asg->due_date->format('M j') }}
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">Create an assignment from a topic.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasInstitution)
        @php $i = $insights['institution']; @endphp
        <div class="col-12 col-xl-8">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt text-success me-2"></i>Recent attendance</h3>
                                <a href="{{ route('academics.attendance.index') }}" class="small fw-semibold">Mark</a>
                            </div>
                            @if($i['recentAttendance']->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Student</th>
                                                <th>Date</th>
                                                <th class="pe-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($i['recentAttendance'] as $row)
                                                <tr>
                                                    <td class="ps-3 text-truncate" style="max-width: 7rem;">{{ $row->user->name ?? '—' }}</td>
                                                    <td class="text-muted text-nowrap">{{ $row->date?->format('M j') }}</td>
                                                    <td class="pe-3">
                                                        <span class="badge rounded-pill {{ $row->status === 'present' ? 'bg-success' : ($row->status === 'absent' ? 'bg-danger' : 'bg-secondary') }}">
                                                            {{ \App\Modules\Academics\Models\Attendance::statuses()[$row->status] ?? $row->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">No attendance logged yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card academics-overview-card h-100">
                        <div class="card-body p-0">
                            <div class="px-3 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-center">
                                <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-file-upload text-info me-2"></i>Recent submissions</h3>
                                <a href="{{ route('academics.reports.show', ['type' => 'student_submission', 'institution_id' => auth()->user()->academic_institution_id]) }}" class="small fw-semibold">Report</a>
                            </div>
                            @if($i['recentSubmissions']->isNotEmpty())
                                <ul class="list-group list-group-flush small">
                                    @foreach($i['recentSubmissions'] as $sub)
                                        <li class="list-group-item px-3 py-2 d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="fw-medium text-dark">
                                                    @if($sub->user)
                                                        <a href="{{ route('academics.people.show', $sub->user) }}" class="text-dark text-decoration-none">{{ $sub->user->name }}</a>
                                                    @else
                                                        Student
                                                    @endif
                                                </div>
                                                <div class="text-muted text-truncate" style="max-width: 14rem;">{{ $sub->assignment->title ?? '' }}</div>
                                            </div>
                                            @if($sub->assignment)
                                                <a href="{{ route('academics.assignments.submissions', $sub->assignment) }}" class="btn btn-sm btn-outline-primary py-0 px-2 flex-shrink-0">View</a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="px-3 py-4 text-muted small text-center">No submissions in your institution yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endif

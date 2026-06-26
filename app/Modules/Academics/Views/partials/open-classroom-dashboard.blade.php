@php
    $oc = $openClassroom ?? ['enabled' => false];
    if (!($oc['enabled'] ?? false)) {
        return;
    }
    $user = auth()->user();
    $isStudent = $user->role === 'student';
    $isTeacher = $user->role === 'faculty' || $user->is_open_teacher;
    $showSection = $isStudent || $isTeacher;
@endphp

@if($showSection)
<div class="academics-oc-dash mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h2 class="h5 mb-0 fw-bold text-dark d-flex align-items-center">
                <span class="rounded-3 bg-success bg-opacity-10 p-2 me-2 text-success"><i class="fas fa-door-open"></i></span>
                Open classrooms
            </h2>
            <p class="small text-muted mb-0 mt-1">Public learning spaces — join without college approval.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-outline-success btn-sm rounded-pill">
                <i class="fas fa-compass me-1"></i>Browse{{ ($oc['browse_count'] ?? 0) > 0 ? ' ('.$oc['browse_count'].')' : '' }}
            </a>
            @if($isTeacher)
                <a href="{{ route('academics.open-classrooms.create') }}" class="btn btn-success btn-sm rounded-pill">
                    <i class="fas fa-plus me-1"></i>Create classroom
                </a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        @if($isStudent)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card academics-stat-card h-100 shadow-none bg-white border-success border-opacity-25">
                <div class="card-body d-flex flex-column py-4">
                    <p class="academics-stat-label mb-2">Joined</p>
                    <p class="academics-stat-value mb-1">{{ $oc['joined_count'] ?? 0 }}</p>
                    @if(($oc['pending_tasks'] ?? 0) > 0)
                        <p class="small text-warning mb-2">{{ $oc['pending_tasks'] }} task(s) to submit</p>
                    @else
                        <p class="small text-muted mb-2">No pending open-class tasks</p>
                    @endif
                    <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-success btn-sm mt-auto align-self-start rounded-pill">My classrooms</a>
                </div>
            </div>
        </div>
        @endif

        @if($isTeacher)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card academics-stat-card h-100 shadow-none bg-white border-success border-opacity-25">
                <div class="card-body d-flex flex-column py-4">
                    <p class="academics-stat-label mb-2">{{ $user->is_open_teacher && !$user->academic_institution_id ? 'Your classrooms' : 'Classrooms you run' }}</p>
                    <p class="academics-stat-value mb-1">{{ $oc['owned_count'] ?? 0 }}</p>
                    <p class="small text-muted mb-2">{{ $oc['joined_count'] ?? 0 }} joined as member</p>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        @if(($oc['owned_count'] ?? 0) > 0)
                            <a href="{{ route('academics.open-classrooms.index', ['tab' => 'mine']) }}" class="btn btn-outline-success btn-sm rounded-pill">Manage</a>
                        @endif
                        <a href="{{ route('academics.open-classrooms.create') }}" class="btn btn-success btn-sm rounded-pill">New</a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isStudent && ($oc['pending_tasks_list'] ?? collect())->isNotEmpty())
        <div class="col-12 col-lg-8">
            <div class="card academics-overview-card h-100">
                <div class="card-body p-0">
                    <div class="px-3 px-md-4 pt-3 pb-2 border-bottom border-light d-flex justify-content-between align-items-center">
                        <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-clipboard-list text-success me-2"></i>Open classroom tasks</h3>
                        @if(($oc['pending_tasks'] ?? 0) > ($oc['pending_tasks_list']->count()))
                            <span class="badge bg-warning text-dark rounded-pill">{{ $oc['pending_tasks'] }} total</span>
                        @endif
                    </div>
                    <ul class="list-group list-group-flush small">
                        @foreach($oc['pending_tasks_list'] as $task)
                            <li class="list-group-item px-3 px-md-4 py-2 d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="fw-medium text-dark text-truncate">{{ $task->title }}</div>
                                    <div class="text-muted">
                                        {{ $task->classroom->title ?? 'Classroom' }}
                                        @if($task->due_date)
                                            · Due {{ $task->due_date->format('M j') }}
                                        @endif
                                    </div>
                                </div>
                                @if($task->classroom)
                                    <a href="{{ route('academics.open-classrooms.show', $task->classroom) }}" class="btn btn-sm btn-outline-success rounded-pill flex-shrink-0">Open</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @elseif($isTeacher && ($oc['owned_classrooms'] ?? collect())->isNotEmpty())
        <div class="col-12 col-lg-8">
            <div class="card academics-overview-card h-100">
                <div class="card-body p-0">
                    <div class="px-3 px-md-4 pt-3 pb-2 border-bottom border-light">
                        <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-chalkboard text-success me-2"></i>Your classrooms</h3>
                    </div>
                    <ul class="list-group list-group-flush small">
                        @foreach($oc['owned_classrooms'] as $room)
                            <li class="list-group-item px-3 px-md-4 py-2 d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="fw-medium text-dark text-truncate">{{ $room->title }}</div>
                                    <div class="text-muted">{{ $room->members_count ?? 0 }} member(s) · {{ ucfirst($room->visibility) }}</div>
                                </div>
                                <a href="{{ route('academics.open-classrooms.show', $room) }}" class="btn btn-sm btn-outline-success rounded-pill flex-shrink-0">Open</a>
                            </li>
                        @endforeach
                    </ul>
                    @if(($oc['owned_count'] ?? 0) > $oc['owned_classrooms']->count())
                        <div class="px-3 px-md-4 py-2 border-top bg-light bg-opacity-50">
                            <a href="{{ route('academics.open-classrooms.index', ['tab' => 'mine']) }}" class="small fw-semibold">View all {{ $oc['owned_count'] }} classrooms</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @elseif($isStudent && ($oc['joined_classrooms'] ?? collect())->isNotEmpty())
        <div class="col-12 col-lg-8">
            <div class="card academics-overview-card h-100">
                <div class="card-body p-0">
                    <div class="px-3 px-md-4 pt-3 pb-2 border-bottom border-light">
                        <h3 class="h6 mb-0 fw-bold text-dark"><i class="fas fa-users text-success me-2"></i>Recently joined</h3>
                    </div>
                    <ul class="list-group list-group-flush small">
                        @foreach($oc['joined_classrooms'] as $room)
                            <li class="list-group-item px-3 px-md-4 py-2 d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="fw-medium text-dark text-truncate">{{ $room->title }}</div>
                                    <div class="text-muted">{{ $room->subject_area ?: 'General' }} · {{ $room->members_count ?? 0 }} members</div>
                                </div>
                                <a href="{{ route('academics.open-classrooms.show', $room) }}" class="btn btn-sm btn-outline-success rounded-pill flex-shrink-0">Open</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @elseif($isStudent)
        <div class="col-12 col-lg-8">
            <div class="card academics-overview-card h-100 border-success border-opacity-25">
                <div class="card-body px-3 px-md-4 py-4 text-center">
                    <p class="text-muted small mb-2 mb-md-3">Discover public classrooms from independent teachers and colleges.</p>
                    <a href="{{ route('academics.open-classrooms.index') }}" class="btn btn-success btn-sm rounded-pill">
                        <i class="fas fa-search me-1"></i>Browse open classrooms
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

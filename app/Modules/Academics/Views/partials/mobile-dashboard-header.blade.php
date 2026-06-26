@php
    use App\Modules\Academics\Support\AcademicsMobileUi;
    $user = auth()->user();
    $roleLabel = match ($user->role) {
        'institution_admin' => 'College admin',
        'faculty' => 'Faculty',
        'student' => 'Student',
        'admin' => 'Platform admin',
        default => ucfirst(str_replace('_', ' ', $user->role)),
    };
@endphp
<div class="acad-dash-header d-md-none">
    <div class="acad-dash-header__top">
        <div class="acad-dash-header__avatar" aria-hidden="true">
            @if($user->profile?->avatar_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile->avatar_path) }}" alt="">
            @else
                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="acad-dash-header__meta">
            <p class="acad-dash-header__greeting mb-0">{{ AcademicsMobileUi::dashboardGreeting($user) }}</p>
            <p class="acad-dash-header__role mb-0">{{ $roleLabel }} · {{ $user->unique_id }}</p>
        </div>
        <button type="button"
                class="acad-dash-header__menu"
                data-bs-toggle="offcanvas"
                data-bs-target="#mmhcAppSidebar"
                aria-label="Open menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
    </div>
    @if($user->role === 'student' && $user->academic_enrollment_status === 'pending')
        <div class="acad-dash-header__chip acad-dash-header__chip--warn">
            <i class="fas fa-hourglass-half"></i>
            <span>College approval pending — open classrooms still available</span>
        </div>
    @elseif($user->role === 'faculty' && $user->is_open_teacher && !$user->academic_institution_id)
        <a href="{{ route('academics.open-classrooms.create') }}" class="acad-dash-header__chip acad-dash-header__chip--action">
            <i class="fas fa-door-open"></i>
            <span>Create your first open classroom</span>
            <i class="fas fa-chevron-right ms-auto"></i>
        </a>
    @elseif($user->role === 'student' && ($openClassroom['pending_tasks'] ?? 0) > 0)
        <a href="{{ route('academics.open-classrooms.index', ['tab' => 'joined']) }}" class="acad-dash-header__chip acad-dash-header__chip--action">
            <i class="fas fa-clipboard-list"></i>
            <span>{{ $openClassroom['pending_tasks'] }} open classroom task(s)</span>
            <i class="fas fa-chevron-right ms-auto"></i>
        </a>
    @elseif($user->role === 'faculty' && ($topicsCount ?? 0) === 0 && !$user->is_open_teacher)
        <div class="acad-dash-header__chip acad-dash-header__chip--info">
            <i class="fas fa-info-circle"></i>
            <span>Ask your college admin to assign subjects</span>
        </div>
    @elseif($user->role === 'institution_admin' && ($enrollmentPendingCount ?? 0) > 0)
        <a href="{{ route('academics.enrollments.index') }}" class="acad-dash-header__chip acad-dash-header__chip--action">
            <i class="fas fa-user-clock"></i>
            <span>{{ $enrollmentPendingCount }} enrollment(s) to review</span>
            <i class="fas fa-chevron-right ms-auto"></i>
        </a>
    @endif
</div>

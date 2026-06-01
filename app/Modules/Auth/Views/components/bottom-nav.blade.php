@once('mmhc-bottom-nav')
<nav class="app-bottom-nav d-md-none" aria-label="Main navigation">
    @if(auth()->user()->hasAcademicRole())
        <a href="{{ route('academics.dashboard') }}" class="app-nav-item {{ request()->routeIs('academics.dashboard') ? 'active' : '' }}">
            <i class="fas fa-graduation-cap"></i>
            <span>Academics</span>
        </a>
        @if(auth()->user()->role === 'student')
        <a href="{{ route('academics.my-assignments') }}" class="app-nav-item {{ request()->routeIs('academics.my-assignments') || request()->routeIs('academics.submit.*') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('academics.mentorship.index') }}" class="app-nav-item {{ request()->routeIs('academics.mentorship.*') ? 'active' : '' }}">
            <i class="fas fa-hands-helping"></i>
            <span>Mentors</span>
        </a>
        @elseif(auth()->user()->role === 'faculty')
        <a href="{{ route('academics.topics.index') }}" class="app-nav-item {{ request()->routeIs('academics.topics.*') ? 'active' : '' }}">
            <i class="fas fa-list-ul"></i>
            <span>Topics</span>
        </a>
        <a href="{{ route('academics.assignments.index') }}" class="app-nav-item {{ request()->routeIs('academics.assignments.*') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('academics.attendance.index') }}" class="app-nav-item {{ request()->routeIs('academics.attendance.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span>Attend</span>
        </a>
        <a href="{{ route('academics.reports.index') }}" class="app-nav-item {{ request()->routeIs('academics.reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        @elseif(auth()->user()->role === 'institution_admin')
        <a href="{{ route('academics.students.index') }}" class="app-nav-item {{ request()->routeIs('academics.students.*') ? 'active' : '' }}">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        <a href="{{ route('academics.enrollments.index') }}" class="app-nav-item {{ request()->routeIs('academics.enrollments.*') ? 'active' : '' }}">
            <i class="fas fa-user-clock"></i>
            <span>Enroll</span>
        </a>
        <a href="{{ route('academics.batches.index') }}" class="app-nav-item {{ request()->routeIs('academics.batches.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>Batches</span>
        </a>
        <a href="{{ route('academics.reports.index') }}" class="app-nav-item {{ request()->routeIs('academics.reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        @else
        <a href="{{ route('academics.institutions.index') }}" class="app-nav-item {{ request()->routeIs('academics.institutions.*') ? 'active' : '' }}">
            <i class="fas fa-university"></i>
            <span>Institutes</span>
        </a>
        <a href="{{ route('academics.batches.index') }}" class="app-nav-item {{ request()->routeIs('academics.batches.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>Batches</span>
        </a>
        @endif
        <a href="{{ route('community.index') }}" class="app-nav-item {{ request()->routeIs('community.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Community</span>
        </a>
    @elseif(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="app-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('admin.users') }}" class="app-nav-item {{ request()->routeIs('admin.users') || request()->routeIs('admin.profiles*') || request()->routeIs('admin.staff.id-card') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="{{ route('admin.service-requests') }}" class="app-nav-item {{ request()->routeIs('admin.service-requests*') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Services</span>
        </a>
    @elseif(auth()->user()->isPatient())
        <a href="{{ route('community.index') }}" class="app-nav-item {{ request()->routeIs('community.*') || request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('services.my-requests') }}" class="app-nav-item {{ request()->routeIs('services.my-requests') || request()->routeIs('services.show') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            <span>Requests</span>
        </a>
        <a href="{{ route('staff.index') }}" class="app-nav-item {{ request()->routeIs('staff.index') || request()->routeIs('book.*') || request()->routeIs('services.index') ? 'active' : '' }}">
            <i class="fas fa-user-nurse"></i>
            <span>Staff</span>
        </a>
    @elseif(auth()->user()->isStaff())
        <a href="{{ route('staff.dashboard') }}" class="app-nav-item {{ request()->routeIs('staff.dashboard') || request()->routeIs('staff.service-details') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        @if(in_array(auth()->user()->role, ['nurse', 'caregiver'], true))
        <a href="{{ route('academics.mentorship.index') }}" class="app-nav-item {{ request()->routeIs('academics.mentorship.*') ? 'active' : '' }}">
            <i class="fas fa-hands-helping"></i>
            <span>Mentors</span>
        </a>
        @endif
        <a href="{{ route('staff.dashboard') }}#assignments" class="app-nav-item">
            <i class="fas fa-tasks"></i>
            <span>Jobs</span>
        </a>
        <a href="{{ route('staff.rewards.index') }}" class="app-nav-item {{ request()->routeIs('staff.rewards.*') || request()->routeIs('rewards.*') ? 'active' : '' }}">
            <i class="fas fa-gift"></i>
            <span>Rewards</span>
        </a>
    @else
        <a href="{{ route('dashboard') }}" class="app-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('community.index') }}" class="app-nav-item {{ request()->routeIs('community.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Community</span>
        </a>
    @endif

    @php
        $bnUser = auth()->user();
        $bnProfile = $bnUser->profile;
        $bnAvatar = ($bnProfile && $bnProfile->avatar_path) ? \Illuminate\Support\Facades\Storage::url($bnProfile->avatar_path) : null;
        $bnProfileActive = request()->routeIs('profile.*') || request()->routeIs('documents.*');
        $bnProfileHref = (!$bnUser->isAdmin() && !$bnUser->hasAcademicRole())
            ? route('profile.index')
            : route('profile.edit');
    @endphp
    <a href="{{ $bnProfileHref }}" class="app-nav-item app-nav-item-profile {{ $bnProfileActive ? 'active' : '' }}" aria-label="My account">
        @if($bnAvatar)
            <img src="{{ $bnAvatar }}" alt="" class="app-nav-profile-thumb">
        @else
            <i class="fas fa-user-circle"></i>
        @endif
        <span>Account</span>
    </a>
</nav>
@endonce

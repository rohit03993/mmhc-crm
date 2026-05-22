@php
    $navUser = auth()->user();
    $navProfile = $navUser->profile;
    $navAvatarUrl = ($navProfile && $navProfile->avatar_path)
        ? \Illuminate\Support\Facades\Storage::url($navProfile->avatar_path)
        : null;
    $navInitials = collect(preg_split('/\s+/', trim($navUser->name ?? 'U')))
        ->filter()
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<div class="dropdown mmhc-nav-profile">
    <button class="btn mmhc-nav-profile-btn dropdown-toggle"
            type="button"
            id="mmhcNavProfileDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Account menu">
        @if($navAvatarUrl)
            <img src="{{ $navAvatarUrl }}" alt="" class="mmhc-nav-profile-img">
        @else
            <span class="mmhc-nav-profile-initials" aria-hidden="true">{{ $navInitials ?: 'U' }}</span>
        @endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end mmhc-nav-profile-menu shadow" aria-labelledby="mmhcNavProfileDropdown">
        <li class="dropdown-header px-3 py-2">
            <div class="fw-semibold text-truncate">{{ $navUser->name }}</div>
            <div class="small text-muted">{{ $navUser->unique_id }} · {{ ucfirst(str_replace('_', ' ', $navUser->role)) }}</div>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        @if(!$navUser->isAdmin() && !$navUser->hasAcademicRole())
        <li>
            <a class="dropdown-item" href="{{ route('profile.index') }}">
                <i class="fas fa-user me-2 text-primary"></i>My profile
            </a>
        </li>
        @endif
        <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                <i class="fas fa-camera me-2 text-primary"></i>Update photo &amp; mobile
            </a>
        </li>
        @if(!$navUser->isAdmin())
        <li>
            <a class="dropdown-item" href="{{ route('documents.index') }}">
                <i class="fas fa-file-alt me-2 text-primary"></i>My documents
            </a>
        </li>
        @endif
        @if($navUser->isStaff() && $navUser->phone_verified_at)
        <li>
            <a class="dropdown-item" href="{{ route('profile.id-card') }}">
                <i class="fas fa-id-card me-2 text-primary"></i>Staff ID card
            </a>
        </li>
        @endif
        <li>
            <a class="dropdown-item" href="{{ route('dashboard') }}">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        <li>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>
        </li>
    </ul>
</div>

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
    $roleLabel = ucfirst(str_replace('_', ' ', $navUser->role));
    $profileDropdownId = $dropdownId ?? 'mmhcDesktopProfileDropdown';
@endphp
<div class="dropdown mmhc-app-profile-chip">
    <button type="button"
            class="mmhc-app-profile-chip__btn dropdown-toggle"
            id="{{ $profileDropdownId }}"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Account menu">
        @if($navAvatarUrl)
            <img src="{{ $navAvatarUrl }}" alt="" class="mmhc-app-profile-chip__avatar">
        @else
            <span class="mmhc-app-profile-chip__initials">{{ $navInitials ?: 'U' }}</span>
        @endif
        <span class="mmhc-app-profile-chip__text">
            <span class="mmhc-app-profile-chip__name">{{ $navUser->name }}</span>
            <span class="mmhc-app-profile-chip__meta">{{ $navUser->unique_id }} · {{ $roleLabel }}</span>
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end mmhc-app-profile-menu shadow" aria-labelledby="{{ $profileDropdownId }}">
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

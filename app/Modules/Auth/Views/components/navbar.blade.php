<nav class="navbar navbar-expand-lg navbar-light top-navbar">
    <div class="container-fluid">
        @auth
            <button class="btn btn-link text-dark d-lg-none me-1 px-2 border-0 mmhc-sidebar-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mmhcAppSidebar" aria-controls="mmhcAppSidebar" aria-label="Open menu" style="min-width:48px;min-height:48px;touch-action:manipulation;">
                <i class="fas fa-bars fa-lg"></i>
            </button>
        @endauth
        <a class="navbar-brand mmhc-navbar-brand" href="{{ auth()->check() ? route('dashboard') : url('/') }}">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--nav" onerror="this.onerror=null;this.src='{{ asset('images/med-logo.png') }}';">
            <span class="visually-hidden">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
        </a>

        @auth
        {{-- Mobile: notifications + profile (replaces 2nd hamburger / navbar-toggler) --}}
        <div class="d-flex d-lg-none align-items-center gap-1 ms-auto mmhc-navbar-mobile-actions">
            @if(!auth()->user()->hasAcademicRole())
            <div class="dropdown">
                <a class="btn btn-link text-dark position-relative mmhc-nav-bell px-2" href="#" id="communityNotificationDropdownMobile" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications" style="min-width:44px;min-height:44px;">
                    <i class="fas fa-bell fa-lg"></i>
                    @if(($communityUnreadNotificationsCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.65rem;">
                            {{ $communityUnreadNotificationsCount > 9 ? '9+' : $communityUnreadNotificationsCount }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="communityNotificationDropdownMobile" style="min-width:min(320px, 92vw);">
                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <form method="POST" action="{{ route('community.notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                        </form>
                    </li>
                    @forelse(($communityRecentNotifications ?? collect()) as $notification)
                        <li>
                            <form method="POST" action="{{ route('community.notifications.open', $notification) }}">
                                @csrf
                                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                <button type="submit" class="dropdown-item small text-start w-100 border-0 bg-transparent @if(is_null($notification->read_at)) fw-semibold @endif">
                                    <strong>{{ $notification->actor->name ?? 'Member' }}</strong>
                                    @if($notification->type === 'comment')
                                        commented on your post
                                    @elseif($notification->type === 'event_interest')
                                        responded on your event
                                    @else
                                        reacted on your post
                                    @endif
                                    <div class="text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
                    @endforelse
                </ul>
            </div>
            @endif
            @include('auth::components.navbar-profile-mobile')
        </div>
        @endauth

        @guest
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        @endguest

        <div class="collapse navbar-collapse d-none d-lg-flex" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard', 'academics.dashboard', 'staff.dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-home me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    @if(!auth()->user()->hasAcademicRole())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('community.*') ? 'active' : '' }}" href="{{ route('community.index') }}">
                                <i class="fas fa-users me-1"></i>
                                Community
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('academics.*') ? 'active' : '' }}" href="{{ route('academics.dashboard') }}">
                                <i class="fas fa-graduation-cap me-1"></i>
                                Academics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users') }}">
                                <i class="fas fa-users me-1"></i>
                                Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.page-content.index') }}">
                                <i class="fas fa-edit me-1"></i>
                                Edit Landing Page
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav">
                @auth
                    @if(!auth()->user()->hasAcademicRole())
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link position-relative" href="#" id="communityNotificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @if(($communityUnreadNotificationsCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $communityUnreadNotificationsCount > 9 ? '9+' : $communityUnreadNotificationsCount }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="communityNotificationDropdown" style="min-width: 320px;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Community Notifications</span>
                                <form method="POST" action="{{ route('community.notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                                </form>
                            </li>
                            @forelse(($communityRecentNotifications ?? collect()) as $notification)
                                <li>
                                    <form method="POST" action="{{ route('community.notifications.open', $notification) }}">
                                        @csrf
                                        <input type="hidden" name="page" value="{{ request('page', 1) }}">
                                        <button type="submit" class="dropdown-item small text-start w-100 border-0 bg-transparent @if(is_null($notification->read_at)) fw-semibold @endif">
                                            <strong>{{ $notification->actor->name ?? 'Member' }}</strong>
                                            @if($notification->type === 'comment')
                                                commented on your post
                                            @elseif($notification->type === 'event_interest')
                                                responded <strong>{{ $notification->meta['status'] ?? 'interested' }}</strong> to your event
                                            @else
                                                reacted ({{ $notification->meta['reaction_type'] ?? 'like' }}) on your post
                                            @endif
                                            <div class="text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text text-muted small">No notifications yet.</span></li>
                            @endforelse
                        </ul>
                    </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $deskProfile = auth()->user()->profile;
                                $deskAvatar = ($deskProfile && $deskProfile->avatar_path) ? \Illuminate\Support\Facades\Storage::url($deskProfile->avatar_path) : null;
                            @endphp
                            @if($deskAvatar)
                                <img src="{{ $deskAvatar }}" alt="" class="mmhc-nav-profile-img mmhc-nav-profile-img--sm">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                            <span>{{ auth()->user()->name }}</span>
                            <span class="badge bg-light text-dark">{{ auth()->user()->unique_id }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if(!auth()->user()->isAdmin() && !auth()->user()->hasAcademicRole())
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user me-2"></i>My profile</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-camera me-2"></i>Update photo &amp; mobile</a></li>
                            @if(!auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('documents.index') }}"><i class="fas fa-file-alt me-2"></i>My documents</a></li>
                            @endif
                            @if(auth()->user()->isStaff() && auth()->user()->phone_verified_at)
                            <li><a class="dropdown-item" href="{{ route('profile.id-card') }}"><i class="fas fa-id-card me-2"></i>Staff ID card</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auth.login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auth.register') }}">
                            <i class="fas fa-user-plus me-1"></i>
                            Register
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

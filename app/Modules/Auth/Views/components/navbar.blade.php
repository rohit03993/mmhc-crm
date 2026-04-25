<nav class="navbar navbar-expand-lg navbar-light top-navbar d-none d-md-flex">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ auth()->check() && !auth()->user()->hasAcademicRole() ? route('community.index') : route('dashboard') }}">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--nav">
            <span class="visually-hidden">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ auth()->user()->hasAcademicRole() ? route('dashboard') : route('community.index') }}">
                            <i class="fas fa-home me-1"></i>
                            {{ auth()->user()->hasAcademicRole() ? 'Dashboard' : 'Community' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('community.index') }}">
                            <i class="fas fa-users me-1"></i>
                            Community
                        </a>
                    </li>
                    
                    @if(auth()->user()->isAdmin())
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            {{ auth()->user()->name }}
                            <span class="badge bg-light text-dark ms-1">{{ auth()->user()->unique_id }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ auth()->user()->hasAcademicRole() ? route('dashboard') : route('community.index') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>{{ auth()->user()->hasAcademicRole() ? 'Dashboard' : 'Community' }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="fas fa-user-cog me-2"></i>Profile Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
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
                        <a class="nav-link" href="{{ route('auth.academics-login') }}" title="College admin, faculty & students">
                            <i class="fas fa-graduation-cap me-1"></i>
                            Academics
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

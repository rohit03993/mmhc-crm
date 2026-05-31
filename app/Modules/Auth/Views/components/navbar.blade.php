@php
    $isAdminNav = auth()->check() && auth()->user()->isAdmin();
@endphp
<nav class="navbar navbar-expand-lg navbar-light top-navbar top-navbar--modern {{ $isAdminNav ? 'top-navbar--admin' : 'top-navbar--member' }}">
    <div class="container-fluid mmhc-topnav-inner">
        @auth
            <button type="button"
                    class="mmhc-sidebar-toggle d-lg-none"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mmhcAppSidebar"
                    aria-controls="mmhcAppSidebar"
                    aria-label="Open menu">
                <span class="mmhc-hamburger" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>

            <a class="navbar-brand mmhc-navbar-brand" href="{{ route('dashboard') }}">
                @include('auth::components.brand-logo-nav')
                <span class="visually-hidden">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
            </a>

            <div class="d-flex d-lg-none align-items-center gap-1 ms-auto mmhc-navbar-mobile-actions">
                @include('auth::components.navbar-notifications', [
                    'dropdownId' => 'communityNotificationDropdownMobile',
                    'buttonClass' => 'mmhc-nav-notifications-btn',
                ])
                @include('auth::components.navbar-profile-mobile')
            </div>

            <div class="collapse navbar-collapse mmhc-app-nav-collapse d-none d-lg-flex" id="navbarNav">
                <ul class="mmhc-app-nav-pills {{ $isAdminNav ? 'mmhc-app-nav-pills--admin' : '' }}">
                    <li>
                        <a class="mmhc-app-nav-pill {{ request()->routeIs('dashboard', 'academics.dashboard', 'staff.dashboard') ? 'is-active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="fas fa-home" aria-hidden="true"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="mmhc-app-nav-pill {{ request()->routeIs('community.*') ? 'is-active' : '' }}"
                           href="{{ route('community.index') }}">
                            <i class="fas fa-users" aria-hidden="true"></i>
                            Community
                        </a>
                    </li>
                    @if($isAdminNav)
                        <li>
                            <a class="mmhc-app-nav-pill {{ request()->routeIs('academics.*') ? 'is-active' : '' }}"
                               href="{{ route('academics.dashboard') }}">
                                <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                                Academics
                            </a>
                        </li>
                        <li>
                            <a class="mmhc-app-nav-pill {{ request()->routeIs('admin.users', 'admin.users.*', 'admin.profiles*') ? 'is-active' : '' }}"
                               href="{{ route('admin.users') }}">
                                <i class="fas fa-user-cog" aria-hidden="true"></i>
                                Manage Users
                            </a>
                        </li>
                        <li>
                            <a class="mmhc-app-nav-pill {{ request()->routeIs('admin.page-content.*') ? 'is-active' : '' }}"
                               href="{{ route('admin.page-content.index') }}">
                                <i class="fas fa-edit" aria-hidden="true"></i>
                                Landing Page
                            </a>
                        </li>
                    @endif
                </ul>
                <div class="mmhc-app-nav-actions">
                    @include('auth::components.navbar-notifications', [
                        'dropdownId' => 'communityNotificationDropdown',
                    ])
                    @include('auth::components.navbar-profile-desktop', ['dropdownId' => 'mmhcDesktopProfileDropdown'])
                </div>
            </div>
        @else
            <a class="navbar-brand mmhc-navbar-brand" href="{{ url('/') }}">
                @include('auth::components.brand-logo-nav')
                <span class="visually-hidden">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
                </ul>
            </div>
        @endauth
    </div>
</nav>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MeD Miracle Health Care') }} - @yield('title', 'Dashboard')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    @yield('head')
    
    <style>
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .top-navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .top-navbar .navbar-nav .nav-link {
            color: #1f2937;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .top-navbar .navbar-nav .nav-link:hover,
        .top-navbar .navbar-nav .nav-link:focus {
            color: #2563eb;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(190deg, #312e81 0%, #1d4ed8 50%, #0f172a 100%);
            box-shadow: 2px 0 18px rgba(15, 23, 42, 0.35);
        }

        .main-content {
            background-color: #f1f5f9;
        }

        .card {
            border: none;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            border: none;
        }

        .brand-logo {
            display: block;
            width: 100%;
            max-width: 160px;
            height: auto;
        }

        .brand-logo--nav {
            max-width: 140px;
        }

        .brand-logo--sidebar {
            max-width: 180px;
            margin: 0 auto;
        }

        .brand-logo--auth {
            max-width: 200px;
            margin: 0 auto 1.25rem;
        }

        .brand-logo-card {
            background: linear-gradient(160deg, rgba(255,255,255,0.20) 0%, rgba(255,255,255,0.06) 100%);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 20px;
            padding: 1.5rem 1.25rem;
            margin-bottom: 0.5rem;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
        }

        .brand-tagline {
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(241, 245, 249, 0.95);
        }

        .sidebar .nav-link {
            border-radius: 14px;
            margin: 0.25rem 0.5rem;
            padding: 0.8rem 1rem;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            color: rgba(255,255,255,0.85);
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: rgba(255,255,255,0.8);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.25s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.18);
            transform: translateX(4px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.2);
            color: #ffffff;
        }

        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            transform: scaleY(1);
        }

        .sidebar .nav-link i {
            width: 22px;
            text-align: center;
            margin-right: 0.75rem;
        }

        .sidebar .nav-item.mt-3 {
            margin-top: 2rem !important;
            border-top: 1px solid rgba(148, 163, 184, 0.25);
            padding-top: 1rem;
        }

        .sidebar .text-center {
            padding: 1.6rem 1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    @include('auth::components.navbar')
    <div class="container-fluid">
        <div class="row">
            @if(auth()->check())
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="text-center mb-4">
                            <div class="brand-logo-card">
                                <img src="{{ asset('images/med-logo.png') }}" alt="MeD Miracle Health Care" class="brand-logo brand-logo--sidebar">
                            </div>
                            <div class="brand-tagline">Miracle Health Care</div>
                        </div>
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">
                                    <i class="fas fa-home me-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i>
                                    My Profile
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Documents
                                </a>
                            </li>
                            
                            @if(auth()->user()->isPatient())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('staff.index') }}">
                                    <i class="fas fa-users me-2"></i>
                                    Available Staff
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('services.index') }}">
                                    <i class="fas fa-user-md me-2"></i>
                                    Request Service
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('services.my-requests') }}">
                                    <i class="fas fa-list me-2"></i>
                                    My Requests
                                </a>
                            </li>
                            @endif
                            
                            @if(auth()->user()->isStaff())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('staff.dashboard') }}">
                                    <i class="fas fa-tasks me-2"></i>
                                    My Assignments
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white {{ request()->routeIs('rewards.*') ? 'active' : '' }}" href="{{ route('rewards.index') }}">
                                    <i class="fas fa-gift me-2"></i>
                                    Rewards & Points
                                </a>
                            </li>
                            @endif
                            
                            @if(auth()->user()->isPatient())
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('plans.index') }}">
                                    <i class="fas fa-clipboard-list me-2"></i>
                                    Healthcare Plans
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('subscriptions.index') }}">
                                    <i class="fas fa-credit-card me-2"></i>
                                    My Subscriptions
                                </a>
                            </li>
                            @endif
                            
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.users') || request()->routeIs('admin.profiles*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                        <i class="fas fa-users me-2"></i>
                                        Manage Users
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.service-requests*') ? 'active' : '' }}" href="{{ route('admin.service-requests') }}">
                                        <i class="fas fa-tasks me-2"></i>
                                        Service Requests
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.referrals.*') ? 'active' : '' }}" href="{{ route('admin.referrals.index') }}">
                                        <i class="fas fa-share-alt me-2"></i>
                                        Referral Management
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}" href="{{ route('admin.rewards.index') }}">
                                        <i class="fas fa-star me-2"></i>
                                        Reward Submissions
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.page-content*') ? 'active' : '' }}" href="{{ route('admin.page-content.index') }}">
                                        <i class="fas fa-edit me-2"></i>
                                        Edit Landing Page
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}" href="#">
                                        <i class="fas fa-list-alt me-2"></i>
                                        Manage Subscriptions
                                        <small class="d-block text-white-50" style="font-size: 0.7rem;">Coming Soon</small>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white {{ request()->routeIs('admin.service-requests*') ? 'active' : '' }}" href="{{ route('admin.service-requests') }}">
                                        <i class="fas fa-user-md me-2"></i>
                                        Service Requests
                                    </a>
                                </li>
                            @endif
                            
                            <li class="nav-item mt-3">
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link text-white btn btn-link">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <span class="badge bg-primary">{{ auth()->user()->unique_id }}</span>
                                <span class="badge bg-secondary">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </main>
            @else
                <!-- Auth pages -->
                <main class="col-12">
                    @yield('content')
                </main>
            @endif
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

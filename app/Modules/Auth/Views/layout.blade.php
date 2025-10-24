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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .navbar-brand {
            font-weight: bold;
        }
        
        /* MeD Miracle Health Care Logo */
        .med-logo-icon {
            position: relative;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-heart {
            position: absolute;
            top: 2px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 18px;
            z-index: 3;
            color: #28a745;
        }
        
        .logo-pill {
            position: absolute;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 6px;
            background: #007bff;
            border-radius: 3px;
            z-index: 1;
        }
        
        .logo-pill::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            width: 24px;
            height: 6px;
            background: #007bff;
            border-radius: 3px;
        }
        
        .med-logo-text {
            text-align: left;
        }
        
        .med-text {
            font-size: 1.8rem;
            font-weight: 900;
            color: #2c3e50;
            line-height: 1;
            letter-spacing: -1px;
        }
        
        .miracle-text {
            font-size: 0.7rem;
            color: #6c757d;
            font-weight: 500;
            line-height: 1;
            margin-top: 2px;
        }
        
        /* Mobile Responsive Logo */
        @media (max-width: 768px) {
            .med-logo-icon {
                width: 40px;
                height: 40px;
            }
            
            .logo-heart {
                width: 14px;
                height: 12px;
                top: 2px;
            }
            
            .logo-heart::before {
                width: 14px;
                height: 12px;
            }
            
            .logo-pill {
                width: 20px;
                height: 5px;
                bottom: 5px;
            }
            
            .logo-pill::before {
                width: 20px;
                height: 5px;
            }
            
            .med-text {
                font-size: 1.4rem;
            }
            
            .miracle-text {
                font-size: 0.6rem;
            }
        }
        
        @media (max-width: 576px) {
            .med-logo-icon {
                width: 35px;
                height: 35px;
            }
            
            .logo-heart {
                width: 12px;
                height: 10px;
                top: 1px;
            }
            
            .logo-heart::before {
                width: 12px;
                height: 10px;
            }
            
            .logo-pill {
                width: 18px;
                height: 4px;
                bottom: 4px;
            }
            
            .logo-pill::before {
                width: 18px;
                height: 4px;
            }
            
            .med-text {
                font-size: 1.2rem;
            }
            
            .miracle-text {
                font-size: 0.55rem;
            }
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
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <!-- MeD Text Logo -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white mb-1">
                                        <span class="text-2xl">M</span><span class="text-xl italic">e</span><span class="text-2xl">D</span>
                                    </div>
                                    <div class="text-xs text-gray-300">Miracle Health Care</div>
                                </div>
                            </div>
                        </div>
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('dashboard') }}">
                                    <i class="fas fa-home me-2"></i>
                                    Dashboard
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('profile.index') }}">
                                    <i class="fas fa-user me-2"></i>
                                    My Profile
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link text-white" href="{{ route('documents.index') }}">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Documents
                                </a>
                            </li>
                            
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
                            
                            @if(auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('admin.users') }}">
                                        <i class="fas fa-users me-2"></i>
                                        Manage Users
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('admin.page-content.index') }}">
                                        <i class="fas fa-edit me-2"></i>
                                        Edit Landing Page
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-white" href="{{ route('admin.subscriptions') }}">
                                        <i class="fas fa-list-alt me-2"></i>
                                        Manage Subscriptions
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

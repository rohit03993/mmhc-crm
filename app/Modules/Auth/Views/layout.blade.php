<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MMHC CRM') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
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
                            <h4 class="text-white">
                                <i class="fas fa-heartbeat me-2"></i>
                                MMHC CRM
                            </h4>
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

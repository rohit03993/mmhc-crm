<nav class="navbar navbar-expand-lg navbar-light top-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/med-logo.png') }}" alt="MeD Miracle Health Care" class="brand-logo brand-logo--nav">
            <span class="visually-hidden">MeD Miracle Health Care</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-home me-1"></i>
                            Dashboard
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            {{ auth()->user()->name }}
                            <span class="badge bg-light text-dark ms-1">{{ auth()->user()->unique_id }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
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

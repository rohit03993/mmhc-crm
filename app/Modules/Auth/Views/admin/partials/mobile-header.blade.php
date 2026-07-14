@php
    $headerTitle = trim($__env->yieldContent('page-title')) ?: 'Admin';
    $backUrl = $adminMobileBackUrl ?? route('admin.dashboard');
@endphp
<header class="app-header-mobile admin-mobile-header d-md-none" role="banner">
    <div class="app-header-content">
        <div class="app-header-left">
            <a href="{{ $backUrl }}" class="app-back-btn" aria-label="Back to admin home">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div class="min-w-0">
                <div class="app-header-title text-truncate">{{ $headerTitle }}</div>
                <div class="app-header-subtitle">Admin · {{ auth()->user()->unique_id }}</div>
            </div>
        </div>
        <div class="app-header-right">
            <button type="button"
                    class="app-header-icon"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mmhcAppSidebar"
                    aria-label="Open menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <a href="{{ route('profile.edit') }}" class="app-header-icon" title="Profile" aria-label="Profile">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</header>

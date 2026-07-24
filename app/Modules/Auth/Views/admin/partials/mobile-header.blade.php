@php
    $headerTitle = trim($__env->yieldContent('page-title')) ?: 'Admin';
    $backUrl = $adminMobileBackUrl ?? route('admin.dashboard');
@endphp
{{-- Compact page context only — menu/profile live in the top navbar --}}
<header class="app-header-mobile admin-mobile-header d-md-none" role="banner">
    <div class="app-header-content">
        <div class="app-header-left">
            <a href="{{ $backUrl }}" class="app-back-btn" aria-label="Back to admin home">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
            </a>
            <div class="min-w-0">
                <div class="app-header-title text-truncate">{{ $headerTitle }}</div>
                <div class="app-header-subtitle text-truncate">Admin</div>
            </div>
        </div>
    </div>
</header>

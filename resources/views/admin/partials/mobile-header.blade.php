@php
    $headerTitle = $adminMobileTitle ?? 'Admin';
    $backUrl = $adminMobileBackUrl ?? route('admin.dashboard');
@endphp
<header class="mmhc-admin-mobile-header" role="banner">
    <div class="mmhc-admin-mobile-header__bar">
        <a href="{{ $backUrl }}" class="mmhc-admin-mobile-header__back" aria-label="Back to dashboard">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </a>
        <div class="mmhc-admin-mobile-header__titles">
            <h1 class="mmhc-admin-mobile-header__title">{{ $headerTitle }}</h1>
            <p class="mmhc-admin-mobile-header__subtitle mb-0">MMHC Admin</p>
        </div>
    </div>
</header>

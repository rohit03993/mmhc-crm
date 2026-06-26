@php
    use App\Modules\Academics\Support\AcademicsMobileUi;
    $backUrl = $academicsMobileBackUrl ?? AcademicsMobileUi::backUrl();
    $headerTitle = trim($__env->yieldContent('page-title')) ?: 'Academics';
@endphp
<header class="acad-mobile-header d-md-none" role="banner">
    <div class="acad-mobile-header__bar">
        <a href="{{ $backUrl }}" class="acad-mobile-header__back" aria-label="Go back">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </a>
        <div class="acad-mobile-header__titles">
            <h1 class="acad-mobile-header__title">{{ $headerTitle }}</h1>
            <p class="acad-mobile-header__subtitle mb-0">Academics</p>
        </div>
        <button type="button"
                class="acad-mobile-header__menu"
                data-bs-toggle="offcanvas"
                data-bs-target="#mmhcAppSidebar"
                aria-label="Open menu">
            <i class="fas fa-ellipsis-v" aria-hidden="true"></i>
        </button>
    </div>
</header>

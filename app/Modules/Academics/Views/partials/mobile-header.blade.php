@php
    $backUrl = $academicsMobileBackUrl ?? route('academics.dashboard');
    $headerTitle = trim($__env->yieldContent('page-title')) ?: 'Academics';
@endphp
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ $backUrl }}" class="btn btn-link text-white p-0 me-3" aria-label="Back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0 text-truncate">{{ $headerTitle }}</h5>
    </div>
</div>

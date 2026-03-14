@extends('auth::layout')

@section('title', ($academicsLogin ?? false) ? 'Academics login - MMHC CRM' : 'Login - MMHC CRM')

@section('head')
<style>
    .auth-page-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .auth-page-wrapper::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        position: relative;
        z-index: 1;
        max-width: 440px;
        width: 100%;
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .login-card-header {
        text-align: center;
        padding: 2.5rem 2rem 1.5rem;
    }

    .login-card-header .brand-logo {
        max-width: 180px;
        height: auto;
        margin: 0 auto 1.5rem auto;
        display: block;
        filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.2));
    }

    .login-card-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .login-card-header p {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }

    .login-card-body {
        padding: 0 2rem 2.5rem;
    }

    .form-floating-modern {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-floating-modern label {
        display: block;
        margin-bottom: 0.5rem;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .form-floating-modern input:focus ~ label {
        color: #667eea;
    }

    .login-tabs .nav-link { border-radius: 12px; font-weight: 600; color: #64748b; }
    .login-tabs .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    #login_phone { padding-left: 3rem; }

    .form-floating-modern .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 3;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .form-floating-modern input:focus ~ .input-icon {
        color: #667eea;
        transform: translateY(-50%) scale(1.1);
    }

    .form-floating-modern input {
        width: 100%;
        height: 56px;
        padding: 1rem 1rem 1rem 52px;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.3s ease;
        background: #ffffff;
        color: #1e293b;
    }

    .form-floating-modern input::placeholder {
        color: #cbd5e1;
    }

    .form-floating-modern input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-floating-modern input.is-invalid {
        border-color: #ef4444;
    }

    .form-floating-modern input.is-invalid ~ label {
        color: #ef4444;
    }

    .password-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        z-index: 3;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }

    .password-toggle:hover {
        color: #667eea;
        transform: translateY(-50%) scale(1.1);
    }

    .remember-me-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.75rem;
    }

    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-check-modern input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #667eea;
        border-radius: 6px;
    }

    .form-check-modern label {
        color: #475569;
        font-size: 0.95rem;
        cursor: pointer;
        margin: 0;
        user-select: none;
    }

    .forgot-password-link {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .forgot-password-link:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    .btn-login {
        width: 100%;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 14px;
        color: white;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        background: #fee2e2;
        color: #991b1b;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
    }

    .alert-modern ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .alert-modern li {
        margin-bottom: 0.25rem;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 2rem 0;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider::before {
        margin-right: 1rem;
    }

    .divider::after {
        margin-left: 1rem;
    }

    .signup-link {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .signup-link p {
        color: #64748b;
        margin: 0;
        font-size: 0.95rem;
    }

    .signup-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .signup-link a:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    @media (max-width: 576px) {
        .login-card-header {
            padding: 2rem 1.5rem 1rem;
        }

        .login-card-body {
            padding: 0 1.5rem 2rem;
        }

        .login-card-header h2 {
            font-size: 1.5rem;
        }
    }

    /* Academics portal: no page scroll, fit viewport, image on left */
    body:has(.academics-login-wrapper),
    html:has(.academics-login-wrapper) {
        overflow: hidden !important;
        height: 100% !important;
    }
    .academics-login-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        height: 100vh;
        max-height: 100vh;
        display: flex;
        background: #f8fafc;
        z-index: 10;
        overflow: hidden;
    }
    .academics-login-left {
        flex: 0 0 50%;
        background: linear-gradient(165deg, #1e3a5f 0%, #2d4a6f 50%, #1e3a5f 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 2rem;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .academics-login-left-inner {
        width: 100%;
        max-width: 100%;
        min-height: min-content;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1rem;
    }
    .academics-left-featured {
        background: rgba(255,255,255,0.08);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 0;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 4px 20px rgba(0,0,0,0.25);
        flex: 0 0 auto;
        min-height: 52vh;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .academics-left-featured .academics-left-slides {
        border-radius: 12px;
        overflow: hidden;
    }
    .academics-left-slides {
        position: relative;
        flex: 1;
        min-height: 0;
    }
    .academics-left-slide {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        pointer-events: none;
    }
    .academics-left-slide.academics-slide-active {
        opacity: 1;
        pointer-events: auto;
    }
    .academics-left-featured-img {
        width: 100%;
        height: 100%;
        min-height: 42vh;
        flex: 1;
        object-fit: contain;
        object-position: center;
        display: block;
        background: rgba(0,0,0,0.15);
    }
    .academics-left-featured-caption {
        padding: 0.75rem 1rem;
        color: #fff;
        text-align: center;
        flex-shrink: 0;
    }
    .academics-left-featured-caption .name { font-size: 1rem; font-weight: 700; display: block; }
    .academics-left-featured-caption .sub { font-size: 0.8rem; opacity: 0.9; }
    .academics-left-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        flex-shrink: 0;
    }
    .academics-left-thumb-card {
        flex: 0 0 calc(25% - 0.5rem);
        max-width: calc(25% - 0.5rem);
        background: rgba(255,255,255,0.08);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 2px 12px rgba(0,0,0,0.2);
    }
    .academics-thumb-btn {
        cursor: pointer;
        padding: 0;
        border: none;
        text-align: left;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .academics-thumb-btn:hover { transform: scale(1.03); box-shadow: 0 4px 16px rgba(0,0,0,0.3); }
    .academics-thumb-btn.active { border-color: rgba(255,255,255,0.8); box-shadow: 0 0 0 2px rgba(255,255,255,0.4); }
    .academics-left-thumb-card img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
        object-position: center;
        display: block;
        background: rgba(0,0,0,0.1);
    }
    .academics-left-thumb-card .name {
        padding: 0.5rem 0.35rem;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.2;
    }
    @media (min-width: 992px) {
        .academics-left-thumb-card img { aspect-ratio: 4/3; object-fit: contain; }
        .academics-left-thumb-card { flex: 0 0 calc(25% - 0.5rem); max-width: calc(25% - 0.5rem); }
    }
    @media (max-width: 991px) {
        .academics-left-thumb-card { flex: 0 0 calc(50% - 0.35rem); max-width: calc(50% - 0.35rem); }
    }
    @media (max-width: 400px) {
        .academics-left-thumb-card { flex: 0 0 calc(50% - 0.25rem); max-width: calc(50% - 0.25rem); }
    }
    .academics-login-left-img-wrap {
        background: rgba(255,255,255,0.95);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        border: 2px solid rgba(255,255,255,0.3);
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    .academics-login-left-img { max-width: 100%; max-height: 22vh; width: auto; height: auto; object-fit: contain; display: block; }
    .academics-login-left-img-placeholder { text-align: center; color: #1e3a5f; padding: 1rem; }
    .academics-placeholder-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.9; }
    .academics-placeholder-text { font-size: 1rem; font-weight: 600; }
    .academics-hero-block {
        color: #fff;
        margin-bottom: 1.25rem;
    }
    .academics-hero-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        letter-spacing: -0.02em;
    }
    .academics-hero-sub {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 1.25rem;
    }
    .academics-hero-desc {
        font-size: 0.95rem;
        line-height: 1.6;
        opacity: 0.85;
    }
    .academics-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .academics-badge {
        background: rgba(255,255,255,0.15);
        color: #fff;
        padding: 0.4rem 0.9rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .academics-login-right {
        flex: 0 0 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem 2rem;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .academics-login-right-inner {
        width: 100%;
        max-width: 400px;
        flex-shrink: 0;
    }
    .academics-logo {
        max-width: 140px;
        height: auto;
        margin-bottom: 0.5rem;
        display: block;
    }
    .academics-portal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
        padding-bottom: 0.35rem;
        border-bottom: 3px solid #0ea5e9;
        display: inline-block;
    }
    .academics-portal-desc {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }
    .academics-main-login {
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }
    .academics-main-login a {
        color: #0ea5e9;
        text-decoration: none;
        font-weight: 500;
    }
    .academics-main-login a:hover { text-decoration: underline; }
    .academics-form-wrap .login-tabs .nav-link.active {
        background: #0ea5e9;
        color: #fff;
    }
    .academics-form-wrap .btn-login {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.35);
    }
    .academics-form-wrap .btn-login:hover {
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.45);
    }
    .academics-form-wrap .form-floating-modern input:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
    }
    .academics-form-wrap .forgot-password-link { color: #0ea5e9; }
    .academics-form-wrap .signup-link a { color: #0ea5e9; }
    .academics-login-footer {
        margin-top: 1.25rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e8f0;
        font-size: 0.75rem;
        color: #64748b;
    }
    .academics-login-footer a {
        color: #0ea5e9;
        text-decoration: none;
        margin-left: 0.5rem;
    }
    .academics-login-footer a:hover { text-decoration: underline; }
    .academics-powered {
        margin-top: 0.5rem;
        margin-bottom: 0;
    }
    @media (max-width: 991px) {
        .academics-login-wrapper { flex-direction: column; overflow-y: auto; }
        .academics-login-left {
            flex: none;
            order: 2;
            min-height: 0;
            max-height: none;
            padding: 1rem;
            overflow: visible;
        }
        .academics-login-right {
            flex: none;
            order: 1;
            padding: 1.25rem 1rem;
            min-height: 0;
        }
        .academics-left-featured { flex: none; min-height: 240px; }
        .academics-left-featured-img {
            min-height: 220px;
            max-height: 55vh;
            height: auto;
            flex: none;
            object-fit: contain;
        }
        .academics-left-thumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 0.75rem;
        }
        .academics-left-thumb-card .name { font-size: 0.65rem; }
        .academics-login-left-img-wrap { min-height: 90px; padding: 0.75rem; margin-bottom: 0.75rem; }
        .academics-login-left-img { max-height: 16vh; }
        .academics-hero-title { font-size: 1.35rem; }
        .academics-hero-sub, .academics-hero-desc { font-size: 0.85rem; }
    }
    /* Fallback when :has() not supported - use JS to add class */
    body.academics-login-page,
    body.academics-login-page html { overflow: hidden !important; height: 100% !important; }
</style>
@endsection

@section('content')
@if(!empty($academicsLogin))
{{-- Academics portal: Sharda-style two-column layout (presentation only; same form/functionality) --}}
<div class="academics-login-wrapper">
    <div class="academics-login-left">
        <div class="academics-login-left-inner">
            @if(isset($achievementMedia) && $achievementMedia->isNotEmpty())
            {{-- Carousel: same images as Achievements & Media Coverage – slides one after another --}}
            <div class="academics-left-featured" id="academicsLeftCarousel" data-total="{{ $achievementMedia->count() }}">
                <div class="academics-left-slides">
                    @foreach($achievementMedia as $index => $item)
                    <div class="academics-left-slide {{ $index === 0 ? 'academics-slide-active' : '' }}" data-academics-slide="{{ $index }}">
                        <img src="{{ storage_asset($item->image_path) ?? '#' }}" alt="{{ $item->caption ?? 'Achievement' }}" class="academics-left-featured-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22 viewBox=%220 0 400 200%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22200%22/%3E%3Ctext fill=%22%239ca3af%22 font-size=%2216%22 x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22%3EAchievement%3C/text%3E%3C/svg%3E';">
                        <div class="academics-left-featured-caption">
                            <span class="name">{{ $item->caption ?? 'Achievements & Media' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="academics-left-thumbs" id="academicsLeftThumbs">
                @foreach($achievementMedia->take(6) as $index => $item)
                <button type="button" class="academics-left-thumb-card academics-thumb-btn {{ $index === 0 ? 'active' : '' }}" data-academics-goto="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}">
                    <img src="{{ storage_asset($item->image_path) ?? '#' }}" alt="" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E';">
                    <div class="name">{{ Str::limit($item->caption ?? 'Media', 12) }}</div>
                </button>
                @endforeach
            </div>
            @else
            <div class="academics-login-left-img-wrap">
                <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="academics-login-left-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div class="academics-login-left-img-placeholder" style="display:none;" aria-hidden="true">
                    <span class="academics-placeholder-icon"><i class="fas fa-graduation-cap"></i></span>
                    <span class="academics-placeholder-text">Academic Portal</span>
                </div>
            </div>
            <div class="academics-hero-block">
                <h3 class="academics-hero-title">MeD Miracle Health Care</h3>
                <p class="academics-hero-sub">Academic Portal</p>
                <p class="academics-hero-desc">For college administrators, faculty &amp; students. Sign in to access your dashboard, assignments and reports.</p>
            </div>
            <div class="academics-badges">
                <span class="academics-badge">Academic Portal</span>
            </div>
            @endif
        </div>
    </div>
    <div class="academics-login-right">
        <div class="academics-login-right-inner">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="academics-logo">
            <h1 class="academics-portal-title">Academics portal</h1>
            <p class="academics-portal-desc">Sign in for college admin, faculty &amp; students. You'll go to the Academics dashboard.</p>
            <p class="academics-main-login">Patients or caregivers? <a href="{{ route('auth.login') }}">Use main login</a>.</p>
            <div class="academics-form-wrap">
                @include('auth::partials.login-form')
            </div>
            <footer class="academics-login-footer">
                <span>©{{ date('Y') }}, MeD Miracle Academic Portal</span>
                <a href="{{ url('/') }}">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</a>
                <p class="academics-powered">Powered by <strong>MeD Miracle</strong></p>
            </footer>
        </div>
    </div>
</div>
@else
<div class="auth-page-wrapper">
    <div class="login-card">
        <div class="login-card-header">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MED Miracle Health Care' }}" class="brand-logo">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue.</p>
            <p class="small text-muted mt-2 mb-0">College admin, faculty or students? <a href="{{ route('auth.academics-login') }}">Academics login</a>.</p>
        </div>
        <div class="login-card-body">
            @include('auth::partials.login-form')
        </div>
    </div>
</div>
@endif

<script>
(function() {
    var w = document.querySelector('.academics-login-wrapper');
    if (w) { document.body.classList.add('academics-login-page'); }
})();

(function() {
    var carousel = document.getElementById('academicsLeftCarousel');
    if (!carousel) return;
    var slides = carousel.querySelectorAll('.academics-left-slide');
    var total = slides.length;
    if (total <= 1) return;
    var current = 0;
    var interval = 5000;

    function goToSlide(index) {
        current = (index + total) % total;
        slides.forEach(function(s, i) {
            s.classList.toggle('academics-slide-active', i === current);
        });
        var thumbs = document.querySelectorAll('#academicsLeftThumbs .academics-thumb-btn');
        thumbs.forEach(function(t, i) {
            t.classList.toggle('active', i === current);
        });
    }

    var tid = setInterval(function() { goToSlide(current + 1); }, interval);

    document.getElementById('academicsLeftThumbs') && document.getElementById('academicsLeftThumbs').addEventListener('click', function(e) {
        var btn = e.target.closest('.academics-thumb-btn');
        if (!btn || btn.getAttribute('data-academics-goto') === null) return;
        goToSlide(parseInt(btn.getAttribute('data-academics-goto'), 10));
        clearInterval(tid);
        tid = setInterval(function() { goToSlide(current + 1); }, interval);
    });
})();

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggleIcon.classList.remove('fa-eye');
        passwordToggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordToggleIcon.classList.remove('fa-eye-slash');
        passwordToggleIcon.classList.add('fa-eye');
    }
}

// Auto-focus email field on page load
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});

// Add smooth form submission for email form
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        var submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...'; }
    });
}
// Activate phone tab on load if session says so
if (document.querySelector('#tab-phone.active')) {
    document.querySelector('#login_phone') && document.querySelector('#login_phone').focus();
}
if (document.getElementById('otp')) {
    document.getElementById('otp').focus();
}
</script>
@endsection

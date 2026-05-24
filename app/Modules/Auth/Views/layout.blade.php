<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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

    <link rel="stylesheet" href="{{ asset('css/mobile-crm.css') }}">
    <link rel="stylesheet" href="{{ asset('css/capacitor-app.css') }}">
    
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
        
        /* Mobile spacing — details in public/css/mobile-crm.css */
        @media (max-width: 767.98px) {
            .main-content {
                background-color: #f1f5f9;
            }
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

        /* Global readability baseline across CRM pages */
        .main-content,
        .main-content .card,
        .main-content .card-body,
        .main-content .card-header,
        .main-content .table,
        .main-content .table td,
        .main-content .table th,
        .main-content p,
        .main-content span,
        .main-content small,
        .main-content div,
        .main-content label,
        .main-content h1,
        .main-content h2,
        .main-content h3,
        .main-content h4,
        .main-content h5,
        .main-content h6 {
            color: #1f2937;
        }

        .main-content .text-muted {
            color: #6b7280 !important;
        }

        /* Preserve white text for intentional dark/gradient backgrounds */
        .main-content .bg-dark,
        .main-content .bg-primary,
        .main-content .bg-secondary,
        .main-content .bg-success,
        .main-content .bg-danger,
        .main-content .bg-info,
        .main-content .bg-warning,
        .main-content .bg-dark *,
        .main-content .bg-primary *,
        .main-content .bg-secondary *,
        .main-content .bg-success *,
        .main-content .bg-danger *,
        .main-content .bg-info *,
        .main-content .bg-warning *,
        .main-content .referral-link-header,
        .main-content .referral-link-header *,
        .main-content .service-card-header,
        .main-content .service-card-header *,
        .main-content .total-earnings-banner,
        .main-content .total-earnings-banner *,
        .main-content [class*="bg-gradient"],
        .main-content [class*="bg-gradient"] * {
            color: #ffffff !important;
        }

        /*
         * Inverse surfaces (dark strips): keep light text vs global .main-content #1f2937 rules.
         * Academics dashboard hero is a light card; only .mmhc-inverse-surface needs overrides here.
         */
        .main-content .mmhc-inverse-surface {
            color: #f8fafc !important;
        }
        .main-content .mmhc-inverse-surface h1,
        .main-content .mmhc-inverse-surface h2,
        .main-content .mmhc-inverse-surface h3,
        .main-content .mmhc-inverse-surface h4,
        .main-content .mmhc-inverse-surface h5,
        .main-content .mmhc-inverse-surface h6,
        .main-content .mmhc-inverse-surface p,
        .main-content .mmhc-inverse-surface span,
        .main-content .mmhc-inverse-surface small,
        .main-content .mmhc-inverse-surface label,
        .main-content .mmhc-inverse-surface div {
            color: inherit !important;
        }
        .main-content .mmhc-inverse-surface a:not(.btn) {
            color: inherit !important;
        }
        .main-content .mmhc-inverse-surface .text-muted {
            color: rgba(248, 250, 252, 0.85) !important;
        }
    </style>
    
    <style>
        /* Auth pages styling */
        .auth-page-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }
        
        body:has(.auth-page-wrapper) {
            overflow: hidden;
        }
        
        body:has(.auth-page-wrapper) .container-fluid,
        body:has(.auth-page-wrapper) .row {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body class="@if(auth()->check()) mmhc-crm-auth @endif @if(auth()->check() && request()->is('academics*')) mmhc-academics @endif @if(auth()->check() && trim($__env->yieldContent('page-title', '')) !== '') mmhc-has-page-title @endif">
    @if(auth()->check())
        @include('auth::components.navbar')
        <div class="offcanvas offcanvas-start sidebar d-lg-none" tabindex="-1" id="mmhcAppSidebar" aria-labelledby="mmhcAppSidebarLabel" style="--bs-offcanvas-width: min(20rem, 92vw);">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="" class="brand-logo brand-logo--sidebar" style="max-height: 2.25rem; width: auto;">
                    <span class="text-white-50 small mb-0" id="mmhcAppSidebarLabel">Menu</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 pt-2">
                @include('auth::components.app-sidebar-nav')
            </div>
        </div>
    @endif
    <div class="container-fluid">
        <div class="row">
            @if(auth()->check())
                <!-- Sidebar — desktop column; mobile uses offcanvas above -->
                <nav class="col-md-3 col-lg-2 d-none d-lg-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="text-center mb-4">
                            <div class="brand-logo-card">
                                <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--sidebar">
                            </div>
                            <div class="brand-tagline">{{ $siteTagline ?? 'Miracle Health Care' }}</div>
                        </div>
                        @include('auth::components.app-sidebar-nav')
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-0 px-md-4 main-content">
                    <!-- Page title (all breakpoints; sidebar is offcanvas on small screens) -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom px-3 px-md-0 mmhc-page-title-bar">
                        <h1 class="h2 mb-0 min-w-0 pe-2">@yield('page-title', 'Dashboard')</h1>
                        <div class="btn-toolbar mb-2 mb-md-0 flex-shrink-0 mmhc-page-title-badges">
                            <div class="btn-group me-2">
                                <span class="badge bg-primary">{{ auth()->user()->unique_id }}</span>
                                <span class="badge bg-secondary">{{ ucfirst(auth()->user()->role) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts - Mobile App Style -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show app-alert" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show app-alert" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(!empty($hasPendingContactUpdate))
                        <div class="alert alert-secondary app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap w-100">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-lock me-2"></i>Action required: verify your updated mobile</div>
                                    <div class="small">
                                        You recently requested a mobile number change. Please complete SMS OTP verification first.
                                        Until this is done, other OTP tasks (referral/reward/service) are temporarily paused to avoid mismatch.
                                    </div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-shield-check me-1"></i>Open Contact Verification
                                </a>
                            </div>
                        </div>
                    @endif

                    @if(!empty($heldEarningsDueToUnverifiedMobile))
                        <div class="alert alert-warning app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap w-100">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-mobile-alt me-2"></i>Verify your account mobile to unlock earnings</div>
                                    <div class="small mb-2">
                                        You have already earned the items below (patient reward OTP or referral OTP completed), but
                                        <strong>payouts stay on hold</strong> until your account mobile is verified by SMS OTP in Profile.
                                        Once verified, these amounts unlock for admin payout automatically.
                                    </div>
                                    <ul class="small mb-0 ps-3">
                                        @if(($heldEarningsDueToUnverifiedMobile['patient_reward']['count'] ?? 0) > 0)
                                            <li><strong>Patient rewards:</strong> ₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['patient_reward']['amount'], 2) }} ({{ $heldEarningsDueToUnverifiedMobile['patient_reward']['count'] }} verified)</li>
                                        @endif
                                        @if(($heldEarningsDueToUnverifiedMobile['staff_referral']['count'] ?? 0) > 0)
                                            <li><strong>Staff referrals:</strong> ₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['staff_referral']['amount'], 2) }} ({{ $heldEarningsDueToUnverifiedMobile['staff_referral']['count'] }} verified)</li>
                                        @endif
                                        @if(($heldEarningsDueToUnverifiedMobile['subscription_referral']['count'] ?? 0) > 0)
                                            <li><strong>Subscription referrals:</strong> ₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['subscription_referral']['amount'], 2) }}</li>
                                        @endif
                                        @if(($heldEarningsDueToUnverifiedMobile['service_request']['count'] ?? 0) > 0)
                                            <li><strong>Approved services:</strong> ₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['service_request']['amount'], 2) }}</li>
                                        @endif
                                    </ul>
                                    @if((float) ($heldEarningsDueToUnverifiedMobile['total'] ?? 0) > 0)
                                        <div class="small mt-2 fw-semibold">Total held: ₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['total'], 2) }}</div>
                                    @endif
                                </div>
                                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-warning fw-semibold text-dark">Verify mobile</a>
                            </div>
                        </div>
                    @elseif(!empty($needsPhoneVerification) && ! request()->routeIs('profile.verify-phone'))
                        <div class="alert alert-warning app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap w-100">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-mobile-alt me-2"></i>Mobile verification required</div>
                                    <div class="small mb-0">Verify your account mobile with SMS OTP to use all MMHC app features (dashboard, academics, bookings, community, and more).</div>
                                </div>
                                <a href="{{ route('profile.verify-phone') }}" class="btn btn-sm btn-outline-dark">Verify now</a>
                            </div>
                        </div>
                    @endif

                    @if(!empty($pendingReferralOtpBanner) && empty($hasPendingContactUpdate))
                        <div class="alert alert-warning app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-shield-alt me-2"></i>Referral verification pending</div>
                                    <div class="small">
                                        Your referral onboarding is still pending OTP verification.
                                        Last OTP destination:
                                        <strong>{{ $pendingReferralOtpBanner->verification_otp_sent_to ?: 'not sent yet' }}</strong>.
                                        Your referral reward is unlocked only after successful OTP verification.
                                    </div>
                                    @if(!empty($pendingReferralOtpContacts))
                                        <div class="small mt-1 text-muted">
                                            Registered mobile: <strong>{{ $pendingReferralOtpContacts['mobile'] ?? 'Not available' }}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('staff.referrals.verify-otp') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="otp_code" class="form-control form-control-sm" maxlength="6" placeholder="6-digit OTP" required style="width: 120px;">
                                        <button type="submit" class="btn btn-sm btn-warning fw-semibold">Verify OTP</button>
                                    </form>
                                    <form method="POST" action="{{ route('staff.referrals.resend-otp') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Resend SMS OTP</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($pendingRewardOtpBanner) && empty($hasPendingContactUpdate))
                        <div class="alert alert-info app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-gift me-2"></i>Patient reward verification pending</div>
                                    <div class="small">
                                        Patient reward for <strong>{{ $pendingRewardOtpBanner->patient_name }}</strong> is pending OTP verification.
                                        Credit is added only after OTP verification.
                                        @if(!empty($pendingRewardOtpBanner->verification_otp_sent_to))
                                            Last sent to: <strong>{{ $pendingRewardOtpBanner->verification_otp_sent_to }}</strong>.
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('rewards.verify-otp-banner', $pendingRewardOtpBanner) }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="otp_code" class="form-control form-control-sm" maxlength="6" placeholder="6-digit OTP" required style="width: 120px;">
                                        <button type="submit" class="btn btn-sm btn-info text-white fw-semibold">Verify OTP</button>
                                    </form>
                                    <form method="POST" action="{{ route('rewards.send-otp-banner', $pendingRewardOtpBanner) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info">Resend SMS OTP</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($pendingServiceCompletionBanner) && empty($hasPendingContactUpdate))
                        <div class="alert alert-primary app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-briefcase-medical me-2"></i>Service completion verification pending</div>
                                    <div class="small">
                                        Service #{{ $pendingServiceCompletionBanner->id }} for
                                        <strong>{{ optional($pendingServiceCompletionBanner->patient)->name ?? 'patient' }}</strong>
                                        requires OTP verification before completion is counted.
                                        @if(!empty($pendingServiceCompletionBanner->completion_otp_sent_to))
                                            Last sent to: <strong>{{ $pendingServiceCompletionBanner->completion_otp_sent_to }}</strong>.
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('staff.service.complete-banner', $pendingServiceCompletionBanner) }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="otp_code" class="form-control form-control-sm" maxlength="6" placeholder="6-digit OTP" required style="width: 120px;">
                                        <button type="submit" class="btn btn-sm btn-primary fw-semibold">Verify & Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('staff.service.completion-otp-banner', $pendingServiceCompletionBanner) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Send SMS OTP</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <style>
                        .app-alert {
                            margin: 16px;
                            border-radius: 12px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        }
                        
                        @media (min-width: 768px) {
                            .app-alert {
                                margin: 0 0 16px 0;
                            }
                        }
                    </style>

                    @yield('content')
                </main>

                @include('auth::components.bottom-nav')
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
    @if(auth()->check())
    <script>
        (function () {
            var el = document.getElementById('mmhcAppSidebar');
            if (!el || typeof bootstrap === 'undefined') return;
            el.addEventListener('click', function (e) {
                if (!e.target.closest('a.nav-link, .nav-link[href], button[type="submit"]')) return;
                var oc = bootstrap.Offcanvas.getInstance(el);
                if (oc) oc.hide();
            });
        })();
    </script>
    @endif
    <script src="{{ asset('js/mobile-crm.js') }}" defer></script>
    <script src="{{ asset('js/capacitor-app.js') }}" defer></script>
    @yield('scripts')
</body>
</html>

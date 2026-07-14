<!DOCTYPE html>
@php
    use App\Modules\Academics\Support\AcademicsMobileUi;
    use App\Modules\Services\Support\HealthcareMobileUi;
    $mobileHtmlClasses = auth()->check()
        ? trim(AcademicsMobileUi::htmlClass(auth()->user()).' '.HealthcareMobileUi::htmlClass(auth()->user()))
        : '';
    $mobileBodyClasses = auth()->check()
        ? trim(AcademicsMobileUi::bodyClass(auth()->user()).' '.HealthcareMobileUi::bodyClass(auth()->user()))
        : '';
    $healthcareMobileOn = auth()->check() && HealthcareMobileUi::enabledFor(auth()->user());
    $healthcareStylesOn = auth()->check() && (auth()->user()->isPatient() || auth()->user()->isStaff());
    $academicsMobileOn = auth()->check() && request()->routeIs('academics.*') && AcademicsMobileUi::enabledFor(auth()->user());
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="@guest mmhc-auth-guest @else {{ $mobileHtmlClasses }} @endguest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MeD Miracle Health Care') }} - @yield('title', 'Dashboard')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    @include('partials.pwa-head')

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    @yield('head')

    @if($academicsMobileOn || (auth()->check() && request()->routeIs('academics.*')))
    <link rel="stylesheet" href="{{ asset('css/academics-mobile.css') }}?v=20260608b">
    <meta name="theme-color" content="#4338ca">
    @endif
    @if($healthcareStylesOn)
    <link rel="stylesheet" href="{{ asset('css/healthcare-mobile.css') }}?v=20260608b">
    @endif
    @if($healthcareMobileOn)
    <meta name="theme-color" content="{{ auth()->user()->isPatient() ? '#0f766e' : '#4338ca' }}">
    @endif

    <link rel="stylesheet" href="{{ asset('css/mobile-crm.css') }}?v=20260608b">
    @auth
    <link rel="stylesheet" href="{{ asset('css/crm-desktop.css') }}?v=20260608b">
    <link rel="stylesheet" href="{{ asset('css/mmhc-member-nav.css') }}?v=20260602">
    <link rel="stylesheet" href="{{ asset('css/mmhc-theme-contrast.css') }}?v=20260603">
    @endauth
    <link rel="stylesheet" href="{{ asset('css/mmhc-public-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/capacitor-app.css') }}">
    
    <style>
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .top-navbar .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            width: auto;
            height: auto;
            object-fit: contain;
        }

        /* Top nav — same for admin, student, staff (fixed height like admin screenshot) */
        .top-navbar .mmhc-navbar-brand {
            display: inline-flex;
            align-items: center;
            padding: 0;
            margin-right: 1rem;
            max-width: none;
            min-width: 0;
            overflow: visible;
            flex: 0 0 auto;
        }

        .top-navbar .brand-logo--nav {
            display: block;
            height: 2.5rem;
            width: auto;
            aspect-ratio: 248 / 76;
            max-width: none;
            max-height: none;
            object-fit: contain;
            object-position: left center;
            flex-shrink: 0;
        }

        /* Sidebar — frosted card + tagline (admin layout) */
        .mmhc-sidebar-brand {
            padding: 1.6rem 1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            margin-bottom: 1.5rem;
        }

        .brand-logo-card {
            background: linear-gradient(160deg, rgba(255,255,255,0.20) 0%, rgba(255,255,255,0.06) 100%);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 20px;
            padding: 1rem 0.85rem;
            margin-bottom: 0.65rem;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            line-height: 0;
        }

        .brand-logo--sidebar {
            display: block;
            width: 100%;
            max-width: 168px;
            height: auto;
            aspect-ratio: 248 / 76;
            max-height: none;
            margin: 0 auto;
            object-fit: contain;
        }

        .brand-logo--auth {
            max-width: 200px;
            margin: 0 auto 1.25rem;
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
            padding: 0;
            border-bottom: none;
            margin-bottom: 0;
        }

        /* Text contrast: see public/css/mmhc-theme-contrast.css (loaded when authenticated) */
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
<body class="@guest mmhc-auth-guest @endguest @if(auth()->check()) mmhc-crm-auth mmhc-app-shell {{ $mobileBodyClasses }} @endif @if(auth()->check() && request()->is('academics*')) mmhc-academics @endif @if(auth()->check() && trim($__env->yieldContent('page-title', '')) !== '') mmhc-has-page-title @endif">
    @if(auth()->check())
        @include('auth::components.navbar')
        <div class="offcanvas offcanvas-start sidebar d-lg-none" tabindex="-1" id="mmhcAppSidebar" aria-labelledby="mmhcAppSidebarLabel" style="--bs-offcanvas-width: min(20rem, 92vw);">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25 py-2">
                <span class="text-white-50 small mb-0" id="mmhcAppSidebarLabel">Menu</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 pt-0">
                @include('auth::components.brand-sidebar-block')
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
                        @include('auth::components.brand-sidebar-block')
                        @include('auth::components.app-sidebar-nav')
                    </div>
                </nav>

                <!-- Main content -->
                <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-0 px-md-4 main-content">
                    <!-- Page title (all breakpoints; sidebar is offcanvas on small screens) -->
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center border-bottom px-3 px-md-0 mmhc-page-title-bar">
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
                                    <div class="fw-semibold mb-1"><i class="fas fa-lock me-2"></i>Action required: verify your new mobile number</div>
                                    <div class="small">
                                        You changed your account mobile. Complete OTP on the new number to finish the update.
                                        Until then, patient reward and referral OTPs are paused so numbers stay in sync.
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
                                        <strong>payouts stay on hold</strong> until your account mobile is confirmed.
                                        If you sign in with WhatsApp OTP on this number, verification completes automatically.
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
                                    <div class="small mb-0">Verify your account mobile with WhatsApp OTP to use all MMHC app features (dashboard, academics, bookings, community, and more).</div>
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
                                        <button type="submit" class="btn btn-sm btn-outline-dark">Resend WhatsApp OTP</button>
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
                                        <button type="submit" class="btn btn-sm btn-outline-info">Resend WhatsApp OTP</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($pendingServiceCompletionBanner) && empty($hasPendingContactUpdate))
                        <div class="alert alert-primary app-alert" role="alert">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="fw-semibold mb-1"><i class="fas fa-briefcase-medical me-2"></i>Complete visit — Service #{{ $pendingServiceCompletionBanner->id }}</div>
                                    <div class="small">
                                        Patient: <strong>{{ optional($pendingServiceCompletionBanner->patient)->name ?? 'patient' }}</strong>.
                                        @if(!empty($serviceCompletionSkipsPatientOtp))
                                            Their mobile matches your verified account — your <strong>login OTP already confirmed this number</strong>. No patient OTP needed.
                                        @else
                                            Send OTP to the <strong>patient’s mobile</strong> (not your staff login). They share the code so completion is recorded.
                                            @if(!empty($pendingServiceCompletionBanner->completion_otp_sent_to))
                                                Last sent to: <strong>{{ $pendingServiceCompletionBanner->completion_otp_sent_to }}</strong>.
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    @if(!empty($serviceCompletionSkipsPatientOtp))
                                        <form method="POST" action="{{ route('staff.service.complete-banner', $pendingServiceCompletionBanner) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary fw-semibold">Mark visit complete</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('staff.service.complete-banner', $pendingServiceCompletionBanner) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="otp_code" class="form-control form-control-sm" maxlength="6" placeholder="Patient OTP" required style="width: 120px;">
                                            <button type="submit" class="btn btn-sm btn-primary fw-semibold">Verify & Complete</button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.service.completion-otp-banner', $pendingServiceCompletionBanner) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Send OTP to patient</button>
                                        </form>
                                    @endif
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

                    @if(auth()->check() && \App\Modules\Academics\Support\AcademicsMobileUi::showMobileHeader(auth()->user()))
                        @include('academics::partials.mobile-header', [
                            'academicsMobileBackUrl' => \App\Modules\Academics\Support\AcademicsMobileUi::backUrl(),
                        ])
                    @endif

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
    @if($academicsMobileOn || $healthcareMobileOn)
    <script src="{{ asset('js/mmhc-pull-refresh.js') }}?v=20260604" defer></script>
    @endif
    @if($academicsMobileOn)
    <script src="{{ asset('js/academics-mobile.js') }}?v=20260604" defer></script>
    @endif
    @if($healthcareMobileOn)
    <script src="{{ asset('js/healthcare-mobile.js') }}?v=20260605" defer></script>
    @endif
    <script src="{{ asset('js/capacitor-app.js') }}" defer></script>
    @include('partials.pwa-scripts')
    @yield('scripts')
</body>
</html>

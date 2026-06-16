@extends('auth::layout')

@section('title', 'Register - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <script>
        (function () {
            try {
                var mobile = window.matchMedia('(max-width: 767.98px)').matches;
                var appPref = localStorage.getItem('mmhc_register_view') === 'app';
                if (mobile || appPref) {
                    document.documentElement.classList.add('register-shell--app');
                }
            } catch (e) { /* ignore */ }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/auth-register-app.css') }}?v=20260532">
    <style>
        .register-page #registrationTabs .nav-link.register-tab-academics {
            text-decoration: none;
        }
        .register-role-details {
            margin-top: 0.85rem;
            font-size: 0.82rem;
            color: #64748b;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .register-role-details summary {
            cursor: pointer;
            padding: 0.55rem 0.75rem;
            font-weight: 600;
            color: #475569;
            list-style: none;
        }
        .register-role-details summary::-webkit-details-marker { display: none; }
        .register-role-details summary::after {
            content: ' \25BC';
            font-size: 0.65rem;
            opacity: 0.6;
        }
        .register-role-details[open] summary::after { content: ' \25B2'; }
        .register-role-details__body {
            padding: 0 0.75rem 0.65rem;
            line-height: 1.45;
            margin: 0;
        }
        .register-academics-card {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-top: 1.25rem;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            border: 1px solid rgba(14, 165, 233, 0.35);
            background: linear-gradient(165deg, rgba(14, 165, 233, 0.09) 0%, #ffffff 60%);
            text-decoration: none;
            color: inherit;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .register-academics-card:hover {
            transform: translateY(-2px);
            border-color: rgba(14, 165, 233, 0.55);
            box-shadow: 0 8px 22px rgba(14, 165, 233, 0.15);
            color: inherit;
        }
        .register-academics-card__icon {
            flex-shrink: 0;
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2) 0%, rgba(99, 102, 241, 0.12) 100%);
            color: #0369a1;
            font-size: 1.05rem;
        }
        .register-academics-card__body { flex: 1; min-width: 0; }
        .register-academics-card__eyebrow {
            display: block;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }
        .register-academics-card__title {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }
        .register-academics-card__desc {
            display: block;
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.35;
        }
        .register-academics-card__cta {
            flex-shrink: 0;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0369a1;
            white-space: nowrap;
        }
        .register-page-header__lead {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 0;
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
        }
        @media (max-width: 767.98px) {
            .register-page .card-body {
                padding: 1rem 0.85rem !important;
            }
            .register-page .register-portal-switch .col-12 {
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }
            .register-page #registrationTabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                flex-direction: row !important;
                gap: 0.4rem;
                padding: 0.4rem;
            }
            .register-page #registrationTabs::-webkit-scrollbar {
                display: none;
            }
            .register-page #registrationTabs .nav-item {
                flex: 0 0 auto;
                width: auto;
            }
            .register-page #registrationTabs .nav-link {
                width: auto;
                min-width: 6.5rem;
                white-space: nowrap;
                padding: 0.65rem 0.85rem !important;
                font-size: 0.8rem !important;
                margin: 0;
            }
            .register-page #registrationTabs .nav-link i {
                display: inline-block !important;
                font-size: 0.75rem;
            }
            .register-page .form-panel {
                padding: 0.85rem 0.5rem;
                border-radius: 14px;
                box-shadow: none;
                background: transparent;
            }
            .register-page h2 {
                font-size: 1.25rem !important;
            }
            .register-page .input-group-text {
                min-width: 2.75rem;
                padding: 0.5rem 0.45rem;
            }
            .register-academics-card {
                flex-wrap: wrap;
                padding: 0.85rem;
            }
            .register-academics-card__cta {
                width: 100%;
                padding-left: calc(42px + 0.85rem);
            }
        }
    </style>
@endsection

@section('content')
<div class="register-shell" id="registerShell">
    <div class="register-app-bar" aria-hidden="false">
        <a href="{{ route('auth.login') }}" class="register-app-bar__back" aria-label="Back to sign in"><i class="fas fa-arrow-left" aria-hidden="true"></i></a>
        <p class="register-app-bar__title">Create account</p>
        <div class="register-app-bar__actions">
            <button type="button" id="registerViewToggle" class="register-view-toggle" aria-pressed="false" title="Switch layout">
                <i class="fas fa-mobile-screen-button" aria-hidden="true"></i><span class="d-none d-sm-inline">App</span>
            </button>
        </div>
    </div>
<div class="container register-page">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-lg border-0">
                <div class="card-body p-3 p-md-4 p-lg-5">
                    <div class="register-app-hero">
                        <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--auth">
                        <h2 class="register-app-hero__title">{{ isset($warrior) && $warrior ? 'Nursing Warrior' : 'Create your account' }}</h2>
                        <p class="register-app-hero__lead">WhatsApp number · sign in with OTP later</p>
                    </div>
                    <div class="register-page-sheet">
                    <div class="text-center register-page-header mb-3 position-relative">
                        <button type="button" class="register-view-toggle position-absolute end-0 top-0 d-none d-md-inline-flex register-view-toggle--classic" data-register-view-toggle aria-pressed="false" title="Switch to app layout">
                            <i class="fas fa-mobile-screen-button" aria-hidden="true"></i> App view
                        </button>
                        <div class="d-inline-block rounded-3 px-3 py-2 mb-2" style="background: rgba(102, 126, 234, 0.08);">
                            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--auth" style="max-height: 50px; display: block;">
                        </div>
                        <h2 class="mt-2 mb-1" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700; font-size: 1.5rem;">{{ isset($warrior) && $warrior ? 'Join as Nursing Warrior' : 'Create your account' }}</h2>
                        <p class="register-page-header__lead">{{ isset($warrior) && $warrior ? 'Nurse or Caregiver Warrior registration' : 'Register with your valid WhatsApp number — sign in later with WhatsApp OTP' }}</p>
                    </div>

                    @if(isset($warrior) && $warrior)
                    <div class="text-center mb-3 p-3 rounded-3 d-none d-md-block" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%); border: 1px solid rgba(102, 126, 234, 0.25);">
                        <div class="d-inline-block rounded-4 overflow-hidden p-2" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);">
                            <img src="{{ asset('images/nursing-warrior-badge.png') }}" alt="Nursing Warrior Badge" class="img-fluid mb-0" style="max-height: 140px; width: auto; display: block; vertical-align: middle;">
                        </div>
                        <p class="text-muted mb-0 small mt-2"><strong>Earn this badge</strong> when you register as Nurse Warrior or Caregiver Warrior.</p>
                    </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($referralCode) && $referralCode && $referrer)
                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-gift fa-2x me-3"></i>
                                <div>
                                    <h5 class="mb-1">You've been referred by {{ $referrer->name }}!</h5>
                                    <p class="mb-0">Register as a <strong>Nurse</strong> or <strong>Caregiver</strong> to join the MMHC CRM team. Complete your registration to get started!</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tab Navigation -->
                    <ul class="nav nav-pills mb-3" id="registrationTabs" role="tablist">
                        @if(((!isset($referralCode) || !$referralCode) && empty($warrior)) || !empty($patientOnly))
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="patient-tab" data-bs-toggle="pill" data-bs-target="#patient-form" type="button" role="tab">
                                    <i class="fas fa-user-injured me-2 d-none d-sm-inline"></i>
                                    <span class="d-block d-sm-none">Patient</span>
                                    <span class="d-none d-sm-block">Patient Registration</span>
                                </button>
                            </li>
                        @endif
                        @if(empty($patientOnly))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ (isset($referralCode) && $referralCode) || !empty($warrior) ? 'active' : '' }}" id="nurse-tab" data-bs-toggle="pill" data-bs-target="#nurse-form" type="button" role="tab">
                                <i class="fas fa-user-nurse me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">{{ !empty($warrior) ? 'Nurse Warrior' : 'Nurse' }}</span>
                                <span class="d-none d-sm-block">{{ !empty($warrior) ? 'Nurse Warrior' : 'Nurse Registration' }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="caregiver-tab" data-bs-toggle="pill" data-bs-target="#caregiver-form" type="button" role="tab">
                                <i class="fas fa-user-md me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">{{ !empty($warrior) ? 'Caregiver Warrior' : 'Caregiver' }}</span>
                                <span class="d-none d-sm-block">{{ !empty($warrior) ? 'Caregiver Warrior' : 'Caregiver Registration' }}</span>
                            </button>
                        </li>
                        @if(empty($warrior))
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('auth.register', ['academics' => 1]) }}" class="nav-link register-tab-academics">
                                <i class="fas fa-graduation-cap me-2 d-none d-sm-inline"></i>
                                <span class="d-block d-sm-none">College</span>
                                <span class="d-none d-sm-block">Academics</span>
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="registrationTabContent">
                        @if(empty($warrior))
                        <!-- Patient Registration Form (not rendered in warrior flow) -->
                        <div class="tab-pane fade {{ (!isset($referralCode) || !$referralCode) || !empty($patientOnly) ? 'show active' : '' }}" id="patient-form" role="tabpanel">
                            <div class="form-panel">
                                <form method="POST" action="{{ route('auth.register.post') }}" id="patientForm">
                                    @csrf
                                    <input type="hidden" name="role" value="patient">
                                    
                                    <div class="row g-3 register-form-steps" data-form-steps>
                                        <div class="col-12 register-form-steps__bar" aria-hidden="true">
                                            <span class="register-form-steps__dot is-active"></span>
                                            <span class="register-form-steps__dot"></span>
                                            <span class="register-form-steps__dot"></span>
                                            <span class="register-form-steps__label">Your details</span>
                                        </div>
                                        <div class="register-step is-active col-12 col-lg-6" data-step="1">
                                            <div class="mb-3">
                                                <label for="patient_name" class="form-label">Full Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('name') is-invalid @enderror" 
                                                           id="patient_name" 
                                                           name="name" 
                                                           value="{{ old('name') }}" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="patient_phone" class="form-label">WhatsApp Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+91</span>
                                                    <input type="tel" 
                                                           class="form-control @error('phone') is-invalid @enderror" 
                                                           id="patient_phone" 
                                                           name="phone" 
                                                           value="{{ old('phone') }}" 
                                                           pattern="[0-9]{10}"
                                                           maxlength="10"
                                                           placeholder="9876543210"
                                                           required>
                                                </div>
                                                <div class="form-text small">Must be active on WhatsApp (10-digit Indian mobile)</div>
                                                @error('phone')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="patient_dob" class="form-label">Date of Birth</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-calendar"></i>
                                                    </span>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="patient_dob" 
                                                           name="date_of_birth" 
                                                           value="{{ old('date_of_birth') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="register-step col-12 col-lg-6" data-step="2">
                                            <div class="mb-3">
                                                <label for="patient_address" class="form-label">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                    <textarea class="form-control" 
                                                              id="patient_address" 
                                                              name="address" 
                                                              rows="3" 
                                                              placeholder="Enter your full address">{{ old('address') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="patient_pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-pin"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('pincode') is-invalid @enderror" 
                                                           id="patient_pincode" 
                                                           name="pincode" 
                                                           value="{{ old('pincode') }}" 
                                                           pattern="[1-9][0-9]{5}"
                                                           maxlength="6"
                                                           placeholder="Enter 6-digit pincode"
                                                           required>
                                                </div>
                                                @error('pincode')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="register-step col-12" data-step="3">
                                            <div class="mb-3">
                                                <label for="patient_password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control @error('password') is-invalid @enderror" 
                                                           id="patient_password" 
                                                           name="password" 
                                                           required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="patient_password_confirmation" class="form-label">Confirm Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="patient_password_confirmation" 
                                                           name="password_confirmation" 
                                                           required>
                                                </div>
                                            </div>

                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="patient_terms" required>
                                                <label class="form-check-label" for="patient_terms">
                                                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row register-inline-submit">
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-user-plus me-2"></i>
                                                    Register as Patient
                                                </button>
                                            </div>
                                            <details class="register-role-details">
                                                <summary>What patient accounts include</summary>
                                                <p class="register-role-details__body">Home care, visits, community, and your care plan on MMHC.</p>
                                            </details>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        @if(empty($patientOnly))
                        <!-- Nurse Registration Form -->
                        <div class="tab-pane fade {{ (isset($referralCode) && $referralCode) || !empty($warrior) ? 'show active' : '' }}" id="nurse-form" role="tabpanel">
                            <div class="form-panel">
                                <form method="POST" action="{{ route('auth.register.post') }}{{ isset($referralCode) && $referralCode ? '?ref=' . $referralCode : '' }}" id="nurseForm" enctype="multipart/form-data">
                                    @csrf
                                    @if(isset($referralCode) && $referralCode)
                                        <input type="hidden" name="ref" value="{{ $referralCode }}">
                                    @endif
                                    <input type="hidden" name="role" value="nurse">
                                    
                                    <div class="row g-3">
                                        <!-- Left Column: 5 fields -->
                                        <div class="col-12 col-lg-6">
                                            <div class="mb-3">
                                                <label for="nurse_name" class="form-label">Full Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('name') is-invalid @enderror" 
                                                           id="nurse_name" 
                                                           name="name" 
                                                           value="{{ old('name') }}" 
                                                           required>
                                                </div>
                                            </div>
<div class="mb-3">
                                                <label for="nurse_phone" class="form-label">WhatsApp Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+91</span>
                                                    <input type="tel" 
                                                           class="form-control @error('phone') is-invalid @enderror" 
                                                           id="nurse_phone" 
                                                           name="phone" 
                                                           value="{{ old('phone') }}" 
                                                           pattern="[0-9]{10}"
                                                           maxlength="10"
                                                           placeholder="9876543210"
                                                           required>
                                                </div>
                                                <div class="form-text small">Must be active on WhatsApp (10-digit Indian mobile)</div>
                                                @error('phone')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="nurse_dob" class="form-label">Date of Birth</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-calendar"></i>
                                                    </span>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="nurse_dob" 
                                                           name="date_of_birth" 
                                                           value="{{ old('date_of_birth') }}">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="nurse_qualification" class="form-label">Nursing Qualification</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-graduation-cap"></i>
                                                    </span>
                                                    <select class="form-select" id="nurse_qualification" name="qualification" required>
                                                        <option value="">Select Qualification</option>
                                                        <option value="GNM" {{ old('qualification') == 'GNM' ? 'selected' : '' }}>GNM (General Nursing & Midwifery)</option>
                                                        <option value="B.Sc Nursing" {{ old('qualification') == 'B.Sc Nursing' ? 'selected' : '' }}>B.Sc Nursing</option>
                                                        <option value="M.Sc Nursing" {{ old('qualification') == 'M.Sc Nursing' ? 'selected' : '' }}>M.Sc Nursing</option>
                                                        <option value="ANM" {{ old('qualification') == 'ANM' ? 'selected' : '' }}>ANM (Auxiliary Nurse Midwife)</option>
                                                        <option value="Other" {{ old('qualification') == 'Other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: 5 fields -->
                                        <div class="col-12 col-lg-6">
                                            <div class="mb-3">
                                                <label for="nurse_experience" class="form-label">Years of Experience</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                    <select class="form-select" id="nurse_experience" name="experience" required>
                                                        <option value="">Select Experience</option>
                                                        <option value="0-1" {{ old('experience') == '0-1' ? 'selected' : '' }}>0-1 years</option>
                                                        <option value="1-3" {{ old('experience') == '1-3' ? 'selected' : '' }}>1-3 years</option>
                                                        <option value="3-5" {{ old('experience') == '3-5' ? 'selected' : '' }}>3-5 years</option>
                                                        <option value="5-10" {{ old('experience') == '5-10' ? 'selected' : '' }}>5-10 years</option>
                                                        <option value="10+" {{ old('experience') == '10+' ? 'selected' : '' }}>10+ years</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="nurse_pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-pin"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('pincode') is-invalid @enderror" 
                                                           id="nurse_pincode" 
                                                           name="pincode" 
                                                           value="{{ old('pincode') }}" 
                                                           pattern="[1-9][0-9]{5}"
                                                           maxlength="6"
                                                           placeholder="Enter 6-digit pincode"
                                                           required>
                                                </div>
                                                @error('pincode')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="nurse_password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control @error('password') is-invalid @enderror" 
                                                           id="nurse_password" 
                                                           name="password" 
                                                           required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="nurse_password_confirmation" class="form-label">Confirm Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="nurse_password_confirmation" 
                                                           name="password_confirmation" 
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Full Width: Address -->
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="nurse_address" class="form-label">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                    <textarea class="form-control" 
                                                              id="nurse_address" 
                                                              name="address" 
                                                              rows="3" 
                                                              placeholder="Enter your full address">{{ old('address') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Full Width: Terms and Submit -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="nurse_terms" required>
                                                <label class="form-check-label" for="nurse_terms">
                                                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                                </label>
                                            </div>

                                            <div class="d-grid register-inline-submit">
                                                <button type="submit" class="btn btn-info btn-lg">
                                                    <i class="fas fa-user-nurse me-2"></i>
                                                    Register as Nurse
                                                </button>
                                            </div>
                                            <details class="register-role-details">
                                                <summary>What nurse accounts include</summary>
                                                <p class="register-role-details__body">Visits, documentation, and patient care workflows in the MMHC CRM.</p>
                                            </details>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Caregiver Registration Form -->
                        <div class="tab-pane fade" id="caregiver-form" role="tabpanel">
                            <div class="form-panel">
                                <form method="POST" action="{{ route('auth.register.post') }}{{ isset($referralCode) && $referralCode ? '?ref=' . $referralCode : '' }}" id="caregiverForm" enctype="multipart/form-data">
                                    @csrf
                                    @if(isset($referralCode) && $referralCode)
                                        <input type="hidden" name="ref" value="{{ $referralCode }}">
                                    @endif
                                    <input type="hidden" name="role" value="caregiver">
                                    
                                    <div class="row g-3">
                                        <!-- Left Column: 5 fields -->
                                        <div class="col-12 col-lg-6">
                                            <div class="mb-3">
                                                <label for="caregiver_name" class="form-label">Full Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('name') is-invalid @enderror" 
                                                           id="caregiver_name" 
                                                           name="name" 
                                                           value="{{ old('name') }}" 
                                                           required>
                                                </div>
                                            </div>
<div class="mb-3">
                                                <label for="caregiver_phone" class="form-label">WhatsApp Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+91</span>
                                                    <input type="tel" 
                                                           class="form-control @error('phone') is-invalid @enderror" 
                                                           id="caregiver_phone" 
                                                           name="phone" 
                                                           value="{{ old('phone') }}" 
                                                           pattern="[0-9]{10}"
                                                           maxlength="10"
                                                           placeholder="9876543210"
                                                           required>
                                                </div>
                                                <div class="form-text small">Must be active on WhatsApp (10-digit Indian mobile)</div>
                                                @error('phone')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="caregiver_dob" class="form-label">Date of Birth</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-calendar"></i>
                                                    </span>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="caregiver_dob" 
                                                           name="date_of_birth" 
                                                           value="{{ old('date_of_birth') }}">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="caregiver_qualification" class="form-label">Professional Qualification</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-graduation-cap"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control" 
                                                           id="caregiver_qualification" 
                                                           name="qualification" 
                                                           value="{{ old('qualification') }}" 
                                                           placeholder="e.g., B.Sc Nursing, GNM, ANM">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: 5 fields -->
                                        <div class="col-12 col-lg-6">
                                            <div class="mb-3">
                                                <label for="caregiver_experience" class="form-label">Years of Experience</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-briefcase"></i>
                                                    </span>
                                                    <select class="form-select" id="caregiver_experience" name="experience">
                                                        <option value="">Select Experience</option>
                                                        <option value="0-1" {{ old('experience') == '0-1' ? 'selected' : '' }}>0-1 years</option>
                                                        <option value="1-3" {{ old('experience') == '1-3' ? 'selected' : '' }}>1-3 years</option>
                                                        <option value="3-5" {{ old('experience') == '3-5' ? 'selected' : '' }}>3-5 years</option>
                                                        <option value="5+" {{ old('experience') == '5+' ? 'selected' : '' }}>5+ years</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="caregiver_pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-pin"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('pincode') is-invalid @enderror" 
                                                           id="caregiver_pincode" 
                                                           name="pincode" 
                                                           value="{{ old('pincode') }}" 
                                                           pattern="[1-9][0-9]{5}"
                                                           maxlength="6"
                                                           placeholder="Enter 6-digit pincode"
                                                           required>
                                                </div>
                                                @error('pincode')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="caregiver_password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control @error('password') is-invalid @enderror" 
                                                           id="caregiver_password" 
                                                           name="password" 
                                                           required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="caregiver_password_confirmation" class="form-label">Confirm Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    <input type="password" 
                                                           class="form-control" 
                                                           id="caregiver_password_confirmation" 
                                                           name="password_confirmation" 
                                                           required>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Full Width: Address -->
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="caregiver_address" class="form-label">Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </span>
                                                    <textarea class="form-control" 
                                                              id="caregiver_address" 
                                                              name="address" 
                                                              rows="3" 
                                                              placeholder="Enter your full address">{{ old('address') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Full Width: Terms and Submit -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3 form-check">
                                                <input type="checkbox" class="form-check-input" id="caregiver_terms" required>
                                                <label class="form-check-label" for="caregiver_terms">
                                                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                                                </label>
                                            </div>

                                            <div class="d-grid register-inline-submit">
                                                <button type="submit" class="btn btn-success btn-lg">
                                                    <i class="fas fa-user-plus me-2"></i>
                                                    Register as Caregiver
                                                </button>
                                            </div>
                                            <details class="register-role-details">
                                                <summary>What caregiver accounts include</summary>
                                                <p class="register-role-details__body">Daily patient care tasks and assigned workflows on MMHC.</p>
                                            </details>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if(empty($warrior) && empty($patientOnly))
                    <a href="{{ route('auth.register', ['academics' => 1]) }}" class="register-academics-card d-none d-md-flex">
                        <span class="register-academics-card__icon" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
                        <span class="register-academics-card__body">
                            <span class="register-academics-card__eyebrow">Colleges &amp; programmes</span>
                            <span class="register-academics-card__title">Student or faculty?</span>
                            <span class="register-academics-card__desc">Join your institute, batch, assignments &amp; coursework on MMHC.</span>
                        </span>
                        <span class="register-academics-card__cta">Academics registration <i class="fas fa-arrow-right ms-1"></i></span>
                    </a>
                    @endif

                    <div class="text-center mt-3 register-signin-footer">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="{{ route('auth.login') }}" class="text-primary text-decoration-none">
                                Sign in here
                            </a>
                        </p>
                    </div>
                    </div>{{-- /.register-page-sheet --}}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="register-app-dock" id="registerAppDock">
    <button type="button" class="register-app-dock__back" id="registerDockBack" disabled>Back</button>
    <button type="button" class="register-app-dock__primary btn-primary" id="registerDockPrimary">Continue</button>
</div>
</div>{{-- /#registerShell --}}

<script src="{{ asset('js/auth-register-app.js') }}?v=20260532"></script>

<style>
/* Modern Registration Design */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
    --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
    --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 1rem 0;
}

/* Info Panel Styling - Removed (using info-panel-top instead) */

/* Desktop Benefits Section at Top - Hidden by default, shown when tab is active */
.desktop-benefits-section {
    margin-bottom: 1.5rem;
    display: none !important;
}

.desktop-benefits-section.active,
.tab-pane.active .desktop-benefits-section {
    display: block !important;
}

/* Mobile Benefits Section - Hidden by default, shown when tab is active */
.mobile-benefits-section {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    display: none !important;
}

.mobile-benefits-section.active,
.tab-pane.active .mobile-benefits-section {
    display: block !important;
}

/* Hide mobile benefits section on desktop (992px and above) - they should only show on mobile */
@media (min-width: 992px) {
    .mobile-benefits-section {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
        width: 0 !important;
    }
    
    .mobile-benefits-section.active,
    .tab-pane.active .mobile-benefits-section,
    .mobile-benefits-section.d-lg-none {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
        width: 0 !important;
    }
}

/* Show mobile benefits section only on mobile devices (below 992px) */
@media (max-width: 991px) {
    .mobile-benefits-section.active,
    .tab-pane.active .mobile-benefits-section {
        display: block !important;
        visibility: visible !important;
        height: auto !important;
        margin-top: 1.5rem !important;
        padding: 1.5rem !important;
        overflow: visible !important;
        opacity: 1 !important;
    }
}

.info-panel-top {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

/* Icons removed - cleaner design */

.info-panel-top h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.info-panel-top .lead {
    font-size: 0.95rem;
    color: #718096;
}

.mobile-benefits-section .icon-wrapper {
    width: 90px;
    height: 90px;
    margin-bottom: 1.5rem;
}

.mobile-benefits-section .icon-wrapper i {
    font-size: 3rem;
}

.mobile-benefits-section h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.benefits-card-mobile {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    margin-top: 1.5rem;
}

.benefits-card-mobile h5 {
    color: #2d3748;
    font-weight: 700;
    font-size: 1.1rem;
}

.referral-badge-mobile {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 1.25rem;
    border-radius: 12px;
    margin-top: 1.5rem;
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.referral-badge-mobile i {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    display: block;
}

.referral-badge-mobile strong {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.referral-badge-mobile small {
    opacity: 0.95;
    font-size: 0.85rem;
}

.icon-wrapper {
    display: inline-block;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    margin: 0 auto;
}

.icon-wrapper i {
    color: white;
    font-size: 4rem;
}

.info-panel h3 {
    font-weight: 700;
    color: #2d3748;
    font-size: 1.75rem;
}

.info-panel .lead {
    color: #718096;
    font-size: 1rem;
    font-weight: 400;
}

.benefits-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
    margin-top: 1.5rem;
}

.benefits-card h5 {
    color: #2d3748;
    font-weight: 700;
    font-size: 1.25rem;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    padding: 0.75rem 0;
    color: #4a5568;
    font-size: 0.95rem;
    border-bottom: 1px solid #f7fafc;
}

.benefits-list li:last-child {
    border-bottom: none;
}

.benefits-list li i {
    color: #48bb78;
    font-size: 0.9rem;
}

/* Desktop Pricing Grid */
.pricing-grid-desktop {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.pricing-item-desktop {
    text-align: center;
    padding: 0.875rem 0.75rem;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.pricing-item-desktop:hover {
    border-color: #667eea;
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}

.pricing-item-desktop .price {
    display: block;
    font-size: 1.35rem;
    font-weight: 700;
    color: #667eea;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}

.pricing-item-desktop .duration {
    display: block;
    font-size: 0.75rem;
    color: #718096;
    font-weight: 500;
}

.pricing-grid-desktop {
    gap: 0.75rem;
    margin-bottom: 1rem;
}

/* Mobile Pricing Grid - Horizontal */
.pricing-grid-horizontal {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.pricing-item-horizontal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.pricing-item-horizontal .price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}

.pricing-item-horizontal .duration {
    font-size: 0.875rem;
    color: #718096;
    font-weight: 500;
}

.referral-badge {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 16px;
    margin-top: 1.5rem;
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.referral-badge i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.referral-badge strong {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.referral-badge small {
    opacity: 0.95;
    font-size: 0.875rem;
}

/* Form Panel Styling */
.form-panel {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    height: 100%;
}

.card {
    border: none;
    border-radius: 24px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    background: white;
}

.card-body {
    padding: 1.5rem;
}

.brand-logo--auth {
    max-height: 60px;
    margin-bottom: 1rem;
}

/* Modern Tab Navigation */
.nav-pills {
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
    gap: 0.5rem;
}

.nav-pills .nav-link {
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.84rem;
    font-weight: 600;
    padding: 0.65rem 1rem;
    border: 2px solid transparent;
    color: #6c757d;
    position: relative;
    overflow: hidden;
}

.nav-pills .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.nav-pills .nav-link:hover::before {
    left: 100%;
}

.nav-pills .nav-link.active {
    background: var(--primary-gradient);
    color: white;
    border-color: transparent;
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.nav-pills .nav-link:not(.active) {
    background: white;
    border-color: #e9ecef;
}

.nav-pills .nav-link:not(.active):hover {
    background: white;
    border-color: #667eea;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* Tab Content Styling */
.tab-pane {
    animation: fadeIn 0.4s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Modern Form Styling */
.form-label {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.input-group {
    position: relative;
    margin-bottom: 0.25rem;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.form-panel .mb-3 {
    margin-bottom: 0.875rem !important;
}

.input-group-text {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border: 2px solid #e2e8f0;
    border-right: none;
    color: #667eea;
    min-width: 45px;
    justify-content: center;
    font-size: 0.9rem;
    padding: 0.65rem 0.75rem;
    transition: all 0.3s ease;
}

.form-control,
.form-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.65rem 0.875rem;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 12px 12px 0;
}

.input-group .form-select {
    border-left: none;
    border-radius: 0 12px 12px 0;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    background: #fafbfc;
    outline: none;
}

.form-control:focus + .input-group-text,
.input-group:focus-within .input-group-text {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e8edff 100%);
    color: #667eea;
}

.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

textarea.form-control {
    border-radius: 10px;
    resize: vertical;
    min-height: 70px;
    padding: 0.65rem 0.875rem;
}

.form-text {
    font-size: 0.8rem;
    color: #718096;
    margin-top: 0.25rem;
}

/* Error States */
.is-invalid {
    border-color: #e53e3e !important;
    background: #fff5f5;
}

.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(229, 62, 62, 0.1);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #e53e3e;
    font-weight: 500;
}

/* Modern Buttons */
.btn {
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn-primary {
    background: var(--primary-gradient);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-info {
    background: var(--info-gradient);
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4);
    color: white;
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 172, 254, 0.5);
    color: white;
}

.btn-success {
    background: var(--success-gradient);
    box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(17, 153, 142, 0.5);
}

.btn-lg {
    padding: 0.875rem 2rem;
    font-size: 1rem;
}

.form-panel .btn-lg {
    padding: 0.75rem 1.75rem;
    font-size: 0.95rem;
}

/* Checkbox Styling */
.form-check-input {
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #cbd5e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background: var(--primary-gradient);
    border-color: #667eea;
}

.form-check-input:focus {
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-check-label {
    color: #4a5568;
    cursor: pointer;
    margin-left: 0.5rem;
}

.form-check-label a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.form-check-label a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Password Strength Indicator */
.password-strength {
    font-size: 0.875rem;
    font-weight: 600;
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 8px;
    background: #f7fafc;
}

/* Alert Styling */
.alert {
    border-radius: 12px;
    border: none;
    box-shadow: var(--shadow-sm);
}

.alert-info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    color: #2c5282;
}

.alert-success {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    color: #22543d;
}

/* Benefit Cards */
.alert.alert-info,
.alert.alert-success {
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1rem;
}

/* Icon Styling */
.fa-3x {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 2px 4px rgba(102, 126, 234, 0.3));
}

.text-primary .fa-3x {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.text-info .fa-3x {
    background: var(--info-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.text-success .fa-3x {
    background: var(--success-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Compact spacing for form */
.form-panel .form-check {
    margin-bottom: 0.75rem;
}

.form-panel .d-grid {
    margin-top: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.card {
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Logo styles removed - now using text-based logo */

/* Responsive Design */
@media (max-width: 991px) {
    .info-panel {
        position: relative;
        top: auto;
        max-height: none;
        margin-bottom: 0;
    }
    
    .form-panel {
        max-height: none;
    }
}

@media (max-width: 768px) {
    body {
        padding: 0.5rem 0;
    }
    
    .container {
        padding: 0 0.65rem;
    }
    
    .register-page .card-body {
        padding: 1rem 0.85rem !important;
    }
    
    .register-page #registrationTabs {
        flex-direction: row !important;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        gap: 0.4rem;
        padding: 0.4rem;
    }
    
    .register-page #registrationTabs .nav-item {
        flex: 0 0 auto;
        width: auto;
    }
    
    .register-page #registrationTabs .nav-link {
        width: auto;
        min-width: 6.5rem;
        white-space: nowrap;
        font-size: 0.8rem;
        padding: 0.65rem 0.85rem;
        margin: 0;
    }
    
    .register-page #registrationTabs .nav-link i {
        display: inline-block !important;
    }
    
    .info-panel {
        padding: 1.25rem 1rem;
    }
    
    .form-panel {
        padding: 1rem 0.75rem;
    }
    
    .icon-wrapper {
        width: 80px;
        height: 80px;
        margin-bottom: 1.5rem;
    }
    
    .icon-wrapper i {
        font-size: 2.5rem;
    }
    
    .info-panel h3 {
        font-size: 1.5rem;
    }
    
    .mobile-benefits-section {
        padding: 1.5rem 1.25rem;
    }
    
    .benefits-card-mobile {
        padding: 1.25rem;
    }
    
    .mobile-benefits-section .icon-wrapper {
        width: 80px;
        height: 80px;
    }
    
    .mobile-benefits-section .icon-wrapper i {
        font-size: 2.5rem;
    }
    
    .mobile-benefits-section h3 {
        font-size: 1.35rem;
    }
    
    .pricing-item-horizontal {
        padding: 0.875rem 1rem;
    }
    
    .pricing-item-horizontal .price {
        font-size: 1.35rem;
    }
    
    .pricing-item-horizontal .duration {
        font-size: 0.8rem;
    }
    
    .form-control,
    .form-select {
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .btn-lg {
        padding: 0.875rem 2rem;
        font-size: 1rem;
    }
    
    .form-label {
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .register-page .card-body {
        padding: 0.85rem 0.65rem !important;
    }
    
    .register-page #registrationTabs .nav-link {
        padding: 0.55rem 0.7rem;
        font-size: 0.75rem;
        min-width: 5.75rem;
    }
    
    .input-group-text {
        min-width: 45px;
        font-size: 0.9rem;
        padding: 0.75rem 0.5rem;
    }
    
    .btn {
        width: 100%;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    .col-lg-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    .nav-pills .nav-link {
        font-size: 0.875rem;
        padding: 0.875rem 1.25rem;
    }
}

@media (min-width: 1200px) {
    .container {
        max-width: 1200px;
    }
    
    .card-body {
        padding: 4rem !important;
    }
}

/* Smooth Transitions */
* {
    transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
}

/* Loading State */
.btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Show/hide benefits section based on active tab
function updateBenefitsVisibility() {
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (activeTabPane) {
        // Hide all benefits sections first
        document.querySelectorAll('.desktop-benefits-section, .mobile-benefits-section').forEach(el => {
            el.classList.remove('active');
        });
        
        // Show benefits for active tab
        const desktopBenefits = activeTabPane.querySelector('.desktop-benefits-section');
        const mobileBenefits = activeTabPane.querySelector('.mobile-benefits-section');
        
        if (desktopBenefits) desktopBenefits.classList.add('active');
        if (mobileBenefits) mobileBenefits.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Check for role parameter in URL and activate the corresponding tab
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    
    if (roleParam) {
        let targetTabId = null;
        let targetPaneId = null;
        
        // Map role parameter to tab and pane IDs
        if (roleParam === 'patient') {
            targetTabId = 'patient-tab';
            targetPaneId = 'patient-form';
        } else if (roleParam === 'nurse') {
            targetTabId = 'nurse-tab';
            targetPaneId = 'nurse-form';
        } else if (roleParam === 'caregiver') {
            targetTabId = 'caregiver-tab';
            targetPaneId = 'caregiver-form';
        }
        
        if (targetTabId && targetPaneId) {
            // Remove active class from all tabs and panes
            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Activate the target tab and pane
            const targetTab = document.getElementById(targetTabId);
            const targetPane = document.getElementById(targetPaneId);
            
            if (targetTab && targetPane) {
                targetTab.classList.add('active');
                targetPane.classList.add('show', 'active');
                
                // Use Bootstrap tab API if available
                if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const tabTrigger = new bootstrap.Tab(targetTab);
                    tabTrigger.show();
                }
            }
        }
    }
    
    // Show benefits for initially active tab
    updateBenefitsVisibility();
    
    // Update benefits visibility when tabs change
    const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"], [data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            setTimeout(updateBenefitsVisibility, 100); // Small delay to ensure tab is switched
        });
    });
    
    // Also listen for Bootstrap tab events
    const tabElements = document.querySelectorAll('#patient-tab, #nurse-tab, #caregiver-tab');
    tabElements.forEach(tab => {
        tab.addEventListener('shown.bs.tab', updateBenefitsVisibility);
    });
    
    // Phone number validation for all three forms
    const patientPhone = document.getElementById('patient_phone');
    const nursePhone = document.getElementById('nurse_phone');
    const caregiverPhone = document.getElementById('caregiver_phone');

    // Function to validate phone number
    function validatePhone(phoneInput) {
        const phone = phoneInput.value.replace(/\D/g, ''); // Remove non-digits
        const isValid = phone.length === 10;
        
        if (phoneInput.value && !isValid) {
            phoneInput.setCustomValidity('Phone number must be exactly 10 digits');
            phoneInput.classList.add('is-invalid');
        } else {
            phoneInput.setCustomValidity('');
            phoneInput.classList.remove('is-invalid');
        }
        
        return isValid;
    }

    // Function to format phone input (numbers only)
    function formatPhoneInput(phoneInput) {
        // Remove any non-digit characters
        let value = phoneInput.value.replace(/\D/g, '');
        
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        
        phoneInput.value = value;
        validatePhone(phoneInput);
    }

    // Add event listeners for phone validation for all forms
    [patientPhone, nursePhone, caregiverPhone].forEach(phoneInput => {
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                formatPhoneInput(this);
            });
            
            phoneInput.addEventListener('blur', function() {
                validatePhone(this);
            });
        }
    });

    // Pincode validation for all forms
    const patientPincode = document.getElementById('patient_pincode');
    const nursePincode = document.getElementById('nurse_pincode');
    const caregiverPincode = document.getElementById('caregiver_pincode');

    function formatPincodeInput(pincodeInput) {
        let value = pincodeInput.value.replace(/\D/g, '');
        if (value.length > 6) {
            value = value.substring(0, 6);
        }
        pincodeInput.value = value;
    }

    [patientPincode, nursePincode, caregiverPincode].forEach(pincodeInput => {
        if (pincodeInput) {
            pincodeInput.addEventListener('input', function() {
                formatPincodeInput(this);
            });
        }
    });

    // Date of birth validation - prevent future dates
    const dateInputs = document.querySelectorAll('input[type="date"][name="date_of_birth"]');
    const today = new Date().toISOString().split('T')[0];
    dateInputs.forEach(dateInput => {
        if (dateInput) {
            dateInput.setAttribute('max', today);
        }
    });

    // Form submission validation
    const forms = ['patientForm', 'nurseForm', 'caregiverForm'];
    const phoneFields = {
        'patientForm': patientPhone,
        'nurseForm': nursePhone,
        'caregiverForm': caregiverPhone
    };

    forms.forEach(formId => {
        const form = document.getElementById(formId);
        const phoneField = phoneFields[formId];
        
        if (form && phoneField) {
            form.addEventListener('submit', function(e) {
                if (!validatePhone(phoneField)) {
                    e.preventDefault();
                    phoneField.focus();
                    return false;
                }
            });
        }
    });

    // Password strength indicator
    const passwordInputs = document.querySelectorAll('input[type="password"][name="password"]');
    passwordInputs.forEach(passwordInput => {
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = getPasswordStrength(password);
                
                // Remove existing strength indicator
                const existingIndicator = this.parentElement.parentElement.querySelector('.password-strength');
                if (existingIndicator) {
                    existingIndicator.remove();
                }
                
                // Add strength indicator
                if (password.length > 0) {
                    const indicator = document.createElement('div');
                    indicator.className = 'password-strength mt-1';
                    indicator.innerHTML = `<small class="text-${strength.color}"><i class="fas ${strength.icon} me-1"></i>${strength.text}</small>`;
                    this.parentElement.parentElement.appendChild(indicator);
                }
            });
        }
    });

    function getPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        if (strength <= 2) {
            return { text: 'Weak password', color: 'danger', icon: 'fa-exclamation-circle' };
        } else if (strength <= 3) {
            return { text: 'Medium password', color: 'warning', icon: 'fa-exclamation-triangle' };
        } else {
            return { text: 'Strong password', color: 'success', icon: 'fa-check-circle' };
        }
    }

    // Smooth scroll to first error on form submission
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find first invalid field
                const firstInvalid = this.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
            this.classList.add('was-validated');
        });
    });
});
</script>
@endsection

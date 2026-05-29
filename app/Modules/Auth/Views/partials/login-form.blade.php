{{-- Shared login form (phone OTP first, then email). --}}
@php
    $phoneTabActive = session('login_tab') !== 'email';
    $emailTabActive = session('login_tab') === 'email';
@endphp
@if($errors->any())
    <div class="alert-modern">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(session('success_otp') && session('otp_sent'))
    <div class="alert alert-success py-2 small mb-2">
        <i class="fas fa-check-circle me-1"></i>{{ session('success_otp') }}
    </div>
@endif

<ul class="nav nav-pills nav-fill mb-2 login-tabs" id="loginTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $phoneTabActive ? 'active' : '' }}" id="tab-phone" data-bs-toggle="pill" data-bs-target="#pane-phone" type="button" role="tab">Phone (SMS OTP)</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $emailTabActive ? 'active' : '' }}" id="tab-email" data-bs-toggle="pill" data-bs-target="#pane-email" type="button" role="tab">Email</button>
    </li>
</ul>

<div class="tab-content" id="loginTabContent">
    <div class="tab-pane fade {{ $phoneTabActive ? 'show active' : '' }}" id="pane-phone" role="tabpanel">
        <p class="small text-muted mb-3">
            <i class="fas fa-mobile-alt me-1"></i>
            <strong>Default sign-in</strong> — use the mobile number you registered with. New accounts always use SMS OTP here.
        </p>
        @if(session('otp_sent'))
            <p class="small fw-semibold text-success mb-2"><i class="fas fa-sms me-1"></i>Step 2 — enter the OTP sent to your mobile</p>
            <form method="POST" action="{{ route('auth.verify-login-otp') }}" id="verifyOtpForm">
                @csrf
                <input type="hidden" name="phone" value="{{ old('phone', session('otp_phone')) }}">
                <div class="form-floating-modern">
                    <label for="otp">6-digit OTP (sent by SMS)</label>
                    <div style="position: relative;">
                        <i class="fas fa-key input-icon"></i>
                        <input type="text"
                               inputmode="numeric"
                               pattern="[0-9]{6}"
                               maxlength="6"
                               class="form-control @error('otp') is-invalid @enderror"
                               id="otp"
                               name="otp"
                               value="{{ old('otp') }}"
                               placeholder="000000"
                               required
                               autocomplete="one-time-code"
                               autofocus>
                    </div>
                    @error('otp')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <p class="small text-muted mb-2">Number: +91 {{ session('otp_phone') }}</p>
                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-check me-2"></i>Verify & Sign In
                </button>
                <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Use different number</a>
            </form>
        @else
            <p class="small text-muted mb-2"><i class="fas fa-list-ol me-1"></i>Step 1 — enter your registered mobile, then we send an SMS code</p>
            <form method="POST" action="{{ route('auth.send-login-otp') }}" id="phoneOtpForm">
                @csrf
                <div class="form-floating-modern">
                    <label for="login_phone">Mobile Number</label>
                    <div style="position: relative;">
                        <span class="input-icon" style="left: 14px;">+91</span>
                        <input type="tel"
                               class="form-control @error('phone') is-invalid @enderror"
                               id="login_phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               placeholder="9876543210"
                               maxlength="10"
                               pattern="[6-9][0-9]{9}"
                               required
                               @if($phoneTabActive) autofocus @endif>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">OTP will be sent to this number by SMS</small>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-sms me-2"></i>Send OTP by SMS
                </button>
            </form>
        @endif

        @unless(session('otp_sent'))
        <div class="login-signup-section" aria-labelledby="login-signup-heading-phone">
            <div class="login-signup-divider" role="presentation"></div>
            <div class="login-signup-head">
                <span class="login-signup-kicker">New to MMHC?</span>
                <h2 id="login-signup-heading-phone" class="login-signup-title">Create your account</h2>
                <p class="login-signup-lead">Register below, then always sign in here with SMS OTP on your mobile.</p>
            </div>
            <div class="login-signup-grid">
                <a href="{{ route('auth.register') }}" class="login-signup-card login-signup-card--healthcare">
                    <span class="login-signup-card-glow" aria-hidden="true"></span>
                    <span class="login-signup-card-icon"><i class="fas fa-heart-pulse" aria-hidden="true"></i></span>
                    <span class="login-signup-card-eyebrow">Healthcare &amp; home care</span>
                    <span class="login-signup-card-name">Medical team &amp; patients</span>
                    <p class="login-signup-card-desc">Patients, nurses &amp; caregivers—home care &amp; community on MMHC.</p>
                    <ul class="login-signup-card-list">
                        <li>Patient, nurse, or caregiver</li>
                        <li>Visits, care tasks &amp; care plans</li>
                    </ul>
                    <span class="login-signup-card-cta">
                        <span>Sign up for healthcare</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </span>
                </a>
                <a href="{{ route('auth.register', ['academics' => 1]) }}" class="login-signup-card login-signup-card--academics">
                    <span class="login-signup-card-glow" aria-hidden="true"></span>
                    <span class="login-signup-card-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
                    <span class="login-signup-card-eyebrow">Colleges &amp; programmes</span>
                    <span class="login-signup-card-name">Academics</span>
                    <p class="login-signup-card-desc">Students &amp; faculty—join your college’s batches &amp; coursework.</p>
                    <ul class="login-signup-card-list">
                        <li>Institute + batch on MMHC</li>
                        <li>Assignments, quizzes &amp; reports</li>
                    </ul>
                    <span class="login-signup-card-cta">
                        <span>Sign up for academics</span>
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </span>
                </a>
            </div>
        </div>
        @endunless
    </div>

    <div class="tab-pane fade {{ $emailTabActive ? 'show active' : '' }}" id="pane-email" role="tabpanel">
        <p class="small text-muted mb-3">
            <i class="fas fa-info-circle me-1"></i>
            <strong>Existing members only</strong> — email and password for accounts created before mobile-only sign-in.
        </p>
        <form method="POST" action="{{ route('auth.login.post') }}" id="loginForm">
            @csrf
            <div class="form-floating-modern">
                <label for="email">Email Address</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email address"
                           required
                           @if($emailTabActive) autofocus @endif>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-floating-modern">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-me-container">
                <div class="form-check-modern">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="{{ route('auth.forgot-password') }}" class="forgot-password-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>
    </div>
</div>



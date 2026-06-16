{{-- Shared login form (phone WhatsApp OTP first, then email). --}}
@php
    $phoneTabActive = session('login_tab') !== 'email';
    $emailTabActive = session('login_tab') === 'email';
    $otpSent = session('otp_sent');
    $topErrors = $otpSent
        ? collect($errors->getMessages())->except('phone')->flatten()->all()
        : $errors->all();
@endphp
@if(count($topErrors) > 0)
    <div class="alert-modern">
        <ul>
            @foreach($topErrors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(session('success_otp') && $otpSent)
    <div class="wa-otp-banner wa-otp-banner--success mb-2">
        <span class="wa-otp-banner__icon"><i class="fas fa-check"></i></span>
        <span>{{ session('success_otp') }}</span>
    </div>
@endif

<ul class="nav nav-pills nav-fill mb-3 login-tabs" id="loginTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link tab-wa {{ $phoneTabActive ? 'active' : '' }}" id="tab-phone" data-bs-toggle="pill" data-bs-target="#pane-phone" type="button" role="tab">
            <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $emailTabActive ? 'active' : '' }}" id="tab-email" data-bs-toggle="pill" data-bs-target="#pane-email" type="button" role="tab">
            <i class="fas fa-envelope me-1"></i> Email
        </button>
    </li>
</ul>

<div class="tab-content" id="loginTabContent">
    <div class="tab-pane fade {{ $phoneTabActive ? 'show active' : '' }}" id="pane-phone" role="tabpanel">
        <div class="wa-otp-flow">
            <div class="wa-otp-steps" aria-label="Sign-in progress">
                <div class="wa-otp-step {{ $otpSent ? 'is-done' : 'is-active' }}">
                    <span class="wa-otp-step-num">1</span>
                    <span>WhatsApp number</span>
                </div>
                <div class="wa-otp-step-line" aria-hidden="true"></div>
                <div class="wa-otp-step {{ $otpSent ? 'is-active' : '' }}">
                    <span class="wa-otp-step-num">2</span>
                    <span>Enter code</span>
                </div>
            </div>

            @if($otpSent)
                @error('phone')
                    <div class="wa-otp-banner wa-otp-banner--warn mb-2" role="alert">
                        <span class="wa-otp-banner__icon"><i class="fas fa-exclamation"></i></span>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div class="wa-otp-number-card">
                    <div class="wa-otp-number-card__wa" aria-hidden="true"><i class="fab fa-whatsapp"></i></div>
                    <div>
                        <p class="wa-otp-number-card__label">Code sent on WhatsApp to</p>
                        <p class="wa-otp-number-card__value">+91 {{ session('otp_phone') }}</p>
                        <p class="wa-otp-number-card__hint">Open WhatsApp on this phone. Code expires in a few minutes.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('auth.verify-login-otp') }}" id="verifyOtpForm">
                    @csrf
                    <input type="hidden" name="phone" value="{{ old('phone', session('otp_phone')) }}">
                    <div class="form-floating-modern mb-2">
                        <label for="otp">6-digit verification code</label>
                        <div style="position: relative;">
                            <i class="fas fa-shield-halved input-icon"></i>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]{6}"
                                   maxlength="6"
                                   class="form-control wa-otp-input @error('otp') is-invalid @enderror"
                                   id="otp"
                                   name="otp"
                                   value="{{ old('otp') }}"
                                   placeholder="······"
                                   required
                                   autocomplete="one-time-code"
                                   autofocus>
                        </div>
                        @error('otp')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-check me-2"></i>Verify &amp; Sign In
                    </button>
                </form>

                <div class="wa-otp-resend-row">
                    <form method="POST" action="{{ route('auth.send-login-otp') }}" id="resendLoginOtpForm" class="d-inline">
                        @csrf
                        <input type="hidden" name="phone" value="{{ session('otp_phone') }}">
                        <input type="hidden" name="resend" value="1">
                        <button type="submit" class="wa-otp-resend-btn">
                            <i class="fab fa-whatsapp me-1"></i>Resend code on WhatsApp
                        </button>
                    </form>
                    <div class="wa-otp-resend-timer" id="waOtpResendTimer" aria-live="polite"></div>
                </div>
                <a href="{{ route('auth.login') }}" class="wa-otp-alt-link">Use a different WhatsApp number</a>
            @else
                <div class="wa-otp-banner wa-otp-banner--info">
                    <span class="wa-otp-banner__icon"><i class="fab fa-whatsapp"></i></span>
                    <div>
                        <strong>Sign in with your WhatsApp number</strong><br>
                        Enter the <strong>valid 10-digit mobile</strong> you registered with. We send a one-time code on <strong>WhatsApp only</strong> (not SMS).
                    </div>
                </div>

                <form method="POST" action="{{ route('auth.send-login-otp') }}" id="phoneOtpForm">
                    @csrf
                    <div class="form-floating-modern">
                        <label for="login_phone">Your WhatsApp number</label>
                        <div style="position: relative;">
                            <span class="wa-phone-prefix">+91</span>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="login_phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="98765 43210"
                                   maxlength="10"
                                   inputmode="numeric"
                                   pattern="[6-9][0-9]{9}"
                                   required
                                   autocomplete="tel-national"
                                   @if($phoneTabActive) autofocus @endif>
                            <i class="fab fa-whatsapp wa-phone-icon" aria-hidden="true"></i>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-wa w-100 mt-1">
                        <i class="fab fa-whatsapp me-2"></i>Send code on WhatsApp
                    </button>
                </form>
            @endif
        </div>

        @unless($otpSent)
        <div class="login-signup-section" aria-labelledby="login-signup-heading-phone">
            <div class="login-signup-divider" role="presentation"></div>
            <div class="login-signup-head">
                <span class="login-signup-kicker">New to MMHC?</span>
                <h2 id="login-signup-heading-phone" class="login-signup-title">Create your account</h2>
                <p class="login-signup-lead">Register with your WhatsApp number, then sign in here with a WhatsApp code.</p>
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
            <strong>Existing members only</strong> — email and password for accounts created before WhatsApp sign-in.
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

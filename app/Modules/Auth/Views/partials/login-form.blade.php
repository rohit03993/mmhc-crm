{{-- Shared login form (email + phone OTP tabs). --}}
@if($errors->any())
    <div class="alert-modern">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(session('success_otp'))
    <div class="alert alert-success py-2 small">{{ session('success_otp') }}</div>
@endif

<ul class="nav nav-pills nav-fill mb-3 login-tabs" id="loginTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('login_tab') !== 'phone') ? 'active' : '' }}" id="tab-email" data-bs-toggle="pill" data-bs-target="#pane-email" type="button" role="tab">Email</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ (session('login_tab') === 'phone') ? 'active' : '' }}" id="tab-phone" data-bs-toggle="pill" data-bs-target="#pane-phone" type="button" role="tab">Phone (WhatsApp OTP)</button>
    </li>
</ul>

<div class="tab-content" id="loginTabContent">
    <div class="tab-pane fade {{ (session('login_tab') !== 'phone') ? 'show active' : '' }}" id="pane-email" role="tabpanel">
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
                           autofocus>
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
                <a href="#" class="forgot-password-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In
            </button>
        </form>
    </div>

    <div class="tab-pane fade {{ (session('login_tab') === 'phone') ? 'show active' : '' }}" id="pane-phone" role="tabpanel">
        @if(session('otp_sent'))
            <form method="POST" action="{{ route('auth.verify-login-otp') }}" id="verifyOtpForm">
                @csrf
                <input type="hidden" name="phone" value="{{ old('phone', session('otp_phone')) }}">
                <div class="form-floating-modern">
                    <label for="otp">6-digit OTP (sent on WhatsApp)</label>
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
                               autocomplete="one-time-code">
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
                               required>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">OTP will be sent to this number on WhatsApp</small>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    <i class="fab fa-whatsapp me-2"></i>Send OTP on WhatsApp
                </button>
            </form>
        @endif
    </div>
</div>

<div class="signup-link">
    <p>
        Don't have an account?
        <a href="{{ route('auth.register') }}">Sign up here</a>
    </p>
</div>

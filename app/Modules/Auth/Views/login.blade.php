@extends('auth::layout')

@section('title', 'Login - MMHC CRM')

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
</style>
@endsection

@section('content')
<div class="auth-page-wrapper">
    <div class="login-card">
        <div class="login-card-header">
            <img src="{{ asset('images/med-logo.png') }}" alt="MED Miracle Health Care" class="brand-logo">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        <div class="login-card-body">
            @if($errors->any())
                <div class="alert-modern">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

            <div class="signup-link">
                <p>
                    Don't have an account? 
                    <a href="{{ route('auth.register') }}">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
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

// Add smooth form submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
});
</script>
@endsection

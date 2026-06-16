@extends('auth::layout')

@section('title', 'Forgot Password - MMHC CRM')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="MMHC" style="max-height: 48px;" class="mb-3">
                        <h1 class="h4 fw-bold text-dark mb-1">Forgot password?</h1>
                        <p class="text-muted small mb-0">Password reset is only for accounts that sign in with <strong>email</strong>.</p>
                    </div>

                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-mobile-alt me-1"></i>
                        Registered with mobile only? Use <a href="{{ route('auth.login') }}#pane-phone" class="alert-link"><i class="fab fa-whatsapp me-1"></i>WhatsApp sign-in</a> on the login page — no email needed.
                    </div>

                    @if(session('status'))
                        <div class="alert alert-success py-2 small">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(! $mailReady)
                        <div class="alert alert-warning py-2 small mb-3">
                            Email reset is not configured on this server. Please contact MMHC support
                            @if($supportEmail)
                                at <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                            @endif
                            or sign in with WhatsApp OTP on your mobile.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('auth.forgot-password.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Account email</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="you@example.com"
                                   required
                                   @disabled(! $mailReady)
                                   autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-2" @disabled(! $mailReady)>
                            <i class="fas fa-paper-plane me-1"></i> Send reset link
                        </button>
                        <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to sign in
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

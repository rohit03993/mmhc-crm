@extends('auth::layout')

@section('title', 'Verify Mobile - MMHC CRM')
@section('page-title', 'Verify your mobile')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                        </div>
                        <h2 class="h5 mb-2">Verify your mobile number</h2>
                        <p class="text-muted small mb-0">
                            For your security, every MMHC account must confirm its mobile number with a one-time WhatsApp code before using the app.
                        </p>
                    </div>

                    @if(!empty($sendError))
                        <div class="alert alert-danger">{{ $sendError }}</div>
                    @endif

                    <div class="alert alert-light border mb-4">
                        <div class="small">
                            <strong>Registered mobile:</strong> {{ $user->displayPhone() }}<br>
                            @if(!empty($otpSentTo))
                                <strong>OTP sent to:</strong> {{ $otpSentTo }}
                            @else
                                <span class="text-muted">Tap below to receive your verification code.</span>
                            @endif
                        </div>
                    </div>

                    @if($user->hasPendingMobileContactVerification())
                        <form method="POST" action="{{ route('profile.verify-contact-otp') }}" class="mb-3">
                            @csrf
                            <label for="otp_code" class="form-label fw-semibold">Enter 6-digit OTP</label>
                            <input type="text" name="otp_code" id="otp_code" class="form-control form-control-lg text-center mb-3" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="000000" required autofocus>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Verify &amp; continue
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('profile.verify-phone.send') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i>{{ $user->hasPendingMobileContactVerification() ? 'Resend OTP' : 'Send OTP to my mobile' }}
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('profile.edit') }}" class="small">Wrong number? Update mobile in Profile</a>
                        <span class="text-muted mx-2">·</span>
                        <form method="POST" action="{{ route('auth.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

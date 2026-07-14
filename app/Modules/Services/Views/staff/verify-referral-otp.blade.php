@extends('auth::layout')

@section('title', 'Verify referral OTP')
@section('page-title', 'Verify referral')

@section('content')
<div class="container-fluid px-3 py-3 mmhc-otp-page">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="mmhc-otp-page__card">
                <div class="mmhc-otp-page__icon warning">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                </div>
                <h1 class="mmhc-otp-page__title">Verify referral OTP</h1>
                <p class="mmhc-otp-page__subtitle">
                    Confirm the WhatsApp code to finish referral onboarding and unlock your reward.
                </p>

                <div class="mmhc-otp-page__meta">
                    <div>Last OTP destination: <strong>{{ $referral->verification_otp_sent_to ?: 'not sent yet' }}</strong></div>
                    @if(!empty($contacts['mobile']))
                        <div class="mt-1">Registered mobile: <strong>{{ $contacts['mobile'] }}</strong></div>
                    @endif
                </div>

                <form method="POST" action="{{ route('staff.referrals.verify-otp') }}" class="mmhc-otp-page__form">
                    @csrf
                    <label for="otp_code" class="form-label fw-semibold">Enter 6-digit OTP</label>
                    <input type="text"
                           name="otp_code"
                           id="otp_code"
                           class="form-control form-control-lg text-center mmhc-otp-page__input"
                           maxlength="6"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           placeholder="••••••"
                           required>
                    <button type="submit" class="btn btn-warning btn-lg w-100 fw-semibold mt-3">
                        Verify OTP
                    </button>
                </form>

                <form method="POST" action="{{ route('staff.referrals.resend-otp') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        Resend WhatsApp OTP
                    </button>
                </form>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Verify reward OTP')
@section('page-title', 'Verify reward')

@section('content')
<div class="container-fluid px-3 py-3 mmhc-otp-page">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="mmhc-otp-page__card">
                <div class="mmhc-otp-page__icon info">
                    <i class="fas fa-gift" aria-hidden="true"></i>
                </div>
                <h1 class="mmhc-otp-page__title">Verify patient reward</h1>
                <p class="mmhc-otp-page__subtitle">
                    Patient: <strong>{{ $reward->patient_name }}</strong>. Credit is added only after OTP verification.
                </p>

                <div class="mmhc-otp-page__meta">
                    @if($reward->verification_otp_sent_to)
                        Last sent to: <strong>{{ $reward->verification_otp_sent_to }}</strong>
                    @else
                        Tap resend to send a WhatsApp OTP to the patient.
                    @endif
                </div>

                <form method="POST" action="{{ route('rewards.verify-otp-banner', $reward) }}" class="mmhc-otp-page__form">
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
                    <button type="submit" class="btn btn-info btn-lg w-100 text-white fw-semibold mt-3">
                        Verify OTP
                    </button>
                </form>

                <form method="POST" action="{{ route('rewards.send-otp-banner', $reward) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-info w-100">
                        Resend WhatsApp OTP
                    </button>
                </form>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('staff.rewards.index') }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Rewards
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

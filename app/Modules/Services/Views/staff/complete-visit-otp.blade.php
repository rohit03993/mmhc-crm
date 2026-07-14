@extends('auth::layout')

@section('title', 'Complete visit OTP')
@section('page-title', 'Complete visit')

@section('content')
<div class="container-fluid px-3 py-3 mmhc-otp-page">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="mmhc-otp-page__card">
                <div class="mmhc-otp-page__icon primary">
                    <i class="fas fa-briefcase-medical" aria-hidden="true"></i>
                </div>
                <h1 class="mmhc-otp-page__title">Complete visit #{{ $serviceRequest->id }}</h1>
                <p class="mmhc-otp-page__subtitle">
                    Patient: <strong>{{ optional($serviceRequest->patient)->name ?? 'patient' }}</strong>
                    · {{ $serviceRequest->serviceType->name ?? 'Service' }}
                </p>

                @if($skipsPatientOtp)
                    <div class="alert alert-success small">
                        Patient mobile matches your verified account. Your login OTP already confirmed this number — no patient OTP needed.
                    </div>
                    <form method="POST" action="{{ route('staff.service.complete-banner', $serviceRequest) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Mark visit complete
                        </button>
                    </form>
                @else
                    <div class="mmhc-otp-page__meta">
                        Send OTP to the <strong>patient’s mobile</strong> (not your staff login). They share the code so completion is recorded.
                        @if($serviceRequest->completion_otp_sent_to)
                            <div class="mt-1">Last sent to: <strong>{{ $serviceRequest->completion_otp_sent_to }}</strong></div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('staff.service.completion-otp-banner', $serviceRequest) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            Send OTP to patient
                        </button>
                    </form>

                    <form method="POST" action="{{ route('staff.service.complete-banner', $serviceRequest) }}" class="mmhc-otp-page__form">
                        @csrf
                        <label for="otp_code" class="form-label fw-semibold">Enter patient OTP</label>
                        <input type="text"
                               name="otp_code"
                               id="otp_code"
                               class="form-control form-control-lg text-center mmhc-otp-page__input"
                               maxlength="6"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               placeholder="••••••"
                               required>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold mt-3">
                            Verify &amp; complete
                        </button>
                    </form>
                @endif
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('staff.service-details', $serviceRequest) }}" class="btn btn-link text-muted text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to job details
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

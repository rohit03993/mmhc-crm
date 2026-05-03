@extends('auth::layout')

@section('title', 'Congratulations - Nursing Warrior')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        .warrior-welcome-card {
            background: linear-gradient(160deg, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.95) 100%);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.2);
            overflow: hidden;
        }
        .warrior-badge-wrap {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
            border-radius: 20px;
            padding: 1.5rem;
            display: inline-block;
        }
        .warrior-welcome-card .btn-continue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .warrior-welcome-card .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
    </style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="warrior-welcome-card p-5 text-center">
                <div class="warrior-badge-wrap mb-4">
                    <img src="{{ asset('images/nursing-warrior-badge.png') }}" alt="Nursing Warrior Badge" class="img-fluid" style="max-height: 180px; width: auto;">
                </div>
                <h1 class="h2 mb-3 fw-bold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    Congratulations, you are a Nursing Warrior!
                </h1>
                <p class="text-muted mb-4 fs-5">
                    You've earned this badge by joining MeD Miracle Health Care as {{ auth()->user()->role === 'nurse' ? 'a Nurse Warrior' : 'a Caregiver Warrior' }}. Thank you for being part of our mission to deliver compassionate care.
                </p>
                @if(!empty($pendingReferralOtp))
                    <div class="alert alert-warning text-start mb-4">
                        <div class="fw-semibold mb-2"><i class="fas fa-shield-alt me-1"></i>Referral OTP verification pending</div>
                        <div class="small mb-2">Enter OTP sent to your mobile ({{ $pendingReferralOtp->verification_otp_sent_to ?: 'registered number' }}) to finalize referral verification and unlock referral earnings.</div>
                        <form method="POST" action="{{ route('staff.referrals.verify-otp') }}" class="d-flex gap-2 flex-wrap">
                            @csrf
                            <input type="text" name="otp_code" class="form-control" maxlength="6" placeholder="Enter 6-digit OTP" required style="max-width: 220px;">
                            <button type="submit" class="btn btn-sm btn-warning">Verify OTP</button>
                        </form>
                        <form method="POST" action="{{ route('staff.referrals.resend-otp') }}" class="mt-2">
                            @csrf
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <select name="otp_channel" class="form-select form-select-sm" style="max-width: 180px;" required>
                                    <option value="mobile">Resend on Mobile</option>
                                    <option value="email">Resend on Email</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Resend OTP</button>
                            </div>
                        </form>
                    </div>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-continue btn-primary text-white">
                    <i class="fas fa-arrow-right me-2"></i>Continue to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

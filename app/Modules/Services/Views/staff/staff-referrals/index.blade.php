@extends('auth::layout')

@section('title', 'Staff Referrals - Staff Dashboard')
@section('page-title', 'Staff Referrals')

@section('head')
@include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Staff Referrals</h5>
    </div>
</div>

@include('services::partials.staff-referrals-assets')

<div class="container-fluid px-3 py-4">
    <div class="hc-stat-chips d-md-none mb-3">
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">{{ $referralStats['completed_referrals'] }}</span>
            <span class="hc-stat-chip__lbl">Referrals</span>
        </div>
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">₹{{ number_format($staffReferralPayableAmount, 0) }}</span>
            <span class="hc-stat-chip__lbl">Payable</span>
        </div>
    </div>

    @include('services::partials.staff-earnings-nav', ['activeTab' => 'staff-referrals'])

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="{{ route('staff.incentives.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i>View All Incentive Details
            </a>
        </div>
    </div>

    @include('services::staff.partials.verification-steps-explainer')

    <!-- Stats Banner (₹100 base per referral + incentive logic) -->
    <div class="row g-3 mb-4">
        @if(!$staffMobileVerified && ($staffReferralHeldAmount > 0 || $staffReferralEarnedAmount > 0))
        <div class="col-12">
            <div class="alert alert-warning mb-0 py-2">
                <i class="fas fa-mobile-alt me-1"></i>
                @if($staffReferralHeldAmount > 0)
                    ₹{{ number_format($staffReferralHeldAmount, 2) }} is earned but not payable until <strong>your Profile mobile</strong> is WhatsApp-verified (separate from the referred person’s WhatsApp OTP).
                @else
                    Verify your account mobile in Profile to unlock referral payouts.
                @endif
            </div>
        </div>
        @endif
        <div class="col-12 col-md-6">
            <div class="stats-card-modern bg-info">
                <div class="stats-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $referralStats['completed_referrals'] }}</div>
                    <div class="stats-label">Referrals (referred staff WhatsApp OTP done)</div>
                    @if(($referralStats['pending_referrals'] ?? 0) > 0)
                        <div class="small text-muted mt-1">{{ $referralStats['pending_referrals'] }} waiting on referred staff mobile OTP</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="stats-card-modern {{ (!$staffMobileVerified && $staffReferralEarnedAmount > 0) ? 'bg-warning' : 'bg-success' }}">
                <div class="stats-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">₹{{ number_format($staffReferralPayableAmount, 2) }}</div>
                    <div class="stats-label">Payable Incentive (base: ₹{{ number_format($basePerRef, 0) }} per referral)</div>
                    @if(!$staffMobileVerified && $staffReferralEarnedAmount > 0)
                        <div class="small text-warning mt-1">₹{{ number_format($staffReferralEarnedAmount, 2) }} earned · payout on hold</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="referral-link-card-modern">
                <div class="referral-link-header">
                    <i class="fas fa-share-alt me-2"></i>
                    <h5 class="mb-0">Your Staff Referral Link</h5>
                </div>
                <div class="referral-link-body">
                    <p class="mb-3">Share this link with nurses and caregivers. Referral stays <strong>pending</strong> until the referred staff completes WhatsApp OTP. After that, base <strong>₹{{ number_format($basePerRef, 0) }}</strong> is earned — payout unlocks once <strong>your account mobile</strong> is verified in Profile.</p>
                    @include('services::partials.referral-link-share', [
                        'inputId' => 'referralLink',
                        'linkUrl' => $referralLink,
                        'theme' => 'staff',
                        'whatsappText' => 'Join MMHC as nurse/caregiver with my referral link: ',
                    ])
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Referral WhatsApp OTP must be completed by the referred staff. Your account mobile must also be verified in Profile before incentive is payable.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrals List -->
    <div class="row">
        <div class="col-12">
            <div class="section-header mb-3">
                <h5 class="section-title">
                    <i class="fas fa-history me-2"></i>Referral History
                </h5>
            </div>

            @if($referrals->count() > 0)
                <div class="referrals-list-cards">
                    @foreach($referrals as $referral)
                        @php
                            $referralBlockers = \App\Modules\Payments\Services\StaffEarningStatusResolver::referralBlockers($referral, $staffMobileVerified);
                            $statusMessages = \App\Modules\Payments\Services\StaffEarningStatusResolver::detailMessagesForBlockers($referralBlockers);
                            $showIncentive = \App\Modules\Payments\Services\StaffEarningStatusResolver::referralIncentiveCountsForStaff($referral, $staffMobileVerified)
                                || $referral->payment_processed;
                        @endphp
                        <div class="referral-entry-card-modern">
                            <div class="referral-entry-header-modern">
                                <div class="referral-entry-info">
                                    <div class="referral-entry-name">
                                        @include('services::partials.account-party-label', [
                                            'name' => $referral->displayReferredName(),
                                            'inactive' => $referral->isReferredInactive(),
                                            'icon' => $referral->isReferredInactive() || $referral->referred_name_snapshot ? 'fa-user' : ($referral->referred_id ? 'fa-user' : 'fa-clock'),
                                        ])
                                    </div>
                                    <div class="referral-entry-meta">
                                        <span class="badge bg-secondary me-2">Code: {{ $referral->referral_code }}</span>
                                        @include('services::staff.partials.payout-status-blockers', ['blockers' => $referralBlockers, 'compact' => true])
                                    </div>
                                </div>
                                @if($showIncentive)
                                <div class="referral-entry-badge-modern">
                                    <span class="badge-points">Base ₹{{ number_format($basePerRef, 0) }}</span>
                                </div>
                                @else
                                <div class="referral-entry-badge-modern">
                                    <span class="badge-points badge-points--held">₹0 · not credited yet</span>
                                </div>
                                @endif
                            </div>
                            @foreach($statusMessages as $statusMessage)
                                <div class="referral-entry-details-modern mb-2">
                                    <div class="detail-row">
                                        <i class="fas fa-info-circle me-2 text-warning"></i>
                                        <span>{{ $statusMessage }}</span>
                                    </div>
                                </div>
                            @endforeach
                            @if(\App\Modules\Payments\Services\StaffEarningStatusResolver::referralOtpVerified($referral) && ($referral->verified_at || $referral->completed_at))
                            <div class="referral-entry-details-modern">
                                <div class="detail-row">
                                    <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                    <span>Referred staff WhatsApp OTP verified: {{ ($referral->verified_at ?? $referral->completed_at)->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $referrals->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-state-icon empty-state-icon--staff-ref">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h5>No Referrals Yet</h5>
                    <p class="text-muted">Share your referral link to start earning rewards!</p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection


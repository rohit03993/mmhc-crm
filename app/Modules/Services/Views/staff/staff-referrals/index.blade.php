@extends('auth::layout')

@section('title', 'Staff Referrals - Staff Dashboard')

@section('content')
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Staff Referrals</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
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
                    ₹{{ number_format($staffReferralHeldAmount, 2) }} is earned but not payable until <strong>your Profile mobile</strong> is SMS-verified (separate from the referred person’s mobile OTP).
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
                    <div class="stats-label">Referrals (referred staff SMS OTP done)</div>
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
                    <p class="mb-3">Share this link with nurses and caregivers. Referral stays <strong>pending</strong> until the referred staff completes SMS OTP. After that, base <strong>₹{{ number_format($basePerRef, 0) }}</strong> is earned — payout unlocks once <strong>your account mobile</strong> is verified in Profile.</p>
                    <div class="input-group-modern">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="referralLink" 
                               value="{{ $referralLink }}" 
                               readonly>
                        <button class="btn btn-primary btn-lg" 
                                type="button" 
                                onclick="copyReferralLink()">
                            <i class="fas fa-copy me-2"></i>Copy Link
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Referral SMS OTP must be completed by the referred staff. Your account mobile must also be verified in Profile before incentive is payable.
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
                                        @if($referral->referred)
                                            <i class="fas fa-user me-2 text-primary"></i>{{ $referral->referred->name }}
                                        @else
                                            <i class="fas fa-clock me-2 text-warning"></i>Pending Registration
                                        @endif
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
                                    <span>Referred staff SMS OTP verified: {{ ($referral->verified_at ?? $referral->completed_at)->format('M d, Y') }}</span>
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
                    <div class="empty-state-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h5>No Referrals Yet</h5>
                    <p class="text-muted">Share your referral link to start earning rewards!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('auth::components.bottom-nav')

<style>
.stats-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-top: 4px solid;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.bg-info .stats-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.badge-points--held {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
    color: #212529 !important;
}

.bg-warning .stats-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
.bg-success .stats-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }

.stats-content {
    flex: 1;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.stats-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.referral-link-card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    border-top: 4px solid #4facfe;
}

.referral-link-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 600;
}

.referral-link-body {
    padding: 1.5rem;
}

.input-group-modern {
    display: flex;
    gap: 0.5rem;
}

.input-group-modern .form-control {
    flex: 1;
    font-family: monospace;
}

.referral-entry-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid #4facfe;
}

.referral-entry-card-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.referral-entry-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.referral-entry-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}

.referral-entry-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.referral-entry-badge-modern {
    text-align: right;
}

.badge-points {
    display: block;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.referral-entry-details-modern {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
}

.empty-state-modern {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1.5rem;
}

@media (max-width: 768px) {
    .stats-card-modern {
        padding: 1.25rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .stats-value {
        font-size: 1.5rem;
    }
    
    .input-group-modern {
        flex-direction: column;
    }
}
</style>

<script>
function copyReferralLink() {
    const referralLinkInput = document.getElementById('referralLink');
    referralLinkInput.select();
    referralLinkInput.setSelectionRange(0, 99999);
    
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(referralLinkInput.value).then(() => {
                showCopySuccess();
            });
        } else {
            document.execCommand('copy');
            showCopySuccess();
        }
    } catch (err) {
        alert('Failed to copy referral link. Please copy manually.');
    }
}

function showCopySuccess() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>
@endsection


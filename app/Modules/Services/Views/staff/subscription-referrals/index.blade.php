@extends('auth::layout')

@section('title', 'Subscription Referrals - Staff Dashboard')
@section('page-title', 'Subscription Referrals')

@section('content')
<div class="mobile-app-container">
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Subscription Referrals</h5>
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

    <!-- Stats Banner -->
    <div class="row g-3 mb-4">
        @if(!$staffMobileVerified && ($subscriptionHeldAmount > 0 || ($stats->earned_commission ?? 0) > 0))
        <div class="col-12">
            <div class="alert alert-warning mb-0 py-2">
                <i class="fas fa-mobile-alt me-1"></i>
                @if($subscriptionHeldAmount > 0)
                    ₹{{ number_format($subscriptionHeldAmount, 2) }} in subscription commission is on hold until you verify your Profile mobile.
                @else
                    Verify your Profile mobile to unlock subscription referral payouts.
                @endif
            </div>
        </div>
        @endif
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-success">
                <div class="stats-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $stats->total_referrals ?? 0 }}</div>
                    <div class="stats-label">Total Referrals</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-info">
                <div class="stats-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $stats->active_referrals ?? 0 }}</div>
                    <div class="stats-label">Active Subscriptions</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-warning">
                <div class="stats-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">₹{{ number_format($stats->total_commission ?? 0, 2) }}</div>
                    <div class="stats-label">Payable Commission</div>
                    @if(!$staffMobileVerified && ($stats->earned_commission ?? 0) > 0)
                        <div class="small text-warning mt-1">₹{{ number_format((float) $stats->earned_commission, 2) }} earned · on hold</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="referral-link-card-modern">
                <div class="referral-link-header" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-share-alt me-2"></i>
                    <h5 class="mb-0">Your Subscription Referral Link</h5>
                </div>
                <div class="referral-link-body">
                    <p class="mb-3">Share this link with patients. When they subscribe using your link, your incentive is calculated from the active incentive rule set.</p>
                    <div class="input-group-modern">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="subscriptionReferralLink" 
                               value="{{ $subscriptionReferralLink }}" 
                               readonly>
                        <button class="btn btn-success btn-lg" 
                                type="button" 
                                onclick="copySubscriptionReferralLink()">
                            <i class="fas fa-copy me-2"></i>Copy Link
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Commission is calculated on base amount (before GST). You can share this link multiple times.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions List -->
    <div class="row">
        <div class="col-12">
            <div class="section-header mb-3">
                <h5 class="section-title">
                    <i class="fas fa-history me-2"></i>Subscription Referral History
                </h5>
            </div>

            @if($subscriptions->count() > 0)
                <div class="subscriptions-list-cards">
                    @foreach($subscriptions as $subscription)
                        <div class="subscription-entry-card-modern">
                            <div class="subscription-entry-header-modern">
                                <div class="subscription-entry-info">
                                    <div class="subscription-entry-name">
                                        <i class="fas fa-user me-2 text-primary"></i>{{ $subscription->user->name }}
                                    </div>
                                    <div class="subscription-entry-meta">
                                        <span class="badge bg-primary me-2">{{ $subscription->plan->name }}</span>
                                        <span class="badge bg-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="subscription-entry-badge-modern">
                                    <span class="badge-commission">₹{{ number_format($subscription->referral_commission_amount, 2) }}</span>
                                    <small class="text-muted d-block mt-1">Calculated from active incentive rules</small>
                                </div>
                            </div>
                            <div class="subscription-entry-details-modern">
                                <div class="detail-row">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    <span>Subscribed: {{ $subscription->created_at->format('M d, Y') }} • {{ $subscription->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-rupee-sign me-2 text-success"></i>
                                    <span>Base Amount: ₹{{ number_format($subscription->base_amount ?? 0, 2) }}</span>
                                </div>
                                @if($subscription->status === 'active')
                                <div class="detail-row">
                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                    <span>Active until: {{ $subscription->end_date->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $subscriptions->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-state-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h5>No Subscription Referrals Yet</h5>
                    <p class="text-muted">Share your subscription referral link with patients to start earning commission!</p>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
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

.bg-success .stats-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.bg-info .stats-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.bg-warning .stats-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }

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
    border-top: 4px solid #43e97b;
}

.referral-link-header {
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

.subscription-entry-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid #43e97b;
}

.subscription-entry-card-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.subscription-entry-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.subscription-entry-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}

.subscription-entry-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.subscription-entry-badge-modern {
    text-align: right;
}

.badge-commission {
    display: block;
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

.subscription-entry-details-modern {
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
function copySubscriptionReferralLink() {
    const referralLinkInput = document.getElementById('subscriptionReferralLink');
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
    btn.classList.add('btn-primary');
    btn.classList.remove('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
    }, 2000);
}
</script>
@endsection


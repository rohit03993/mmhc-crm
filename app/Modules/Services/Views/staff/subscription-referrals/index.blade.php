@extends('auth::layout')

@section('title', 'Subscription Referrals - Staff Dashboard')
@section('page-title', 'Subscription Referrals')

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
        <h5 class="text-white mb-0">Subscription Referrals</h5>
    </div>
</div>

@include('services::partials.staff-referrals-assets')

<div class="container-fluid px-3 py-4">
    <div class="hc-stat-chips hc-stat-chips--3 d-md-none mb-3">
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">{{ $stats->total_referrals ?? 0 }}</span>
            <span class="hc-stat-chip__lbl">Total</span>
        </div>
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">{{ $stats->active_referrals ?? 0 }}</span>
            <span class="hc-stat-chip__lbl">Active</span>
        </div>
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">₹{{ number_format($stats->total_commission ?? 0, 0) }}</span>
            <span class="hc-stat-chip__lbl">Commission</span>
        </div>
    </div>

    @include('services::partials.staff-earnings-nav', ['activeTab' => 'subscription-referrals'])

    @if(empty($staffMobileVerified))
    <div class="alert alert-warning mb-3 py-2 small">
        <i class="fas fa-mobile-alt me-1"></i>
        Verify your account mobile in <a href="{{ route('profile.edit') }}" class="alert-link fw-semibold">Profile</a> to unlock subscription referral payouts.
    </div>
    @endif

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
            <div class="referral-link-card-modern referral-link-card-modern--subscription">
                <div class="referral-link-header referral-link-header--subscription">
                    <i class="fas fa-share-alt me-2"></i>
                    <h5 class="mb-0">Your Subscription Referral Link</h5>
                </div>
                <div class="referral-link-body">
                    <p class="mb-3">Share this link with patients. When they subscribe using your link, your incentive is calculated from the active incentive rule set.</p>
                    @include('services::partials.referral-link-share', [
                        'inputId' => 'subscriptionReferralLink',
                        'linkUrl' => $subscriptionReferralLink,
                        'theme' => 'subscription',
                        'whatsappText' => 'Subscribe to MMHC healthcare plans with my link: ',
                    ])
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
                                        @include('services::partials.account-party-label', [
                                            'name' => $subscription->displaySubscriberName(),
                                            'inactive' => $subscription->isSubscriberInactive(),
                                            'icon' => 'fa-user',
                                        ])
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
                    <div class="empty-state-icon empty-state-icon--sub-ref">
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
@endsection


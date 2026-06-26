@extends('auth::layout')

@section('title', 'Refer Friends — MMHC')
@section('page-title', 'Refer Friends')

@section('content')
<div class="mobile-app-container mmhc-referrals-page hc-mobile-shell" data-mmhc-ptr>
    <div class="app-mobile-header d-md-none">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-link text-white p-0 me-3" aria-label="Back to dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h5 class="text-white mb-0">Refer Friends</h5>
        </div>
    </div>

    @include('services::partials.staff-referrals-assets')

    <div class="container-fluid px-3 py-4">
        <div class="d-none d-md-flex align-items-center mb-3">
            <a href="{{ route('dashboard') }}" class="btn btn-link text-decoration-none ps-0">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
        </div>

        <div class="mmhc-patient-referral-hero mb-4">
            <h1 class="h5 mb-2">Share healthcare plans</h1>
            <p class="text-muted small mb-0">
                Send your link to family or friends. When they subscribe to an MMHC plan using it, they are linked to your account and you can track them here.
            </p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="stats-card-modern bg-info">
                    <div class="stats-icon"><i class="fas fa-users"></i></div>
                    <div class="stats-content">
                        <div class="stats-value">{{ $stats['total_referrals'] ?? 0 }}</div>
                        <div class="stats-label">Friends referred</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stats-card-modern bg-success">
                    <div class="stats-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stats-content">
                        <div class="stats-value">{{ $stats['active_referrals'] ?? 0 }}</div>
                        <div class="stats-label">Active plans</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="referral-link-card-modern referral-link-card-modern--subscription mb-4">
            <div class="referral-link-header referral-link-header--subscription">
                <i class="fas fa-share-alt me-2"></i>
                <span>Your referral link</span>
            </div>
            <div class="referral-link-body">
                <p class="small text-muted mb-3">Works in the mobile app and in the browser — tap Copy or WhatsApp to share.</p>
                @include('services::partials.referral-link-share', [
                    'inputId' => 'patientPlanReferralLink',
                    'linkUrl' => $planReferralLink,
                    'theme' => 'subscription',
                    'whatsappText' => 'Join MMHC healthcare plans with my referral link: ',
                ])
            </div>
        </div>

        <div class="section-header mb-3">
            <h5 class="section-title mb-0">
                <i class="fas fa-history me-2"></i>Referral history
            </h5>
        </div>

        @if($referrals->count() > 0)
            <div class="subscriptions-list-cards">
                @foreach($referrals as $subscription)
                    <div class="subscription-entry-card-modern">
                        <div class="subscription-entry-header-modern">
                            <div>
                                <div class="subscription-entry-name">{{ $subscription->user->name ?? 'Member' }}</div>
                                <div class="subscription-entry-meta">
                                    <span class="badge bg-light text-dark">{{ $subscription->plan->name ?? 'Plan' }}</span>
                                    <span class="text-muted small">{{ $subscription->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="subscription-entry-badge-modern">
                                <span class="badge {{ $subscription->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">{{ $referrals->links() }}</div>
        @else
            <div class="empty-state-modern">
                <div class="empty-state-icon empty-state-icon--sub-ref">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h5 class="mb-2">No referrals yet</h5>
                <p class="text-muted small mb-3">Share your link on WhatsApp — when someone subscribes, they will appear here.</p>
            </div>
        @endif
    </div>
</div>
@endsection

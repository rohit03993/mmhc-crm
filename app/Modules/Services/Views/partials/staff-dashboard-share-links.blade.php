{{-- Staff dashboard: quick copy/WhatsApp for both referral types (mobile + desktop) --}}
<section class="mmhc-dashboard-share mb-4" aria-labelledby="mmhc-dashboard-share-title">
    <div class="mmhc-dashboard-share__head">
        <h5 id="mmhc-dashboard-share-title" class="mb-1">
            <i class="fas fa-share-alt me-2 text-primary"></i>Share &amp; earn
        </h5>
        <p class="text-muted small mb-0">Copy or send on WhatsApp — same links as the full referral pages.</p>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="mmhc-share-card mmhc-share-card--staff">
                <div class="mmhc-share-card__title">
                    <i class="fas fa-user-friends me-2"></i>Refer nurses &amp; caregivers
                </div>
                @include('services::partials.referral-link-share', [
                    'inputId' => 'referralLink',
                    'linkUrl' => $referralLink ?? '',
                    'theme' => 'staff',
                    'whatsappText' => 'Join MMHC as nurse/caregiver with my referral link: ',
                ])
                <a href="{{ route('staff.staff-referrals.index') }}" class="btn btn-sm btn-link px-0 mt-2">View staff referrals</a>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mmhc-share-card mmhc-share-card--plans">
                <div class="mmhc-share-card__title">
                    <i class="fas fa-heartbeat me-2"></i>Refer patients to plans
                </div>
                @include('services::partials.referral-link-share', [
                    'inputId' => 'subscriptionReferralLink',
                    'linkUrl' => $subscriptionReferralLink ?? '',
                    'theme' => 'subscription',
                    'whatsappText' => 'Subscribe to MMHC healthcare plans with my link: ',
                ])
                <a href="{{ route('staff.subscription-referrals.index') }}" class="btn btn-sm btn-link px-0 mt-2">View plan referrals</a>
            </div>
        </div>
    </div>
</section>

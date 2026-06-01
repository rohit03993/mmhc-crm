@if(!empty($planReferralLink))
<section class="mmhc-patient-referral-teaser mb-4" aria-labelledby="patient-refer-teaser-title">
    <div class="mmhc-patient-referral-teaser__inner">
        <div class="mmhc-patient-referral-teaser__text">
            <h6 id="patient-refer-teaser-title" class="mb-1">
                <i class="fas fa-user-plus me-1"></i>Refer friends to MMHC plans
            </h6>
            <p class="small text-muted mb-0">
                @if(($referralStats['total_referrals'] ?? 0) > 0)
                    {{ $referralStats['total_referrals'] }} referred · {{ $referralStats['active_referrals'] ?? 0 }} active
                @else
                    Share your link — track when friends subscribe.
                @endif
            </p>
        </div>
        <a href="{{ route('patient.referrals.index') }}" class="btn btn-success btn-sm mmhc-patient-referral-teaser__btn">
            Refer <i class="fas fa-chevron-right ms-1"></i>
        </a>
    </div>
</section>
@endif

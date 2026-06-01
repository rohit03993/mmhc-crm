<nav class="mmhc-quick-actions" aria-label="Quick actions">
    <a href="{{ route('staff.index') }}" class="mmhc-quick-actions__item mmhc-quick-actions__item--primary">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-crosshairs"></i></span>
        <span class="mmhc-quick-actions__label">Find staff</span>
    </a>
    <a href="{{ route('services.my-requests') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-clipboard-list"></i></span>
        <span class="mmhc-quick-actions__label">My requests</span>
    </a>
    <a href="{{ route('patient.referrals.index') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-user-plus"></i></span>
        <span class="mmhc-quick-actions__label">Refer</span>
    </a>
    <a href="{{ route('plans.index') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-heartbeat"></i></span>
        <span class="mmhc-quick-actions__label">Plans</span>
    </a>
    <a href="{{ route('profile.edit') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-user-edit"></i></span>
        <span class="mmhc-quick-actions__label">Profile</span>
    </a>
</nav>

<nav class="mmhc-quick-actions mmhc-quick-actions--staff" aria-label="Staff quick actions">
    <a href="#assignments" class="mmhc-quick-actions__item mmhc-quick-actions__item--primary">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-briefcase-medical"></i></span>
        <span class="mmhc-quick-actions__label">My visits</span>
        @if(($pending_booking_count ?? 0) > 0)
        <span class="mmhc-quick-actions__badge">{{ $pending_booking_count }}</span>
        @endif
    </a>
    <a href="{{ route('academics.open-classrooms.index') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-graduation-cap"></i></span>
        <span class="mmhc-quick-actions__label">Learn</span>
    </a>
    <a href="{{ route('staff.rewards.index') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-gift"></i></span>
        <span class="mmhc-quick-actions__label">Rewards</span>
    </a>
    <a href="{{ route('staff.staff-referrals.index') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-user-plus"></i></span>
        <span class="mmhc-quick-actions__label">Refer staff</span>
    </a>
    <a href="{{ route('profile.edit') }}" class="mmhc-quick-actions__item">
        <span class="mmhc-quick-actions__icon"><i class="fas fa-user-edit"></i></span>
        <span class="mmhc-quick-actions__label">Profile</span>
    </a>
</nav>

@php
    $active = $activeTab ?? '';
@endphp
<nav class="mmhc-staff-earnings-nav mmhc-staff-earnings-nav--sticky" aria-label="Earnings sections">
    <a href="{{ route('staff.dashboard') }}"
       class="mmhc-staff-earnings-nav__item mmhc-staff-earnings-nav__item--dash {{ $active === 'dashboard' ? 'is-active' : '' }}">
        <i class="fas fa-home me-1"></i>Dashboard
    </a>
    <a href="{{ route('staff.rewards.index') }}"
       class="mmhc-staff-earnings-nav__item {{ $active === 'rewards' ? 'is-active' : '' }}">
        <i class="fas fa-gift me-1"></i>Patient rewards
    </a>
    <a href="{{ route('staff.staff-referrals.index') }}"
       class="mmhc-staff-earnings-nav__item {{ $active === 'staff-referrals' ? 'is-active' : '' }}">
        <i class="fas fa-user-friends me-1"></i>Staff refs
    </a>
    <a href="{{ route('staff.subscription-referrals.index') }}"
       class="mmhc-staff-earnings-nav__item {{ $active === 'subscription-referrals' ? 'is-active' : '' }}">
        <i class="fas fa-heartbeat me-1"></i>Plan refs
    </a>
</nav>

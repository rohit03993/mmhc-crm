@php
    use App\Modules\Payments\Services\StaffEarningStatusResolver;
    $compact = !empty($compact);
    $status = $status ?? '';
    // Backward compat for older blades still passing held_mobile
    if ($status === 'held_mobile') {
        $status = StaffEarningStatusResolver::HELD_ACCOUNT_MOBILE;
    }
    $label = StaffEarningStatusResolver::badgeLabel($status, $compact);
@endphp
@if($status === StaffEarningStatusResolver::PAID)
    <span class="badge rounded-pill bg-success">{{ $label }}</span>
@elseif($status === StaffEarningStatusResolver::HELD_ACCOUNT_MOBILE)
    <span class="badge rounded-pill bg-warning text-dark" title="Verify your own account mobile in Profile (not the patient or referred staff number)">{{ $label }}</span>
@elseif($status === StaffEarningStatusResolver::PENDING_PATIENT_OTP)
    <span class="badge rounded-pill bg-secondary" title="WhatsApp OTP on the patient mobile entered on the form">{{ $label }}</span>
@elseif($status === StaffEarningStatusResolver::PENDING_REFERRAL_OTP)
    <span class="badge rounded-pill bg-secondary" title="WhatsApp OTP on the referred nurse/caregiver mobile">{{ $label }}</span>
@elseif($status === StaffEarningStatusResolver::PAYABLE)
    <span class="badge rounded-pill bg-primary">{{ $label }}</span>
@else
    <span class="badge rounded-pill bg-light text-dark border">{{ $label }}</span>
@endif

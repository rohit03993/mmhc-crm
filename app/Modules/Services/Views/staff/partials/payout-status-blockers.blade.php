@php
    use App\Modules\Payments\Services\StaffEarningStatusResolver;
    $blockers = $blockers ?? [];
    $compact = !empty($compact);
@endphp
<div class="d-flex flex-column align-items-{{ $align ?? 'end' }} gap-1">
    @foreach($blockers as $blocker)
        @include('services::staff.partials.payout-status-badge', ['status' => $blocker, 'compact' => $compact])
    @endforeach
</div>

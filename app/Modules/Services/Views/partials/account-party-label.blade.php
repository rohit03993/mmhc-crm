@php
    $name = $name ?? 'Unknown';
    $inactive = (bool) ($inactive ?? false);
    $icon = $icon ?? 'fa-user';
@endphp
<span class="account-party-label {{ $inactive ? 'account-party-label--inactive' : '' }}">
    <i class="fas {{ $icon }} me-2 {{ $inactive ? 'text-secondary' : 'text-primary' }}"></i>
    <span class="{{ $inactive ? 'text-muted text-decoration-line-through' : '' }}">{{ $name }}</span>
    @if($inactive)
        <span class="badge bg-secondary ms-2">Inactive</span>
    @endif
</span>

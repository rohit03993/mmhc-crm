@php
    $current = $currentStep ?? 'staff';
    // Linear path: Find staff → Book → Confirm (My requests). Bottom nav covers Services / Requests.
    $steps = [
        'staff' => ['label' => 'Find', 'route' => route('staff.index'), 'icon' => 'fa-search'],
        'book' => ['label' => 'Book', 'route' => null, 'icon' => 'fa-calendar-check'],
        'requests' => ['label' => 'Confirm', 'route' => route('services.my-requests'), 'icon' => 'fa-check'],
    ];
    // Map legacy 'services' to Find so old includes still highlight correctly.
    if ($current === 'services') {
        $current = 'staff';
    }
@endphp
<nav class="mmhc-booking-flow mb-3" aria-label="Booking progress">
    <ol class="mmhc-booking-flow__list">
        @foreach($steps as $key => $step)
            @php
                $keys = array_keys($steps);
                $isActive = $current === $key;
                $isPast = array_search($key, $keys, true) < array_search($current, $keys, true);
            @endphp
            <li class="mmhc-booking-flow__item {{ $isActive ? 'is-active' : '' }} {{ $isPast ? 'is-done' : '' }}">
                @if($step['route'] && ! $isActive && $isPast)
                    <a href="{{ $step['route'] }}" class="mmhc-booking-flow__link">
                        <span class="mmhc-booking-flow__icon"><i class="fas {{ $step['icon'] }}"></i></span>
                        <span class="mmhc-booking-flow__label">{{ $step['label'] }}</span>
                    </a>
                @else
                    <span class="mmhc-booking-flow__link mmhc-booking-flow__link--static">
                        <span class="mmhc-booking-flow__icon"><i class="fas {{ $step['icon'] }}"></i></span>
                        <span class="mmhc-booking-flow__label">{{ $step['label'] }}</span>
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@php
    $current = $currentStep ?? 'services';
    $steps = [
        'services' => ['label' => 'Services', 'route' => route('services.index'), 'icon' => 'fa-list'],
        'staff' => ['label' => 'Find staff', 'route' => route('staff.index'), 'icon' => 'fa-users'],
        'book' => ['label' => 'Book', 'route' => null, 'icon' => 'fa-calendar-check'],
        'requests' => ['label' => 'My requests', 'route' => route('services.my-requests'), 'icon' => 'fa-clipboard-list'],
    ];
@endphp
<nav class="mmhc-booking-flow mb-3" aria-label="Booking steps">
    <ol class="mmhc-booking-flow__list">
        @foreach($steps as $key => $step)
            @php
                $isActive = $current === $key;
                $isPast = array_search($key, array_keys($steps), true) < array_search($current, array_keys($steps), true);
            @endphp
            <li class="mmhc-booking-flow__item {{ $isActive ? 'is-active' : '' }} {{ $isPast ? 'is-done' : '' }}">
                @if($step['route'] && ! $isActive)
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

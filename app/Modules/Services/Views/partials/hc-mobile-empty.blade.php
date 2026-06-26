@props([
    'icon' => 'fa-inbox',
    'title' => 'Nothing here yet',
    'text' => '',
    'actionUrl' => null,
    'actionLabel' => null,
])
<div class="hc-empty d-md-none">
    <div class="hc-empty__icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>
    <h2 class="hc-empty__title">{{ $title }}</h2>
    @if($text)
        <p class="hc-empty__text">{{ $text }}</p>
    @endif
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="hc-empty__btn">{{ $actionLabel }}</a>
    @endif
</div>

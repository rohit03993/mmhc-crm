@props([
    'icon' => 'fa-inbox',
    'title' => 'Nothing here yet',
    'text' => '',
    'actionUrl' => null,
    'actionLabel' => null,
])
<div class="acad-empty d-md-none">
    <div class="acad-empty__icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>
    <h2 class="acad-empty__title">{{ $title }}</h2>
    @if($text)
        <p class="acad-empty__text">{{ $text }}</p>
    @endif
    @if($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="acad-empty__btn">{{ $actionLabel }}</a>
    @endif
</div>

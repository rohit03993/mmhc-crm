@props([
    'label' => 'Academics',
    'title',
    'lede' => null,
])
<div class="acad-m-hero d-md-none">
    <p class="acad-m-hero__label">{{ $label }}</p>
    <h2 class="acad-m-hero__title">{{ $title }}</h2>
    @if($lede)
        <p class="acad-m-hero__lede">{{ $lede }}</p>
    @endif
</div>

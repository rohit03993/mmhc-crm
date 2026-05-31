@php
    $person = $person ?? null;
    $size = $size ?? 30;
    $avatarPath = $person?->profile?->avatar_path;
    $avatarUrl = $avatarPath ? \Illuminate\Support\Facades\Storage::url($avatarPath) : null;
    $initial = $person ? strtoupper(substr($person->name, 0, 1)) : '?';
@endphp
@if($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="" class="author-avatar author-avatar--photo" width="{{ $size }}" height="{{ $size }}" loading="lazy">
@else
    <div class="author-avatar" style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ max(0.72, $size / 38) }}rem;">{{ $initial }}</div>
@endif

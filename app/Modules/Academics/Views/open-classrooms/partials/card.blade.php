@php use Illuminate\Support\Str; @endphp
@php
    $showUrl = route('academics.open-classrooms.show', $classroom);
    $isTappable = empty($canJoin);
    $pill = 'open';
    $pillLabel = 'Public';
    if (!empty($owner)) {
        $pill = 'ok';
        $pillLabel = 'Yours';
    } elseif (!empty($joined)) {
        $pill = 'ok';
        $pillLabel = 'Joined';
    } elseif (!empty($canJoin)) {
        $pill = 'pending';
        $pillLabel = 'Not joined';
    }
@endphp
<article class="acad-oc-card{{ !empty($joined) ? ' acad-oc-card--joined' : '' }}{{ !empty($owner) ? ' acad-oc-card--mine' : '' }}">
    @if($isTappable)
        <a href="{{ $showUrl }}" class="acad-oc-card__tap">
    @else
        <div class="acad-oc-card__tap acad-oc-card__tap--static">
    @endif
        <div class="acad-oc-card__icon" aria-hidden="true">
            <i class="fas fa-chalkboard"></i>
        </div>
        <div class="acad-oc-card__content">
            <div class="acad-oc-card__head">
                <h3 class="acad-oc-card__title">{{ $classroom->title }}</h3>
                <span class="acad-status-pill acad-status-pill--{{ $pill }}">{{ $pillLabel }}</span>
            </div>
            @if($classroom->subject_area)
                <p class="acad-oc-card__subject">{{ $classroom->subject_area }}</p>
            @endif
            <p class="acad-oc-card__meta">
                <span><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>{{ $classroom->owner->name ?? 'Teacher' }}</span>
                <span><i class="fas fa-users" aria-hidden="true"></i>{{ $classroom->members_count }}</span>
            </p>
            @if($classroom->description)
                <p class="acad-oc-card__desc">{{ Str::limit($classroom->description, 90) }}</p>
            @endif
        </div>
        @if($isTappable)
            <i class="fas fa-chevron-right acad-oc-card__chev" aria-hidden="true"></i>
        @endif
    @if($isTappable)
        </a>
    @else
        </div>
    @endif

    @if(!empty($canJoin))
        <div class="acad-oc-card__footer">
            <form method="POST" action="{{ route('academics.open-classrooms.join', $classroom) }}" class="acad-oc-card__join-form">
                @csrf
                <button type="submit" class="acad-btn-primary w-100">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Join classroom
                </button>
            </form>
            <a href="{{ $showUrl }}" class="acad-btn-ghost acad-oc-card__preview">Preview</a>
        </div>
    @endif
</article>

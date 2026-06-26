@extends('auth::layout')

@section('title', $openClassroom->title)
@section('page-title', $openClassroom->title)

@section('content')
@php
    $notesCount = $openClassroom->resources->count();
    $assignCount = $openClassroom->assignments->count();
@endphp
<div class="container-fluid py-3 acad-mobile-page{{ $canJoin ? ' acad-mobile-page--sticky-cta' : '' }}" data-mmhc-ptr>
    <div class="acad-oc-show-hero">
        <div class="acad-oc-show-hero__icon" aria-hidden="true"><i class="fas fa-chalkboard"></i></div>
        <div class="acad-oc-show-hero__body">
            <div class="acad-oc-show-hero__top">
                <h1 class="acad-oc-show-hero__title">{{ $openClassroom->title }}</h1>
                @if($isOwner)
                    <span class="acad-status-pill acad-status-pill--ok">Your classroom</span>
                @elseif($isMember)
                    <span class="acad-status-pill acad-status-pill--ok">Joined</span>
                @endif
            </div>
            @if($openClassroom->subject_area)
                <p class="acad-oc-show-hero__subject">{{ $openClassroom->subject_area }}</p>
            @endif
            <p class="acad-oc-show-hero__meta mb-0">
                <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i>
                {{ $openClassroom->owner->name ?? '—' }}
            </p>
        </div>
    </div>

    <div class="acad-oc-stats mb-3">
        <div class="acad-oc-stats__item">
            <span class="acad-oc-stats__value">{{ $openClassroom->members_count }}</span>
            <span class="acad-oc-stats__label">Members</span>
        </div>
        <div class="acad-oc-stats__item">
            <span class="acad-oc-stats__value">{{ $notesCount }}</span>
            <span class="acad-oc-stats__label">Notes</span>
        </div>
        <div class="acad-oc-stats__item">
            <span class="acad-oc-stats__value">{{ $assignCount }}</span>
            <span class="acad-oc-stats__label">Tasks</span>
        </div>
    </div>

    @if($openClassroom->description)
        <p class="acad-oc-show-desc mb-3">{{ $openClassroom->description }}</p>
    @endif

    <div class="acad-homework-actions mb-3 d-none d-md-flex">
        @if($canJoin)
            <form method="POST" action="{{ route('academics.open-classrooms.join', $openClassroom) }}" class="flex-fill">
                @csrf
                <button type="submit" class="acad-btn-primary w-100">Join classroom</button>
            </form>
        @elseif($isMember && ! $isOwner)
            <form method="POST" action="{{ route('academics.open-classrooms.leave', $openClassroom) }}" onsubmit="return confirm('Leave this classroom?');">
                @csrf
                <button type="submit" class="acad-btn-ghost acad-btn-ghost--muted">Leave</button>
            </form>
        @endif
        @if($isOwner)
            <a href="{{ route('academics.open-classrooms.edit', $openClassroom) }}" class="acad-btn-ghost">Settings</a>
        @endif
    </div>

    @if($canJoin)
        <div class="acad-oc-guest-panel mb-3 d-md-none">
            <div class="acad-oc-guest-panel__icon" aria-hidden="true"><i class="fas fa-lock-open"></i></div>
            <p class="acad-oc-guest-panel__text mb-0">Join to unlock notes, files, and assignments from this teacher.</p>
        </div>
    @elseif($isMember && ! $isOwner)
        <div class="acad-homework-actions mb-3 d-md-none">
            <form method="POST" action="{{ route('academics.open-classrooms.leave', $openClassroom) }}" onsubmit="return confirm('Leave this classroom?');">
                @csrf
                <button type="submit" class="acad-btn-ghost acad-btn-ghost--muted w-100">Leave classroom</button>
            </form>
        </div>
    @elseif($isOwner)
        <div class="acad-homework-actions mb-3 d-md-none">
            <a href="{{ route('academics.open-classrooms.edit', $openClassroom) }}" class="acad-btn-ghost w-100 text-center">Classroom settings</a>
        </div>
    @endif

    @if($isOwner || $isMember)
        <section class="acad-homework-section">
            <h2 class="acad-homework-section__title">Notes &amp; materials</h2>
            <div class="acad-homework-section__body">
                @if($openClassroom->resources->isEmpty())
                    <p class="acad-oc-section-empty mb-0">No notes yet.</p>
                @else
                    <div class="acad-oc-resource-list">
                        @foreach($openClassroom->resources as $r)
                            @php
                                $rtype = $r->resource_type;
                                $ricon = match ($rtype) {
                                    \App\Modules\Academics\Models\OpenClassroomResource::TYPE_VIDEO_LINK => 'fa-play-circle',
                                    \App\Modules\Academics\Models\OpenClassroomResource::TYPE_FILE => 'fa-file-alt',
                                    default => 'fa-sticky-note',
                                };
                            @endphp
                            <article class="acad-oc-resource">
                                <div class="acad-oc-resource__icon" aria-hidden="true"><i class="fas {{ $ricon }}"></i></div>
                                <div class="acad-oc-resource__body">
                                    <h3 class="acad-oc-resource__title">{{ $r->title }}</h3>
                                    @if($r->description)
                                        <p class="acad-oc-resource__desc">{{ $r->description }}</p>
                                    @endif
                                    <div class="acad-oc-resource__actions">
                                        @if($rtype === \App\Modules\Academics\Models\OpenClassroomResource::TYPE_VIDEO_LINK && $r->video_url)
                                            <a href="{{ $r->video_url }}" target="_blank" rel="noopener" class="acad-btn-ghost acad-btn-primary--sm">Watch</a>
                                        @elseif($r->file_path)
                                            <a href="{{ route('academics.open-classrooms.resources.download', [$openClassroom, $r]) }}" class="acad-btn-ghost acad-btn-primary--sm">Download</a>
                                        @else
                                            <span class="acad-oc-resource__tag">Text note</span>
                                        @endif
                                        @if($isOwner)
                                            <form method="POST" action="{{ route('academics.open-classrooms.resources.destroy', [$openClassroom, $r]) }}" class="d-inline" onsubmit="return confirm('Remove?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="acad-btn-ghost acad-btn-ghost--muted acad-btn-primary--sm">Remove</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if($isOwner)
                    <details class="acad-oc-expand mt-3">
                        <summary class="acad-oc-expand__summary"><i class="fas fa-plus" aria-hidden="true"></i> Add note or file</summary>
                        <div class="acad-oc-expand__body">
                            <form method="POST" action="{{ route('academics.open-classrooms.resources.store', $openClassroom) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Title" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Type</label>
                                    <select name="resource_type" class="form-select">
                                        <option value="note">Text note</option>
                                        <option value="file">File (PDF, image)</option>
                                        <option value="video_link">Video link</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Content</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Content / description"></textarea>
                                </div>
                                <div class="mb-2">
                                    <input type="url" name="video_url" class="form-control" placeholder="YouTube / video URL (if video)">
                                </div>
                                <div class="mb-3">
                                    <input type="file" name="file" class="form-control">
                                </div>
                                <button type="submit" class="acad-btn-primary w-100">Add material</button>
                            </form>
                        </div>
                    </details>
                @endif
            </div>
        </section>

        <section class="acad-homework-section">
            <h2 class="acad-homework-section__title">Assignments</h2>
            <div class="acad-homework-section__body">
                @if($openClassroom->assignments->isEmpty())
                    <p class="acad-oc-section-empty mb-0">No assignments yet.</p>
                @else
                    <div class="acad-assignment-list">
                        @foreach($openClassroom->assignments as $a)
                            @php $sub = $mySubmissions->get($a->id); @endphp
                            <a href="{{ route('academics.open-classrooms.assignments.show', [$openClassroom, $a]) }}" class="acad-assignment-card acad-assignment-card--link">
                                <div class="acad-assignment-card__top">
                                    <h3 class="acad-assignment-card__title">{{ $a->title }}</h3>
                                    <span class="acad-status-pill acad-status-pill--{{ $sub ? 'ok' : 'pending' }}">{{ $sub ? 'Submitted' : 'Open' }}</span>
                                </div>
                                @if($a->due_date)
                                    <p class="acad-assignment-card__due mb-0">
                                        <i class="far fa-calendar" aria-hidden="true"></i>
                                        Due {{ $a->due_date->format('M j, Y') }}
                                    </p>
                                @endif
                                <span class="acad-assignment-card__open">Open task <i class="fas fa-chevron-right" aria-hidden="true"></i></span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($isOwner)
                    <details class="acad-oc-expand mt-3">
                        <summary class="acad-oc-expand__summary"><i class="fas fa-plus" aria-hidden="true"></i> Post new assignment</summary>
                        <div class="acad-oc-expand__body">
                            <form method="POST" action="{{ route('academics.open-classrooms.assignments.store', $openClassroom) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="Assignment title" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Instructions</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Instructions"></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Due date</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">Checklist</label>
                                    <textarea name="checklist_items_raw" class="form-control" rows="2" placeholder="One item per line"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold mb-1">Attachments</label>
                                    <input type="file" name="attachments[]" class="form-control" multiple>
                                </div>
                                <button type="submit" class="acad-btn-primary w-100">Publish assignment</button>
                            </form>
                        </div>
                    </details>
                @endif
            </div>
        </section>
    @else
        <div class="acad-oc-guest-panel acad-oc-guest-panel--full">
            <div class="acad-oc-guest-panel__icon" aria-hidden="true"><i class="fas fa-door-open"></i></div>
            <h2 class="acad-oc-guest-panel__title">Join to get started</h2>
            <p class="acad-oc-guest-panel__text">Notes, files, and assignments are available after you join this classroom.</p>
        </div>
    @endif

    @if($canJoin)
        <div class="acad-oc-sticky-cta d-md-none">
            <form method="POST" action="{{ route('academics.open-classrooms.join', $openClassroom) }}">
                @csrf
                <button type="submit" class="acad-btn-primary w-100">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Join classroom
                </button>
            </form>
        </div>
    @endif
</div>
@endsection

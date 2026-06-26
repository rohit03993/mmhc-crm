@extends('auth::layout')

@section('title', 'Edit classroom')
@section('page-title', 'Classroom settings')

@section('content')
<div class="container-fluid py-3 acad-mobile-page acad-mobile-page--form-footer" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none mb-3">
        <p class="acad-m-hero__label">Settings</p>
        <h2 class="acad-m-hero__title">{{ $openClassroom->title }}</h2>
        <p class="acad-m-hero__lede mb-0">Update details and whether new members can join.</p>
    </div>

    <div class="acad-form-card">
        <form method="POST" action="{{ route('academics.open-classrooms.update', $openClassroom) }}" id="oc-edit-form">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold" for="oc_title">Title</label>
                <input type="text" name="title" id="oc_title" class="form-control" value="{{ old('title', $openClassroom->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="oc_subject">Subject area</label>
                <input type="text" name="subject_area" id="oc_subject" class="form-control" value="{{ old('subject_area', $openClassroom->subject_area) }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="oc_desc">Description</label>
                <textarea name="description" id="oc_desc" class="form-control" rows="3">{{ old('description', $openClassroom->description) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold" for="oc_vis">Visibility</label>
                <select name="visibility" id="oc_vis" class="form-select">
                    <option value="public" @selected(old('visibility', $openClassroom->visibility) === 'public')>Public</option>
                    <option value="unlisted" @selected(old('visibility', $openClassroom->visibility) === 'unlisted')>Unlisted</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $openClassroom->is_active))>
                <label class="form-check-label" for="is_active">Accepting new members</label>
            </div>
            <div class="d-none d-md-flex gap-2">
                <button type="submit" class="acad-btn-primary">Save</button>
                <a href="{{ route('academics.open-classrooms.show', $openClassroom) }}" class="acad-btn-ghost">Back</a>
            </div>
        </form>
    </div>

    <div class="acad-form-footer d-md-none">
        <a href="{{ route('academics.open-classrooms.show', $openClassroom) }}" class="acad-btn-ghost">Back</a>
        <button type="submit" form="oc-edit-form" class="acad-btn-primary flex-grow-1">Save</button>
    </div>
</div>
@endsection

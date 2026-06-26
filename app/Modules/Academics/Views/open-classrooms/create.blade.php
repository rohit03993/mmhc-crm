@extends('auth::layout')

@section('title', 'Create open classroom')
@section('page-title', 'New classroom')

@section('content')
<div class="container-fluid py-3 acad-mobile-page acad-mobile-page--form-footer" data-mmhc-ptr>
    <div class="acad-m-hero d-md-none mb-3">
        <p class="acad-m-hero__label">Teaching</p>
        <h2 class="acad-m-hero__title">New classroom</h2>
        <p class="acad-m-hero__lede mb-0">Public rooms can be discovered and joined by any learner on the platform.</p>
    </div>

    <div class="acad-form-card">
        <form method="POST" action="{{ route('academics.open-classrooms.store') }}" id="oc-create-form">
            @csrf
            <h3 class="acad-form-card__title d-none d-md-block">Create your open classroom</h3>
            <p class="small text-muted d-none d-md-block">Anyone can discover and join public classrooms. You can post notes and assignments without a college.</p>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="title">Classroom name <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255" placeholder="e.g. Pharmacology for GNM">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="subject_area">Subject area</label>
                <input type="text" name="subject_area" id="subject_area" class="form-control" value="{{ old('subject_area') }}" maxlength="120" placeholder="Anatomy, Pharmacology, etc.">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" maxlength="5000" placeholder="What will learners study here?"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="visibility">Visibility</label>
                <select name="visibility" id="visibility" class="form-select">
                    <option value="public" @selected(old('visibility', 'public') === 'public')>Public — listed for all students</option>
                    <option value="unlisted" @selected(old('visibility') === 'unlisted')>Unlisted — only people with the link</option>
                </select>
            </div>

            <div class="d-none d-md-flex gap-2 flex-wrap">
                <button type="submit" class="acad-btn-primary">Create classroom</button>
                <a href="{{ route('academics.open-classrooms.index') }}" class="acad-btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    <div class="acad-form-footer d-md-none">
        <a href="{{ route('academics.open-classrooms.index') }}" class="acad-btn-ghost">Cancel</a>
        <button type="submit" form="oc-create-form" class="acad-btn-primary flex-grow-1">Create</button>
    </div>
</div>
@endsection

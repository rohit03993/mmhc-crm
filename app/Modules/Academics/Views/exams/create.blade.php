@extends('auth::layout')

@section('title', 'Create exam — Academics')
@section('page-title', 'Create exam')

@section('content')
<div class="container-fluid py-3 py-md-4">
    <div class="mb-3">
        <a href="{{ route('academics.exams.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fas fa-arrow-left me-1"></i>All exams</a>
    </div>
    <div class="card border shadow-sm rounded-3">
        <div class="card-body p-4">
            <h1 class="h5 fw-bold mb-3">New quiz / exam</h1>
            @include('academics::exams.partials.form', ['exam' => null])
        </div>
    </div>
</div>
@endsection

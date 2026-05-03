@extends('auth::layout')

@section('title', 'My OSCE sessions')
@section('page-title', 'Clinical skills (OSCE)')

@section('content')
<div class="container-fluid py-3">
    <p class="text-muted small mb-4">Sessions for your batch or college. Use station checklists during practice or assessment.</p>
    <div class="card">
        <div class="card-body p-0">
            @if($sessions->isEmpty())
                <p class="text-muted p-4 mb-0">No OSCE sessions available yet.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($sessions as $s)
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="fw-medium">{{ $s->title }}</span>
                                <span class="text-muted small ms-2">{{ $s->stations->count() }} stations</span>
                            </div>
                            <a href="{{ route('academics.osce.my.show', $s) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection

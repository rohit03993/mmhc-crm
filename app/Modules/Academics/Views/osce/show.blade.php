@extends('auth::layout')

@section('title', $session->title)
@section('page-title', 'OSCE: '.$session->title)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <p class="text-muted small mb-1">{{ $session->institution->name ?? '' }} @if($session->batch) · {{ $session->batch->name }} @else · <span class="badge bg-light text-dark border">College-wide</span> @endif</p>
            @if($session->starts_at)
                <p class="small mb-0"><strong>Starts:</strong> {{ $session->starts_at->format('M d, Y H:i') }} · <strong>Duration:</strong> {{ $session->duration_minutes }} min</p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            @empty($readOnly)
                <a href="{{ route('academics.osce.stations.create', $session) }}" class="btn btn-sm btn-primary">Add station</a>
                <a href="{{ route('academics.osce.edit', $session) }}" class="btn btn-sm btn-outline-secondary">Edit session</a>
                <form action="{{ route('academics.osce.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this OSCE session and all stations?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            @endempty
            <a href="{{ !empty($readOnly) ? route('academics.osce.my') : route('academics.osce.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </div>
    @if($session->description)
        <div class="alert alert-light border small mb-4" style="white-space: pre-line;">{{ $session->description }}</div>
    @endif
    <h3 class="h6">Stations</h3>
    @if($session->stations->isEmpty())
        @empty($readOnly)
            <p class="text-muted">No stations yet. Add a station with a checklist evaluators can follow.</p>
        @else
            <p class="text-muted">Stations will appear here when faculty publish them.</p>
        @endempty
    @else
        <div class="row g-3">
            @foreach($session->stations as $st)
                <div class="col-12 col-lg-6">
                    <div class="card h-100 border shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <h4 class="h6 mb-2">{{ $st->sort_order + 1 }}. {{ $st->name }}</h4>
                                @empty($readOnly)
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('academics.osce.stations.edit', [$session, $st]) }}" class="btn btn-outline-secondary">Edit</a>
                                        <form action="{{ route('academics.osce.stations.destroy', [$session, $st]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove station?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                @endempty
                            </div>
                            @if($st->time_limit_seconds)
                                <p class="small text-muted mb-2"><i class="fas fa-clock me-1"></i>{{ $st->time_limit_seconds }}s suggested limit</p>
                            @endif
                            @if($st->instructions)
                                <p class="small mb-2" style="white-space: pre-line;">{{ $st->instructions }}</p>
                            @endif
                            <strong class="small text-uppercase text-muted">Checklist</strong>
                            <ol class="small mb-0 mt-1 ps-3">
                                @foreach($st->checklist_items ?? [] as $row)
                                    @if(is_array($row))
                                        <li>{{ $row['label'] ?? '' }} <span class="text-muted">({{ rtrim(rtrim(number_format((float) ($row['points'] ?? 1), 2, '.', ''), '0'), '.') }} pt)</span></li>
                                    @endif
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

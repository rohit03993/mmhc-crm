@extends('auth::layout')

@section('title', 'OSCE sessions')
@section('page-title', 'OSCE sessions')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <p class="text-muted small mb-0">Structured clinical skills sessions: stations, timers, and printable checklists.</p>
        <a href="{{ route('academics.osce.create') }}" class="btn btn-primary btn-sm">New session</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            @if($sessions->isEmpty())
                <p class="text-muted p-4 mb-0">No OSCE sessions yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Institution</th>
                                <th>Batch</th>
                                <th>Starts</th>
                                <th>Stations</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $s)
                                <tr>
                                    <td class="fw-medium">{{ $s->title }}</td>
                                    <td>{{ $s->institution->name ?? '—' }}</td>
                                    <td>{{ $s->batch->name ?? 'All batches' }}</td>
                                    <td>{{ $s->starts_at ? $s->starts_at->format('M d, Y H:i') : '—' }}</td>
                                    <td>{{ $s->stations->count() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('academics.osce.show', $s) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', ($title ?? 'Report') . ' - Academics')
@section('page-title', $title ?? 'Report')

@section('content')
<div class="container-fluid py-3 no-print">
    @php
        $query = request()->only(['type', 'institution_id', 'batch_id']);
        $downloadUrl = route('academics.reports.download', $query);
    @endphp
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">{{ $title ?? 'Report' }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ $downloadUrl }}" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Download CSV</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();"><i class="fas fa-print me-1"></i>Print / Save as PDF</button>
            <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-secondary">Back to reports</a>
        </div>
    </div>
</div>

<div class="container-fluid py-3">
    <div class="card">
        <div class="card-body p-0">
            @if(empty($rows) || $rows->isEmpty())
                <p class="text-muted p-4 mb-0">No data for this report with the selected filters.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach($headers ?? [] as $h)
                                    <th>{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<style media="print">
    .no-print { display: none !important; }
    .sidebar, .navbar, .btn, nav { display: none !important; }
</style>
@endsection

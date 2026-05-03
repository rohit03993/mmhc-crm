@extends('auth::layout')

@section('title', ($title ?? 'Report') . ' - Academics')
@section('page-title', $title ?? 'Report')

@section('content')
<div class="container-fluid py-3 py-md-4 no-print">
    @php
        $query = request()->only(['type', 'institution_id', 'batch_id', 'subject_id', 'teaching_method_key', 'assignment_type', 'assessment_type_key', 'summative_only']);
        $downloadUrl = route('academics.reports.download', $query);
    @endphp
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">{{ $title ?? 'Report' }}</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $downloadUrl }}" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Download CSV</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print();"><i class="fas fa-print me-1"></i>Print / Save as PDF</button>
            <a href="{{ route('academics.reports.index') }}" class="btn btn-outline-secondary">Back to reports</a>
        </div>
    </div>
</div>

@if(($reportType ?? '') === 'student_submission' && (isset($reportInstitutions) || isset($reportBatches)))
<div class="container-fluid py-2 no-print">
    <form method="GET" action="{{ route('academics.reports.show') }}" class="card shadow-sm mb-3">
        <div class="card-body">
            <input type="hidden" name="type" value="student_submission">
            <input type="hidden" name="teaching_method_key" value="{{ request('teaching_method_key') }}">
            <input type="hidden" name="assignment_type" value="{{ request('assignment_type') }}">
            <input type="hidden" name="assessment_type_key" value="{{ request('assessment_type_key') }}">
            <input type="hidden" name="summative_only" value="{{ request()->boolean('summative_only') ? '1' : '0' }}">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="report_institution_id" class="form-label small mb-0">College</label>
                    <select name="institution_id" id="report_institution_id" class="form-select form-select-sm">
                        <option value="">All institutions</option>
                        @foreach($reportInstitutions as $inst)
                            <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label for="report_batch_id" class="form-label small mb-0">Batch</label>
                    <select name="batch_id" id="report_batch_id" class="form-select form-select-sm">
                        <option value="">All batches</option>
                        @foreach($reportBatches as $b)
                            <option value="{{ $b->id }}" data-institution-id="{{ $b->institution_id ?? '' }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->institution->name ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply filters</button>
                </div>
            </div>
        </div>
    </form>
    <script>
    (function() {
        var col = document.getElementById('report_institution_id');
        var batch = document.getElementById('report_batch_id');
        if (!col || !batch) return;
        function filterBatches() {
            var instId = col.value || '';
            [].slice.call(batch.options).forEach(function(opt) {
                if (opt.value === '') { opt.style.display = ''; return; }
                opt.style.display = (!instId || opt.getAttribute('data-institution-id') === instId) ? '' : 'none';
            });
        }
        col.addEventListener('change', filterBatches);
        filterBatches();
    })();
    </script>
</div>
@endif

<div class="container-fluid py-3">
    <div class="card shadow-sm">
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
                                @foreach($row as $index => $cell)
                                    <td>
                                        @if(($reportType ?? '') === 'student_submission' && $index === 0)
                                            <a href="{{ route('academics.reports.student', $row[7] ?? $cell) }}" class="text-primary fw-medium text-decoration-none">{{ $cell }}</a>
                                        @elseif(($reportType ?? '') === 'student_submission' && $index === 7)
                                            <a href="{{ route('academics.reports.student', $cell) }}" class="btn btn-sm btn-outline-primary">View full report</a>
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(isset($paginator) && $paginator->hasPages())
                <div class="p-3 border-top">
                    {{ $paginator->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<style media="print">
    .no-print { display: none !important; }
    .sidebar, .navbar, .btn, nav { display: none !important; }
</style>
@endsection

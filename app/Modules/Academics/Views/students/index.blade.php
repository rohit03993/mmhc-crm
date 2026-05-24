@extends('auth::layout')

@section('title', 'Students - Academics')
@section('page-title', 'Students')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">Students</h2>
        <a href="{{ route('academics.students.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add student</a>
    </div>

    @if(isset($institutions) && $institutions->isNotEmpty())
    <form action="{{ route('academics.students.index') }}" method="GET" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small text-muted mb-0">Institution</label>
            <select name="institution_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}" @selected((string) request('institution_id') === (string) $inst->id)>{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($students->isEmpty())
                <p class="text-muted p-4 mb-0">No students yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                @if(isset($institutions) && $institutions->isNotEmpty())<th>Institution</th>@endif
                                <th>Batches</th>
                                <th>Enrollment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $s)
                            <tr>
                                <td><a href="{{ route('academics.reports.student', $s) }}" class="fw-medium text-dark text-decoration-none">{{ $s->name }}</a></td>
                                <td>{{ $s->email ?? $s->phone }}</td>
                                @if(isset($institutions) && $institutions->isNotEmpty())
                                <td>{{ $s->academicInstitution->name ?? '—' }}</td>
                                @endif
                                <td class="small">{{ $s->academicBatches->pluck('name')->join(', ') ?: '—' }}</td>
                                <td>
                                    @php $st = $s->academic_enrollment_status ?? 'approved'; @endphp
                                    <span class="badge bg-{{ $st === 'approved' ? 'success' : ($st === 'pending' ? 'warning text-dark' : 'danger') }}">{{ ucfirst($st) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $students->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

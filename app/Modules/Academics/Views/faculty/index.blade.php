@extends('auth::layout')

@section('title', 'Faculty - Academics')
@section('page-title', 'Faculty')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">Faculty</h2>
        <a href="{{ route('academics.faculty.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add faculty</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($faculty->isEmpty())
                <p class="text-muted p-4 mb-0">No faculty yet. Use <strong>Add faculty</strong> to create a faculty account, then assign them to batches in <strong>Batches → Edit batch</strong>.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Unique ID</th>
                                <th>Phone</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($faculty as $f)
                            <tr>
                                <td>
                                    <a href="{{ route('academics.people.show', $f) }}" class="fw-medium text-dark text-decoration-none">{{ $f->name }}</a>
                                </td>
                                <td>{{ $f->email }}</td>
                                <td><code>{{ $f->unique_id ?? '—' }}</code></td>
                                <td>{{ $f->phone ?? '—' }}</td>
                                <td>
                                    @if($f->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $faculty->links() }}
                </div>
            @endif
        </div>
    </div>
    <p class="small text-muted mt-3 mb-0">To assign faculty to batches and subjects, go to <strong>Batches</strong> → select a batch → <strong>Edit</strong> → Assign students &amp; faculty.</p>
</div>
@endsection

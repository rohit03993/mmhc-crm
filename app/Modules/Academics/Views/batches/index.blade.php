@extends('auth::layout')

@section('title', 'Batches - Academics')
@section('page-title', 'Batches')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">Batches</h2>
        <a href="{{ route('academics.batches.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Batch</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($batches->isEmpty())
                <p class="text-muted p-4 mb-0">No batches yet. Create one from an institution.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Institution</th>
                                <th>Academic Year</th>
                                <th>Students</th>
                                <th>Faculty</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td>{{ $batch->name }}</td>
                                <td>{{ $batch->institution->name ?? '—' }}</td>
                                <td>{{ $batch->academic_year ?? '—' }}</td>
                                <td>{{ $batch->students()->count() }}</td>
                                <td>{{ $batch->faculty()->count() }}</td>
                                <td>
                                    @if($batch->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('academics.batches.edit', $batch) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('academics.batches.destroy', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this batch?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('auth::layout')

@section('title', 'Institutions - Academics')
@section('page-title', 'Institutions')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="h5 mb-0">Institutions</h2>
        <a href="{{ route('academics.institutions.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Institution</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($institutions->isEmpty())
                <p class="text-muted p-4 mb-0">No institutions yet. Create one to get started.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($institutions as $institution)
                            <tr>
                                <td>{{ $institution->name }}</td>
                                <td><code>{{ $institution->code ?? '—' }}</code></td>
                                <td>{{ $institution->email ?? $institution->phone ?? '—' }}</td>
                                <td>
                                    @if($institution->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('academics.institutions.edit', $institution) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('academics.institutions.destroy', $institution) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this institution?');">
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
                    {{ $institutions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

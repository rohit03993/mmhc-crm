@extends('auth::layout')

@section('title', 'Institutions - Academics')
@section('page-title', 'Institutions')

@section('content')
<div class="container-fluid py-3 acad-mobile-page" data-mmhc-ptr>
    @include('academics::partials.mobile-list-hero', ['title' => 'Institutions', 'lede' => 'Colleges on the platform.'])

    <div class="d-none d-md-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 academics-page-toolbar">
        <div>
            <h2 class="h5 mb-1">Institutions</h2>
            <p class="text-muted small mb-0">Each row is a college. <strong>ID</strong> is the database key (for linking users). <strong>Code</strong> is your short institute identifier (e.g. MMCN-BPL).</p>
        </div>
        <a href="{{ route('academics.institutions.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add institution</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($institutions->isEmpty())
                @include('academics::partials.mobile-empty-state', [
                    'icon' => 'fa-university',
                    'title' => 'No colleges yet',
                    'text' => 'Create an institution to get started.',
                    'actionUrl' => route('academics.institutions.create'),
                    'actionLabel' => 'Add institution',
                ])
                <p class="text-muted p-4 mb-0 d-none d-md-block">No institutions yet. Create one to get started.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">ID</th>
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
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $institution->id }}</span></td>
                                <td>
                                    <a href="{{ route('academics.institutions.show', $institution) }}" class="fw-medium text-dark text-decoration-none">{{ $institution->name }}</a>
                                </td>
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

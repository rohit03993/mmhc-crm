@extends('auth::layout')

@section('title', 'Site backups - Admin')
@section('page-title', 'Full site backup')

@section('head')
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-database me-2 text-primary"></i>Full site backup
                    </h2>
                    <p class="text-muted mb-0">Database dump + <code>storage/app/private</code> + <code>storage/app/public</code> (not S3 — use your cloud console for bucket copies).</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info">
                <strong>CLI:</strong>
                <code>php artisan backup:create</code> &mdash; create &middot;
                <code>php artisan backup:restore /full/path/to/mmhc-backup-….zip</code> &mdash; restore (maintenance mode recommended) &middot;
                <code>php artisan backup:prune</code> &mdash; delete old zips (see <code>BACKUP_KEEP_DAYS</code>).
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-plus-circle me-2"></i>New backup</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Requires MySQL client tools (<code>mysqldump</code>) on the server PATH, or set <code>BACKUP_MYSQLDUMP_PATH</code> in <code>.env</code>. SQLite copies the database file.</p>
                    <form method="POST" action="{{ route('admin.backups.store') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i>Create backup now
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-folder-open me-2"></i>Stored backups ({{ count($backups) }})
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>File</th>
                                    <th class="text-end">Size</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $b)
                                    <tr>
                                        <td><code class="small">{{ $b['name'] }}</code></td>
                                        <td class="text-end">{{ number_format($b['size'] / 1048576, 2) }} MiB</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestamp($b['modified'])->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.backups.download', ['filename' => $b['name']]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-archive me-1"></i>Download
                                            </a>
                                            <form method="POST" action="{{ route('admin.backups.destroy', ['filename' => $b['name']]) }}" class="d-inline" onsubmit="return confirm('Delete this backup file from the server?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center py-4">No backups yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

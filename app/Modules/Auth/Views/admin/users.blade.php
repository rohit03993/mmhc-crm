@extends('auth::layout')

@section('title', 'Manage Users - MMHC CRM')
@section('page-title', 'User Management')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->getBag('updateUser')->isNotEmpty())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Could not update user.</strong>
        <ul class="mb-0 mt-2 ps-3 small">@foreach($errors->getBag('updateUser')->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Create New User Card -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Create New User
                </h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-plus me-2"></i>
                    Add User
                </button>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                @php
                    $segment = $segment ?? 'all';
                    $segmentLabels = [
                        'all' => 'All users',
                        'academics' => 'Academic users',
                        'healthcare' => 'Healthcare & operations',
                    ];
                    $listTitle = $segmentLabels[$segment] ?? 'All users';
                @endphp
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">
                                <i class="fas fa-users me-2 text-primary"></i>
                                {{ $listTitle }}
                            </h5>
                            @if($segment !== 'all')
                                <p class="text-muted small mb-0">{{ $segment === 'academics' ? 'College roles only. Academic admin is listed first and is protected from bulk delete (same as CRM admin).' : 'Showing admin, nurses, caregivers, and patients.' }}</p>
                            @endif
                            <p class="text-muted small mb-0 mt-1 um-list-meta">
                                <strong>{{ number_format($users->total()) }}</strong> {{ $users->total() === 1 ? 'user' : 'users' }} total
                                @if($users->count() > 0)
                                    · showing <strong>{{ $users->firstItem() }}–{{ $users->lastItem() }}</strong> on this page
                                @endif
                                · <span id="umSelectedCount">0</span> selected
                            </p>
                            @if(($searchQuery ?? '') !== '')
                                <p class="text-muted small mb-0">{{ $users->total() }} {{ $users->total() === 1 ? 'match' : 'matches' }} for &ldquo;{{ \Illuminate\Support\Str::limit($searchQuery, 48) }}&rdquo;</p>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 flex-shrink-0 align-self-start">
                            <button type="button" class="btn btn-danger btn-sm rounded-pill d-none" id="umBulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#bulkDeleteUsersModal">
                                <i class="fas fa-trash-alt me-1"></i>
                                <span>Delete selected (<span id="umBulkDeleteCount">0</span>)</span>
                            </button>
                        </div>
                        @if(($unverifiedPhoneCount ?? 0) > 0)
                            <form method="POST" action="{{ route('admin.users.bulk-phone-reminders') }}" class="d-inline flex-shrink-0 align-self-start"
                                  onsubmit="return confirm('Send verification OTP by SMS to up to 150 users with unverified mobiles? Each user receives a 6-digit code.');">
                                @csrf
                                <input type="hidden" name="limit" value="150">
                                @if(($segment ?? 'all') !== 'all')
                                    <input type="hidden" name="segment" value="{{ $segment }}">
                                @endif
                                @if(($searchQuery ?? '') !== '')
                                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                                @endif
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="fas fa-sms me-1"></i>
                                    <span class="d-none d-sm-inline">OTP remind {{ $unverifiedPhoneCount }} unverified</span>
                                    <span class="d-sm-none">Remind ({{ $unverifiedPhoneCount }})</span>
                                </button>
                            </form>
                        @endif
                    </div>
                    <form id="umUserSearchForm" method="GET" action="{{ route('admin.users') }}" class="um-filter-form" role="search" aria-label="Search and filter users">
                        <div class="um-filter-row d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                            <div class="input-group input-group-sm um-search-input shadow-sm flex-grow-1">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search" aria-hidden="true"></i></span>
                                <input type="search"
                                       name="q"
                                       id="umUserSearchInput"
                                       class="form-control border-start-0"
                                       placeholder="Search name, email, phone, or ID…"
                                       value="{{ $searchQuery ?? '' }}"
                                       autocomplete="off"
                                       inputmode="search"
                                       aria-label="Search users">
                                @if(($searchQuery ?? '') !== '')
                                    <a href="{{ route('admin.users', array_filter(['segment' => $segment !== 'all' ? $segment : null, 'per_page' => ($perPage ?? 10) !== 10 ? $perPage : null])) }}" class="btn btn-outline-secondary border" title="Clear search text">Clear</a>
                                @endif
                            </div>
                            <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 flex-shrink-0">
                                <label class="visually-hidden" for="umUserSegment">User segment</label>
                                <select name="segment" id="umUserSegment" class="form-select form-select-sm um-segment-select" aria-label="Filter by user segment" onchange="this.form.submit()">
                                    <option value="all" @selected($segment === 'all')>Everyone</option>
                                    <option value="academics" @selected($segment === 'academics')>Academics only</option>
                                    <option value="healthcare" @selected($segment === 'healthcare')>Healthcare &amp; ops</option>
                                </select>
                                <label class="visually-hidden" for="umPerPage">Users per page</label>
                                <select name="per_page" id="umPerPage" class="form-select form-select-sm um-per-page-select" aria-label="Users per page" onchange="this.form.submit()" title="Users per page">
                                    @foreach(\App\Models\Core\User::adminListPerPageOptions() as $option)
                                        <option value="{{ $option }}" @selected(($perPage ?? 10) === $option)>{{ $option }} / page</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm px-4 um-search-submit">
                                    <i class="fas fa-search me-1 d-none d-sm-inline"></i>Search
                                </button>
                            </div>
                        </div>
                        <p class="text-muted small mb-0 mt-2 um-filter-hint"><i class="fas fa-info-circle me-1 opacity-75"></i>Use the list filter with search. Phone: 10 digits or +91 — spaces ignored.</p>
                        <p class="text-muted small mb-0 mt-1 um-filter-hint"><strong>Mobile verified</strong> means SMS OTP or admin manual verification. Admin verify unlocks app access and staff rewards/payouts.</p>
                    </form>
                </div>
            </div>
            @php
                $showInstitutionColumn = ($segment ?? 'all') === 'academics';
                $tableColCount = ($showInstitutionColumn ? 6 : 5) + 1;
            @endphp
            <div class="card-body p-0">
                <div class="um-table-wrap">
                    <table class="table table-hover align-middle mb-0 um-table mmhc-no-mobile-cards">
                        <thead class="table-light">
                            <tr>
                                <th class="um-col-check ps-3" style="width:2.5rem;">
                                    <input type="checkbox" class="form-check-input" id="umSelectAllPage" title="Select all on this page" aria-label="Select all users on this page">
                                </th>
                                <th class="ps-2">User</th>
                                <th>Contact</th>
                                @if($showInstitutionColumn)
                                    <th class="um-col-institution">Institution</th>
                                @endif
                                <th class="um-col-profile">Profile</th>
                                <th class="um-col-status">Status</th>
                                <th class="text-end pe-4 um-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('auth::admin.partials.users-table-rows', [
                                'users' => $users,
                                'showInstitutionColumn' => $showInstitutionColumn,
                                'tableColCount' => $tableColCount,
                                'searchQuery' => $searchQuery ?? '',
                                'segment' => $segment ?? 'all',
                            ])
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 p-3 border-top bg-light um-pagination-bar">
                    <div class="d-flex flex-wrap align-items-center gap-2 small text-muted">
                        @if($users->total() > 0)
                            <span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span>
                            <span class="text-muted">·</span>
                            <span><strong>{{ number_format($users->total()) }}</strong> users</span>
                            <span class="text-muted">·</span>
                            <span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                        @endif
                        <form method="GET" action="{{ route('admin.users') }}" class="d-inline-flex align-items-center gap-1 ms-md-2 um-per-page-form">
                            @if($segment !== 'all')
                                <input type="hidden" name="segment" value="{{ $segment }}">
                            @endif
                            @if(($searchQuery ?? '') !== '')
                                <input type="hidden" name="q" value="{{ $searchQuery }}">
                            @endif
                            <label for="umPerPageFooter" class="mb-0 text-nowrap">Per page</label>
                            <select name="per_page" id="umPerPageFooter" class="form-select form-select-sm" style="width:auto; min-width:4.5rem;" onchange="this.form.submit()">
                                @foreach(\App\Models\Core\User::adminListPerPageOptions() as $option)
                                    <option value="{{ $option }}" @selected(($perPage ?? 10) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @if($users->hasPages())
                        <div>{{ $users->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.um-filter-form { max-width: 100%; }
.um-filter-row { min-width: 0; }
.um-search-input { border-radius: 0.5rem; overflow: hidden; min-width: 0; }
.um-search-input .form-control:focus { box-shadow: none; border-color: #dee2e6; }
.um-segment-select {
    cursor: pointer;
    min-width: 11.5rem;
    border-radius: 0.5rem;
}
.um-per-page-select {
    cursor: pointer;
    min-width: 6.5rem;
    border-radius: 0.5rem;
}
.um-pagination-bar .um-per-page-form select {
    cursor: pointer;
}
@media (min-width: 768px) {
    .um-segment-select { max-width: 14rem; }
    .um-search-submit { white-space: nowrap; }
}
.um-filter-row .um-segment-select,
.um-filter-row .um-search-submit {
    min-height: calc(1.5em + 0.5rem + 2px);
}
.um-filter-hint { line-height: 1.4; }
.um-table-wrap { overflow-x: visible; width: 100%; }
.um-table { table-layout: fixed; width: 100%; }
.um-table thead th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.um-table td { vertical-align: middle; word-break: break-word; }
.um-col-user { width: 18%; }
.um-col-contact { width: 22%; }
.um-col-institution { width: 18%; }
.um-col-profile { width: 16%; }
.um-col-status { width: 10%; }
.um-col-actions { width: 4.5rem; }
.um-table__row:hover { background: #f8fafc; }
.um-name-link:hover { color: #0d9488 !important; text-decoration: underline !important; }
.um-table__link:hover .um-table__id .badge { background-color: #0f766e !important; }
.um-table__user-cell { max-width: 100%; }
.um-avatar { width: 40px; height: 40px; object-fit: cover; flex-shrink: 0; }
.um-avatar--placeholder { width: 40px; height: 40px; background: linear-gradient(135deg, #312e81, #1d4ed8); flex-shrink: 0; }
.um-completion-bar { margin-bottom: 0; max-width: 100%; }
.um-actions-dropdown .dropdown-menu { min-width: 12rem; }
.um-actions-dropdown .dropdown-item { font-size: 0.875rem; }
@media (max-width: 1199.98px) {
    .um-table-wrap { overflow-x: visible; }
    .um-table { table-layout: auto; }
    .um-col-user, .um-col-contact, .um-col-institution, .um-col-profile, .um-col-status { width: auto; }
}
@media (max-width: 767.98px) {
    .um-table-wrap { overflow-x: visible; padding: 0.75rem; }
    .um-table thead { display: none; }
    .um-table, .um-table tbody, .um-table tr, .um-table td { display: block; width: 100%; }
    .um-table tbody tr {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.75rem 0.875rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .um-table tbody td {
        border: 0;
        padding: 0.35rem 0;
        text-align: left !important;
    }
    .um-table tbody td::before {
        content: attr(data-label);
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .um-table tbody td.um-col-actions::before { display: none; }
    .um-table tbody td.um-col-actions { padding-top: 0.5rem; }
    .um-table tbody td.ps-4, .um-table tbody td.pe-4 { padding-left: 0 !important; padding-right: 0 !important; }
}
</style>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>
                    Create New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body">
                    @if($errors->getBag('createUser')->isNotEmpty())
                        <div class="alert alert-danger small mb-3">
                            <ul class="mb-0 ps-3">@foreach($errors->getBag('createUser')->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name', 'createUser') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select @error('role', 'createUser') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <optgroup label="Healthcare &amp; CRM">
                                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                    <option value="nurse" @selected(old('role') === 'nurse')>Nurse</option>
                                    <option value="caregiver" @selected(old('role') === 'caregiver')>Caregiver</option>
                                    <option value="patient" @selected(old('role') === 'patient')>Patient</option>
                                </optgroup>
                                <optgroup label="Academics">
                                    <option value="super_admin" @selected(old('role') === 'super_admin')>Academic admin (all institutions)</option>
                                    <option value="institution_admin" @selected(old('role') === 'institution_admin')>Institution admin</option>
                                    <option value="faculty" @selected(old('role') === 'faculty')>Faculty</option>
                                    <option value="student" @selected(old('role') === 'student')>Student</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="row d-none" id="create_academic_institution_row">
                        <div class="col-12 mb-3">
                            <label for="academic_institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                            <select class="form-select @error('academic_institution_id', 'createUser') is-invalid @enderror" id="academic_institution_id" name="academic_institution_id">
                                <option value="">Select institution</option>
                                @foreach($institutions ?? [] as $inst)
                                    <option value="{{ $inst->id }}" @selected((string) old('academic_institution_id') === (string) $inst->id)>
                                        {{ $inst->name }}@if($inst->code) ({{ $inst->code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @if(($institutions ?? collect())->isEmpty())
                                <div class="form-text text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Add an institution under Academics first (super admin), or run the academic demo seeder.</div>
                            @else
                                <small class="text-muted">Required for institution admin, faculty, and students.</small>
                            @endif
                        </div>
                    </div>
                    <div class="row d-none" id="create_academic_batches_row">
                        <div class="col-12 mb-3">
                            <label for="academic_batch_ids" class="form-label">Batches <span class="text-muted fw-normal">(optional)</span></label>
                            <select class="form-select @error('academic_batch_ids', 'createUser') is-invalid @enderror @error('academic_batch_ids.*', 'createUser') is-invalid @enderror" id="academic_batch_ids" name="academic_batch_ids[]" multiple size="5">
                            </select>
                            <small class="text-muted">Choose one or more batches for this institution (faculty and students). Leave empty to assign later.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email', 'createUser') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control @error('phone', 'createUser') is-invalid @enderror" id="phone" name="phone" pattern="[0-9]{10}" maxlength="10" placeholder="9876543210" value="{{ old('phone') }}" required>
                            </div>
                            <small class="form-text text-muted">Enter 10-digit Indian mobile number</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pincode', 'createUser') is-invalid @enderror" id="pincode" name="pincode" pattern="[1-9][0-9]{5}" maxlength="6" placeholder="462001" value="{{ old('pincode') }}" required>
                            <small class="form-text text-muted">Enter 6-digit Indian pincode</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address', 'createUser') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Enter full address">{{ old('address') }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password', 'createUser') is-invalid @enderror" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk delete selected users -->
<div class="modal fade" id="bulkDeleteUsersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Delete selected accounts</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.users.bulk-delete') }}" id="bulkDeleteUsersForm">
                @csrf
                @if(($segment ?? 'all') !== 'all')
                    <input type="hidden" name="segment" value="{{ $segment }}">
                @endif
                @if(($searchQuery ?? '') !== '')
                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                @endif
                @if(($perPage ?? 10) !== 10)
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif
                <div id="bulkDeleteUserIdInputs"></div>
                <div class="modal-body">
                    <div class="alert alert-danger small mb-3">
                        <strong>Permanent.</strong> Profiles, visits, subscriptions, invoices, academics, and files for selected users are removed. Referral history for other staff stays visible as <span class="badge bg-secondary">Inactive</span>. Mobile numbers can be reused.
                    </div>
                    <p class="mb-2">You are deleting <strong id="bulkDeleteModalCount">0</strong> account(s) on this page.</p>
                    <ul id="bulkDeleteNamePreview" class="small text-muted mb-3 ps-3"></ul>
                    <label class="form-label small fw-semibold" for="bulk_confirm_phrase">Type <code>DELETE</code> to confirm</label>
                    <input type="text" class="form-control" id="bulk_confirm_phrase" name="confirm_phrase" autocomplete="off" required pattern="DELETE" placeholder="DELETE">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="bulkDeleteSubmitBtn" disabled>
                        <i class="fas fa-trash-alt me-1"></i>Delete permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Single user delete -->
<div class="modal fade" id="deleteSingleUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-user-times me-2"></i>Delete account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="deleteSingleUserForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="mb-2">Remove <strong id="deleteSingleUserName">—</strong> (<span id="deleteSingleUserUid" class="text-muted">—</span>)?</p>
                    <p class="small text-muted">Referral rows for other staff remain with an <span class="badge bg-secondary">Inactive</span> label. Phone/email can be registered again.</p>
                    <label class="form-label small fw-semibold mt-3" for="single_confirm_phrase">Type <code>DELETE</code> to confirm</label>
                    <input type="text" class="form-control" id="single_confirm_phrase" name="confirm_phrase" autocomplete="off" required pattern="DELETE" placeholder="DELETE">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteSingleSubmitBtn" disabled>Delete permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editUserForm" action="">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editUserContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $mmhcBatchesForJs = ($batches ?? collect())->map(function ($b) {
        return [
            'id' => $b->id,
            'institution_id' => $b->institution_id,
            'label' => $b->name . ($b->academic_year ? ' (' . $b->academic_year . ')' : ''),
        ];
    })->values();
    $mmhcOldBatchIds = old('academic_batch_ids');
    $mmhcOldBatchIds = is_array($mmhcOldBatchIds) ? $mmhcOldBatchIds : [];
@endphp
<script>
window.mmhcInstitutions = @json($institutions ?? []);
window.mmhcBatches = @json($mmhcBatchesForJs);

function mmhcEscapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text == null ? '' : String(text);
    return d.innerHTML;
}

function mmhcInstitutionOptions(selectedId) {
    const insts = window.mmhcInstitutions || [];
    let html = '<option value="">Select institution</option>';
    insts.forEach(function (i) {
        const sel = String(selectedId || '') === String(i.id) ? ' selected' : '';
        const label = (i.name || '') + (i.code ? ' (' + i.code + ')' : '');
        html += '<option value="' + i.id + '"' + sel + '>' + mmhcEscapeHtml(label) + '</option>';
    });
    return html;
}

function umRebuildCreateBatchOptions(preserveSelectedIds) {
    const instSel = document.getElementById('academic_institution_id');
    const batchSel = document.getElementById('academic_batch_ids');
    if (!batchSel || !instSel) return;
    const instId = instSel.value ? String(instSel.value) : '';
    const preserve = new Set((preserveSelectedIds || []).map(String));
    const batches = window.mmhcBatches || [];
    batchSel.innerHTML = '';
    batches.filter(function (b) { return String(b.institution_id) === instId; }).forEach(function (b) {
        const opt = document.createElement('option');
        opt.value = b.id;
        opt.textContent = b.label || ('Batch #' + b.id);
        if (preserve.has(String(b.id))) opt.selected = true;
        batchSel.appendChild(opt);
    });
}

function umSyncCreateAcademicInstitutionRow() {
    const roleEl = document.getElementById('role');
    const row = document.getElementById('create_academic_institution_row');
    const sel = document.getElementById('academic_institution_id');
    if (!roleEl || !row) return;
    const needs = ['institution_admin', 'faculty', 'student'].indexOf(roleEl.value) !== -1;
    row.classList.toggle('d-none', !needs);
    if (sel) {
        sel.required = needs;
        if (!needs) sel.value = '';
    }
    umSyncCreateAcademicBatchesRow();
}

function umSyncCreateAcademicBatchesRow() {
    const roleEl = document.getElementById('role');
    const row = document.getElementById('create_academic_batches_row');
    const batchSel = document.getElementById('academic_batch_ids');
    if (!roleEl || !row) return;
    const needsInst = ['institution_admin', 'faculty', 'student'].indexOf(roleEl.value) !== -1;
    const needsBatch = ['faculty', 'student'].indexOf(roleEl.value) !== -1;
    row.classList.toggle('d-none', !(needsInst && needsBatch));
    if (batchSel && needsInst && needsBatch) {
        batchSel.disabled = false;
        const selected = Array.from(batchSel.selectedOptions).map(function (o) { return o.value; });
        umRebuildCreateBatchOptions(selected.length ? selected : []);
    } else if (batchSel) {
        batchSel.disabled = true;
        batchSel.innerHTML = '';
    }
}

document.getElementById('role') && document.getElementById('role').addEventListener('change', umSyncCreateAcademicInstitutionRow);
document.getElementById('academic_institution_id') && document.getElementById('academic_institution_id').addEventListener('change', function () {
    umSyncCreateAcademicBatchesRow();
});
document.addEventListener('DOMContentLoaded', function () {
    umSyncCreateAcademicInstitutionRow();
    umRebuildCreateBatchOptions(@json($mmhcOldBatchIds));
});

function mmhcBatchMultiOptions(institutionId, selectedIds) {
    const inst = institutionId != null && institutionId !== '' ? String(institutionId) : '';
    const sel = new Set((selectedIds || []).map(String));
    const batches = window.mmhcBatches || [];
    let html = '';
    batches.filter(function (b) { return String(b.institution_id) === inst; }).forEach(function (b) {
        const isSel = sel.has(String(b.id)) ? ' selected' : '';
        html += '<option value="' + b.id + '"' + isSel + '>' + mmhcEscapeHtml(b.label || ('Batch #' + b.id)) + '</option>';
    });
    return html;
}

function umSyncEditAcademicRow() {
    const roleEl = document.getElementById('edit_role');
    const row = document.getElementById('edit_academic_institution_row');
    const sel = document.getElementById('edit_academic_institution_id');
    const batchRow = document.getElementById('edit_academic_batches_row');
    const batchSel = document.getElementById('edit_academic_batch_ids');
    if (!roleEl || !row) return;
    const needs = ['institution_admin', 'faculty', 'student'].indexOf(roleEl.value) !== -1;
    row.style.display = needs ? '' : 'none';
    if (sel) sel.required = needs;
    const showBatches = ['faculty', 'student'].indexOf(roleEl.value) !== -1 && needs;
    if (batchRow) batchRow.style.display = showBatches ? '' : 'none';
    if (batchSel) {
        if (!showBatches) {
            batchSel.disabled = true;
            batchSel.innerHTML = '';
        } else {
            batchSel.disabled = false;
            if (sel) {
                const selected = Array.from(batchSel.selectedOptions).map(function (o) { return parseInt(o.value, 10); }).filter(Boolean);
                batchSel.innerHTML = mmhcBatchMultiOptions(sel.value, selected.length ? selected : []);
            }
        }
    }
}

// Edit User Function
function editUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    const content = document.getElementById('editUserContent');
    const form = document.getElementById('editUserForm');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    form.action = `/admin/users/${userId}`;
    modal.show();
    
    fetch(`/admin/users/${userId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="${String(user.name || '').replace(/"/g, '&quot;')}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <optgroup label="Healthcare &amp; CRM">
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                    <option value="nurse" ${user.role === 'nurse' ? 'selected' : ''}>Nurse</option>
                                    <option value="caregiver" ${user.role === 'caregiver' ? 'selected' : ''}>Caregiver</option>
                                    <option value="patient" ${user.role === 'patient' ? 'selected' : ''}>Patient</option>
                                </optgroup>
                                <optgroup label="Academics">
                                    <option value="super_admin" ${user.role === 'super_admin' ? 'selected' : ''}>Academic admin</option>
                                    <option value="institution_admin" ${user.role === 'institution_admin' ? 'selected' : ''}>Institution admin</option>
                                    <option value="faculty" ${user.role === 'faculty' ? 'selected' : ''}>Faculty</option>
                                    <option value="student" ${user.role === 'student' ? 'selected' : ''}>Student</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="edit_academic_institution_row" style="display:none;">
                        <div class="col-12 mb-3">
                            <label for="edit_academic_institution_id" class="form-label">Institution <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_academic_institution_id" name="academic_institution_id">
                                ${mmhcInstitutionOptions(user.academic_institution_id)}
                            </select>
                        </div>
                    </div>
                    <div class="row" id="edit_academic_batches_row" style="display:none;">
                        <div class="col-12 mb-3">
                            <label for="edit_academic_batch_ids" class="form-label">Batches <span class="text-muted fw-normal">(optional)</span></label>
                            <select class="form-select" id="edit_academic_batch_ids" name="academic_batch_ids[]" multiple size="5">
                                ${mmhcBatchMultiOptions(user.academic_institution_id, user.academic_batch_ids || [])}
                            </select>
                            <small class="text-muted">Batches must match the institution above.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="edit_email" name="email" value="${user.email}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" value="${user.phone}" pattern="[0-9]{10}" maxlength="10" placeholder="9876543210" required>
                            </div>
                            <small class="form-text text-muted">Enter 10-digit Indian mobile number</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_pincode" name="pincode" value="${user.pincode || ''}" pattern="[1-9][0-9]{5}" maxlength="6" placeholder="462001" required>
                            <small class="form-text text-muted">Enter 6-digit Indian pincode</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth" value="${user.date_of_birth || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_is_active" class="form-label">Status</label>
                            <select class="form-select" id="edit_is_active" name="is_active">
                                <option value="1" ${user.is_active ? 'selected' : ''}>Active</option>
                                <option value="0" ${!user.is_active ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2">${user.address || ''}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Mobile verification</label>
                            <div class="alert ${user.has_verified_phone ? 'alert-success' : 'alert-warning'} mb-2 py-2 small">
                                ${user.has_verified_phone
                                    ? '<i class="fas fa-check-circle me-1"></i>Verified' + (user.phone_verified_at ? ' — ' + mmhcEscapeHtml(user.phone_verified_at) : '') + (user.phone_verified_source_label ? '<br><span class="text-muted">' + mmhcEscapeHtml(user.phone_verified_source_label) + (user.phone_verified_by_admin ? ' (' + mmhcEscapeHtml(user.phone_verified_by_admin) + ')' : '') + '</span>' : '')
                                    : '<i class="fas fa-exclamation-triangle me-1"></i>Not verified — user cannot use the app until SMS OTP or you verify manually.'}
                            </div>
                            ${!user.has_verified_phone && user.phone ? `
                                <form method="POST" action="/admin/users/${user.id}/verify-phone" class="d-inline me-2" onsubmit="return confirm('Manually verify this mobile? Unlocks app and staff rewards/payouts.');">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i>Verify mobile (admin)</button>
                                </form>
                            ` : ''}
                            ${user.has_verified_phone ? `
                                <form method="POST" action="/admin/users/${user.id}/revoke-phone-verification" class="d-inline" onsubmit="return confirm('Revoke verification? User must verify again via SMS OTP.');">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-undo me-1"></i>Revoke verification</button>
                                </form>
                            ` : ''}
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Change Password (Leave blank to keep current password)</label>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                ${user.plain_password
                                    ? 'A password is already set. Leave the fields above blank to keep it, or enter a new password to replace it.'
                                    : 'No readable stored password (either none was saved for admin view, or it was encrypted on another server — different APP_KEY). Set a new password above if you need to change it.'}
                            </div>
                        </div>
                    </div>
                `;
                const er = document.getElementById('edit_role');
                const eInst = document.getElementById('edit_academic_institution_id');
                if (er) er.addEventListener('change', umSyncEditAcademicRow);
                if (eInst) eInst.addEventListener('change', function () {
                    const batchSel = document.getElementById('edit_academic_batch_ids');
                    const prev = batchSel ? Array.from(batchSel.selectedOptions).map(function (o) { return parseInt(o.value, 10); }) : [];
                    if (batchSel) batchSel.innerHTML = mmhcBatchMultiOptions(eInst.value, prev);
                });
                umSyncEditAcademicRow();
            } else {
                content.innerHTML = '<div class="alert alert-danger">Failed to load user details for editing.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">An error occurred while loading user details.</div>';
        });
}

// Debounced live search (GET): empty clears; 2+ chars submits after pause
(function () {
    const form = document.getElementById('umUserSearchForm');
    const input = document.getElementById('umUserSearchInput');
    if (!form || !input) {
        return;
    }
    let timer;
    const debounceMs = 420;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const v = this.value.trim();
        if (v.length === 0) {
            timer = setTimeout(function () { form.submit(); }, debounceMs);
            return;
        }
        if (v.length < 2) {
            return;
        }
        timer = setTimeout(function () { form.submit(); }, debounceMs);
    });
})();

// Toggle User Status Function
function toggleUserStatus(userId) {
    if (confirm('Are you sure you want to change this user\'s status?')) {
        // Create and submit form for status toggle
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/toggle-status`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

@if($errors->getBag('createUser')->isNotEmpty())
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('createUserModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
    }
    umSyncCreateAcademicInstitutionRow();
    umRebuildCreateBatchOptions(@json($mmhcOldBatchIds));
});
@endif

(function () {
    const checkboxes = () => Array.from(document.querySelectorAll('.um-user-checkbox'));
    const selectAll = document.getElementById('umSelectAllPage');
    const selectedCountEl = document.getElementById('umSelectedCount');
    const bulkBtn = document.getElementById('umBulkDeleteBtn');
    const bulkCountEl = document.getElementById('umBulkDeleteCount');
    const bulkModalCount = document.getElementById('bulkDeleteModalCount');
    const bulkPreview = document.getElementById('bulkDeleteNamePreview');
    const bulkIdInputs = document.getElementById('bulkDeleteUserIdInputs');
    const bulkPhrase = document.getElementById('bulk_confirm_phrase');
    const bulkSubmit = document.getElementById('bulkDeleteSubmitBtn');

    function selectedBoxes() {
        return checkboxes().filter(function (cb) { return cb.checked; });
    }

    function syncSelectionUi() {
        const selected = selectedBoxes();
        const n = selected.length;
        if (selectedCountEl) selectedCountEl.textContent = String(n);
        if (bulkCountEl) bulkCountEl.textContent = String(n);
        if (bulkModalCount) bulkModalCount.textContent = String(n);
        if (bulkBtn) bulkBtn.classList.toggle('d-none', n === 0);
        if (selectAll) {
            const all = checkboxes();
            selectAll.checked = all.length > 0 && n === all.length;
            selectAll.indeterminate = n > 0 && n < all.length;
        }
        if (bulkPreview) {
            bulkPreview.innerHTML = '';
            selected.forEach(function (cb) {
                const row = cb.closest('tr');
                const nameEl = row ? row.querySelector('.um-name-link') : null;
                const li = document.createElement('li');
                li.textContent = nameEl ? nameEl.textContent.trim() : ('User #' + cb.value);
                bulkPreview.appendChild(li);
            });
        }
        if (bulkIdInputs) {
            bulkIdInputs.innerHTML = '';
            selected.forEach(function (cb) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = cb.value;
                bulkIdInputs.appendChild(input);
            });
        }
    }

    checkboxes().forEach(function (cb) {
        cb.addEventListener('change', syncSelectionUi);
    });
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes().forEach(function (cb) { cb.checked = selectAll.checked; });
            syncSelectionUi();
        });
    }
    if (bulkPhrase && bulkSubmit) {
        bulkPhrase.addEventListener('input', function () {
            bulkSubmit.disabled = bulkPhrase.value.trim() !== 'DELETE' || selectedBoxes().length === 0;
        });
    }
    const bulkModal = document.getElementById('bulkDeleteUsersModal');
    if (bulkModal) {
        bulkModal.addEventListener('show.bs.modal', syncSelectionUi);
    }
    syncSelectionUi();
})();

function openDeleteUserModal(userId, name, uniqueId) {
    const form = document.getElementById('deleteSingleUserForm');
    const phrase = document.getElementById('single_confirm_phrase');
    const submit = document.getElementById('deleteSingleSubmitBtn');
    if (!form) return;
    form.action = '/admin/users/' + userId;
    document.getElementById('deleteSingleUserName').textContent = name;
    document.getElementById('deleteSingleUserUid').textContent = uniqueId;
    if (phrase) phrase.value = '';
    if (submit) submit.disabled = true;
    if (phrase) {
        phrase.oninput = function () {
            submit.disabled = phrase.value.trim() !== 'DELETE';
        };
    }
    const modalEl = document.getElementById('deleteSingleUserModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(modalEl).show();
    }
}

</script>
@endsection

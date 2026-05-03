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
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="flex-shrink-0">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-users me-2 text-primary"></i>
                            All users
                        </h5>
                        @if(($searchQuery ?? '') !== '')
                            <small class="text-muted">{{ $users->total() }} {{ $users->total() === 1 ? 'match' : 'matches' }} for &ldquo;{{ \Illuminate\Support\Str::limit($searchQuery, 48) }}&rdquo;</small>
                        @endif
                    </div>
                    <form id="umUserSearchForm" method="GET" action="{{ route('admin.users') }}" class="um-search flex-grow-1" style="min-width: 220px; max-width: 28rem;" role="search" aria-label="Search users">
                        <div class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden border bg-white">
                            <span class="input-group-text border-0 bg-white text-muted ps-3"><i class="fas fa-search" aria-hidden="true"></i></span>
                            <input type="search"
                                   name="q"
                                   id="umUserSearchInput"
                                   class="form-control border-0 shadow-none"
                                   placeholder="Name, email, phone, or unique ID…"
                                   value="{{ $searchQuery ?? '' }}"
                                   autocomplete="off"
                                   inputmode="search"
                                   aria-label="Search by name, email, or phone">
                            @if(($searchQuery ?? '') !== '')
                                <a href="{{ route('admin.users') }}" class="btn btn-link btn-sm text-decoration-none text-muted px-2 border-0">Clear</a>
                            @endif
                            <button type="submit" class="btn btn-primary px-3 rounded-0">Search</button>
                        </div>
                        <small class="text-muted d-block mt-1 ms-1">Tip: paste a 10-digit number or +91 number — spaces and dashes are ignored.</small>
                    </form>
                    <div class="ms-md-auto flex-shrink-0">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteNonAdminModal">
                            <i class="fas fa-trash-alt me-2"></i>
                            Delete all non-admin
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 um-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap ps-4">Unique ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="um-table__row">
                                    <td class="ps-4">
                                        <a href="{{ route('admin.profiles.view', $user) }}" class="um-table__link um-table__id text-decoration-none">
                                            <span class="badge rounded-pill bg-secondary">{{ $user->unique_id }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.profiles.view', $user) }}" class="um-table__link text-decoration-none">
                                            <span class="fw-semibold text-dark d-block">{{ $user->name }}</span>
                                            <span class="small text-muted">Profile &amp; stats</span>
                                        </a>
                                    </td>
                                    <td class="text-muted small">{{ $user->email }}</td>
                                    <td class="text-muted small">{{ $user->phone }}</td>
                                    <td>
                                        <span class="badge rounded-pill
                                            @if($user->role == 'admin') bg-danger
                                            @elseif($user->role == 'caregiver') bg-primary
                                            @elseif($user->role == 'nurse') bg-info text-dark
                                            @else bg-success @endif">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ $user->is_active ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            @if(in_array($user->role, ['nurse', 'caregiver']))
                                                <a class="btn btn-outline-success rounded-start-pill"
                                                   href="{{ route('admin.staff.incentives', $user) }}"
                                                   title="Full incentives">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.profiles.view', $user) }}" class="btn btn-outline-primary @if(!in_array($user->role, ['nurse', 'caregiver'])) rounded-start-pill @endif" title="Profile &amp; stats">
                                                <i class="fas fa-id-card"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning" onclick="editUser({{ $user->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary rounded-end-pill" onclick="toggleUserStatus({{ $user->id }})" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fas fa-users fa-2x mb-2 opacity-50"></i><br>
                                        @if(($searchQuery ?? '') !== '')
                                            No users match &ldquo;{{ \Illuminate\Support\Str::limit($searchQuery, 60) }}&rdquo;. Try another name, email fragment, or phone digits.
                                        @else
                                            No users found
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="d-flex justify-content-center p-3 border-top bg-light">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.um-table thead th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
.um-table__row:hover { background: #f8fafc; }
.um-table__link:hover .fw-semibold { color: #0d9488 !important; text-decoration: underline; }
.um-table__link:hover .um-table__id .badge { background-color: #0f766e !important; }
.um-search .input-group-text { min-width: 2.25rem; justify-content: center; }
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="caregiver">Caregiver</option>
                                <option value="nurse">Nurse</option>
                                <option value="patient">Patient</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">+91</span>
                                <input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]{10}" maxlength="10" placeholder="9876543210" required>
                            </div>
                            <small class="form-text text-muted">Enter 10-digit Indian mobile number</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pincode" name="pincode" pattern="[1-9][0-9]{5}" maxlength="6" placeholder="462001" required>
                            <small class="form-text text-muted">Enter 6-digit Indian pincode</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter full address"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
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

<!-- Delete Non-Admin Users Confirmation Modal -->
<div class="modal fade" id="deleteNonAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Delete All Non-Admin Users
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                <p>You are about to delete <strong>all non-admin users</strong> from the system.</p>
                <p class="mb-0">This will permanently delete:</p>
                <ul>
                    <li>All nurses</li>
                    <li>All caregivers</li>
                    <li>All patients</li>
                </ul>
                <p class="mt-3 mb-0"><strong>Admin users will remain protected and will not be deleted.</strong></p>
                <p class="text-muted mt-2">Are you absolutely sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <form method="POST" action="{{ route('admin.users.delete-non-admin') }}" id="deleteNonAdminForm">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i>Yes, Delete All Non-Admin Users
                    </button>
                </form>
            </div>
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

<script>
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
                            <input type="text" class="form-control" id="edit_name" name="name" value="${user.name}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                <option value="caregiver" ${user.role === 'caregiver' ? 'selected' : ''}>Caregiver</option>
                                <option value="nurse" ${user.role === 'nurse' ? 'selected' : ''}>Nurse</option>
                                <option value="patient" ${user.role === 'patient' ? 'selected' : ''}>Patient</option>
                            </select>
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

</script>
@endsection

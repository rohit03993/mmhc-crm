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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    All Users
                </h5>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteNonAdminModal">
                    <i class="fas fa-trash-alt me-2"></i>
                    Delete All Non-Admin Users
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Unique ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user->unique_id }}</span>
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($user->role == 'admin') bg-danger
                                            @elseif($user->role == 'caregiver') bg-primary
                                            @elseif($user->role == 'nurse') bg-info
                                            @else bg-success @endif">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-warning' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewUser({{ $user->id }})"
                                                    title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="editUser({{ $user->id }})"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" 
                                                    onclick="toggleUserStatus({{ $user->id }})"
                                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2"></i><br>
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    User Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
// View User Function
function viewUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
    const content = document.getElementById('viewUserContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    fetch(`/admin/users/${userId}/view`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Unique ID:</strong><br>
                            <span class="badge bg-secondary">${user.unique_id}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            <span class="badge ${user.is_active ? 'bg-success' : 'bg-warning'}">
                                ${user.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Full Name:</strong><br>
                            ${user.name}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong><br>
                            ${user.email}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Phone:</strong><br>
                            ${user.phone}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Role:</strong><br>
                            <span class="badge bg-primary">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            ${user.date_of_birth || 'Not provided'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Created:</strong><br>
                            ${user.created_at}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Address:</strong><br>
                            ${user.address || 'Not provided'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Pincode:</strong><br>
                            ${user.pincode || 'Not provided'}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <strong>Reward Points:</strong><br>
                            ${user.reward_points || 0} points (₹${((user.reward_points || 0) * 10).toFixed(2)})
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <strong>Password:</strong><br>
                            <div class="input-group">
                                <input type="text" class="form-control" id="viewPassword" value="${user.plain_password || 'Not available'}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyPassword('viewPassword')">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">This password is visible only to admin</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-warning btn-sm" onclick="resetUserPassword(${userId})">
                                <i class="fas fa-key me-2"></i>Reset Password
                            </button>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="alert alert-danger">Failed to load user details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">An error occurred while loading user details.</div>';
        });
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
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Current Password: <strong>${user.plain_password || 'Not available'}</strong>
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

// Reset Password Function
function resetUserPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password? A new random password will be generated.')) {
        fetch(`/admin/users/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password reset successfully! New password: ' + data.new_password);
                // Update the password field in the view modal
                document.getElementById('viewPassword').value = data.new_password;
            } else {
                alert('Failed to reset password.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while resetting password.');
        });
    }
}

// Copy Password Function
function copyPassword(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    alert('Password copied to clipboard!');
}
</script>
@endsection

@php
    $deletionPolicy = app(\App\Services\AccountDeletion\DeletionPolicy::class);
    $actingAdmin = auth()->user();
@endphp
@forelse($users as $user)
    @php
        $canDelete = $actingAdmin && $deletionPolicy->canSelectForBulkDelete($user, $actingAdmin);
        $roleBadge = match ($user->role) {
            'admin' => 'bg-danger',
            'institution_admin' => 'text-bg-primary',
            'faculty' => 'bg-info text-dark',
            'student' => 'text-bg-secondary',
            'nurse' => 'bg-info text-dark',
            'caregiver' => 'bg-primary',
            'patient' => 'bg-success',
            default => 'bg-secondary',
        };
        $roleLabel = match ($user->role) {
            'institution_admin' => 'Inst. admin',
            default => ucfirst(str_replace('_', ' ', $user->role)),
        };
        $institutionLabel = '—';
        if ($user->academicInstitution) {
            $institutionLabel = $user->academicInstitution->name;
            if ($user->academicInstitution->code) {
                $institutionLabel .= ' ('.$user->academicInstitution->code.')';
            }
        }
        $batchLabel = (in_array($user->role, ['faculty', 'student'], true) && $user->academicBatches->isNotEmpty())
            ? $user->academicBatches->pluck('name')->join(', ')
            : null;
    @endphp
    <tr class="um-table__row" data-user-id="{{ $user->id }}">
        <td class="um-col-check ps-3" data-label="Select">
            @if($canDelete)
                <input type="checkbox" class="form-check-input um-user-checkbox" name="user_ids[]" value="{{ $user->id }}" aria-label="Select {{ $user->name }}">
            @else
                <span class="text-muted small" title="Protected account"><i class="fas fa-lock"></i></span>
            @endif
        </td>
        <td class="ps-2 um-col-user" data-label="User">
            <div class="d-flex align-items-start gap-2 um-table__user-cell">
                @if($user->profile?->avatar_path)
                    <img src="{{ Storage::url($user->profile->avatar_path) }}" alt="" class="um-avatar rounded-circle" width="40" height="40">
                @else
                    <div class="um-avatar um-avatar--placeholder rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fas fa-user text-white small"></i>
                    </div>
                @endif
                <div class="min-w-0 flex-grow-1">
                    <a href="{{ route('admin.profiles.view', $user) }}" class="um-name-link fw-semibold text-dark text-decoration-none d-block text-truncate" title="{{ $user->name }}">
                        {{ $user->name }}
                    </a>
                    <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                        <a href="{{ route('admin.profiles.view', $user) }}" class="um-table__link text-decoration-none">
                            <span class="badge rounded-pill bg-secondary um-table__id">{{ $user->unique_id }}</span>
                        </a>
                        <span class="badge rounded-pill {{ $roleBadge }}">{{ $roleLabel }}</span>
                    </div>
                </div>
            </div>
        </td>
        <td class="um-col-contact" data-label="Contact">
            <div class="um-contact min-w-0">
                <div class="um-contact__email small text-muted text-truncate" title="{{ $user->email }}">{{ $user->email }}</div>
                <div class="um-contact__phone small">{{ $user->phone ?: '—' }}</div>
                <div class="um-contact__verify d-flex flex-wrap align-items-center gap-1 mt-1">
                    @if($user->phone && $user->phone_verified_at)
                        <span class="badge rounded-pill bg-success">Verified</span>
                        @if($user->phone_verified_source === 'admin')
                            <span class="badge rounded-pill bg-primary" title="Verified manually by admin">Admin</span>
                        @endif
                    @elseif($user->phone)
                        <span class="badge rounded-pill bg-warning text-dark">Unverified</span>
                    @endif
                </div>
                @if($user->phoneVerificationSourceLabel())
                    <div class="um-contact__proof small text-muted mt-1">{{ $user->phoneVerificationSourceLabel() }}</div>
                @endif
            </div>
        </td>
        @if($showInstitutionColumn)
            <td class="um-col-institution" data-label="Institution">
                <div class="um-institution min-w-0">
                    <div class="small fw-medium text-truncate" title="{{ $institutionLabel }}">{{ $institutionLabel }}</div>
                    @if($batchLabel)
                        <div class="small text-muted text-truncate mt-1" title="{{ $batchLabel }}">{{ $batchLabel }}</div>
                    @endif
                </div>
            </td>
        @endif
        <td class="um-col-profile" data-label="Profile">
            <div class="um-profile-summary">
                @if($user->profile && $user->profile->isComplete())
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Complete</span>
                @elseif($user->profile)
                    <span class="badge bg-warning-subtle text-dark border">Incomplete</span>
                @else
                    <span class="text-muted small">No profile</span>
                @endif
                @if($user->profile)
                    <div class="progress um-completion-bar mt-1" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $user->profile->getCompletionPercentage() }}%"></div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mt-1">
                        <span class="small text-muted">{{ $user->profile->getCompletionPercentage() }}%</span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">{{ $user->documents_count ?? 0 }} docs</span>
                    </div>
                @else
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill mt-1">{{ $user->documents_count ?? 0 }} docs</span>
                @endif
            </div>
        </td>
        <td class="um-col-status" data-label="Status">
            <span class="badge rounded-pill {{ $user->is_active ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
            <div class="small text-muted mt-1">{{ $user->created_at->format('M d, Y') }}</div>
        </td>
        <td class="text-end pe-4 um-col-actions" data-label="Actions">
            <div class="dropdown um-actions-dropdown">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="User actions">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profiles.view', $user) }}">
                            <i class="fas fa-eye me-2 text-primary"></i>View profile
                        </a>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" onclick="editUser({{ $user->id }})">
                            <i class="fas fa-edit me-2 text-warning"></i>Edit user
                        </button>
                    </li>
                    @if(in_array($user->role, ['nurse', 'caregiver'], true))
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.staff.incentives', $user) }}">
                                <i class="fas fa-chart-line me-2 text-success"></i>Incentives
                            </a>
                        </li>
                    @endif
                    @if(in_array($user->role, ['nurse', 'caregiver'], true) && $user->hasVerifiedPhone())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.staff.id-card', $user) }}">
                                <i class="fas fa-id-card me-2 text-secondary"></i>ID card
                            </a>
                        </li>
                    @endif
                    @if($user->phone && ! $user->hasVerifiedPhone())
                        <li>
                            <form method="POST" action="{{ route('admin.users.verify-phone', $user) }}" onsubmit="return confirm(@json('Manually verify mobile for '.$user->name.'? They will see verified by admin and can use the app + rewards.'));">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-check-double me-2 text-success"></i>Verify mobile
                                </button>
                            </form>
                        </li>
                    @elseif($user->hasVerifiedPhone())
                        <li>
                            <form method="POST" action="{{ route('admin.users.revoke-phone-verification', $user) }}" onsubmit="return confirm(@json('Revoke mobile verification for '.$user->name.'? They must verify again via WhatsApp OTP.'));">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-mobile-alt me-2"></i>Revoke verification
                                </button>
                            </form>
                        </li>
                    @endif
                    @if($canDelete)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item text-danger" onclick="openDeleteUserModal({{ $user->id }}, @json($user->name), @json($user->unique_id))">
                            <i class="fas fa-trash-alt me-2"></i>Delete account
                        </button>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item" onclick="toggleUserStatus({{ $user->id }})">
                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-2"></i>{{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ $tableColCount }}" class="text-center text-muted py-5">
            <i class="fas fa-users fa-2x mb-2 opacity-50"></i><br>
            @if(($searchQuery ?? '') !== '')
                No users match &ldquo;{{ \Illuminate\Support\Str::limit($searchQuery, 60) }}&rdquo; with this filter. Try clearing search or switching segment.
            @elseif(($segment ?? 'all') !== 'all')
                No users in this segment yet. Try &ldquo;Everyone&rdquo; or seed academic demo data.
            @else
                No users found
            @endif
        </td>
    </tr>
@endforelse

@php
    $roleBadge = match ($user->role) {
        'admin', 'super_admin' => 'danger',
        'nurse' => 'info',
        'caregiver', 'faculty' => 'primary',
        'patient' => 'success',
        default => 'secondary',
    };
    $completionPct = $profile ? $profile->getCompletionPercentage() : 0;
@endphp

@include('profiles::partials.apv-base-styles')

<div class="apv profile-top mb-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="apv-card apv-card--hero text-center">
                @include('profiles::profile.partials.avatar-upload', ['profile' => $profile, 'variant' => 'apv'])

                <h1 class="h4 fw-bold mb-1">{{ $user->name }}</h1>
                <p class="text-muted small mb-2">{{ $user->email }}</p>

                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    <span class="badge rounded-pill bg-{{ $roleBadge }} px-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                    <span class="badge rounded-pill bg-light text-dark border px-3">{{ $user->unique_id }}</span>
                    <span class="badge rounded-pill {{ $user->is_active ? 'bg-success text-white' : 'bg-warning text-dark' }} px-3">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if($profile)
                    <div class="px-1 mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Profile completion</span>
                            <span class="fw-semibold">{{ $completionPct }}%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $completionPct }}%"></div>
                        </div>
                    </div>
                @endif

                <div class="d-flex flex-column gap-2 px-1">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm rounded-pill w-100">
                        <i class="fas fa-edit me-1"></i>Edit profile
                    </a>
                    @if($user->isStaff())
                        @if($user->hasVerifiedPhone())
                            <a href="{{ route('profile.id-card') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                                <i class="fas fa-id-card me-1"></i>View ID card
                            </a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                                <i class="fas fa-mobile-alt me-1"></i>Verify mobile for ID card
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="apv-card h-100">
                <h2 class="apv-card__title"><i class="fas fa-id-card me-2 text-primary"></i>Account details</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="apv-k">Phone</span>
                        <p class="apv-v mb-0">{{ $user->displayPhone() ?: '—' }}</p>
                        <div class="mt-2">
                            @if($pendingDisplay = $user->displayPendingPhone())
                                <span class="badge bg-warning text-dark">Pending: {{ $pendingDisplay }}</span>
                                <a href="{{ route('profile.edit') }}" class="small d-block mt-1">Complete OTP verification →</a>
                            @elseif($user->hasVerifiedPhone())
                                <span class="badge bg-success">Mobile verified</span>
                                @if($user->phone_verified_source === 'admin')
                                    <span class="badge bg-primary ms-1">{{ $user->phoneVerificationUserLabel() }}</span>
                                @else
                                    <span class="text-muted small d-block mt-1">{{ $user->phoneVerificationUserLabel() }} · {{ $user->phone_verified_at?->format('M j, Y') }}</span>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">Mobile not verified</span>
                                @if($user->isStaff())
                                    <span class="text-muted small d-block mt-1">Verify mobile to unlock payouts and ID card.</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <span class="apv-k">Date of birth</span>
                        <p class="apv-v mb-0">{{ $user->getFormattedDateOfBirth() }}</p>
                    </div>
                    <div class="col-12">
                        <span class="apv-k">Address</span>
                        <p class="apv-v mb-0">{{ $user->address ?: '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="apv-k">Pincode</span>
                        <p class="apv-v mb-0">{{ $user->pincode ?: '—' }}</p>
                    </div>
                    @if($user->qualification || $user->experience)
                        <div class="col-md-6">
                            <span class="apv-k">Qualification / experience</span>
                            <p class="apv-v mb-0">
                                {{ $user->qualification ?: '—' }}
                                @if($user->experience)
                                    <span class="text-muted">·</span> {{ $user->experience }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

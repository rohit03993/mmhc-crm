@php
    $user = auth()->user();
    $canCreatePosts = $user->isAdmin() || $user->isNurse() || $user->isCaregiver();
@endphp

<div class="alert alert-light border community-phone-tip mb-3" role="status">
    <div class="d-flex gap-2 align-items-start">
        <i class="fas fa-mobile-alt text-primary mt-1"></i>
        <div class="small">
            <strong>Your MMHC account uses your mobile number.</strong>
            No email is required to use Community — sign in with SMS OTP on the Phone tab anytime.
            @if($user->unique_id)
                <span class="d-block text-muted mt-1">Your ID: <strong>{{ $user->unique_id }}</strong></span>
            @endif
        </div>
    </div>
</div>

@if(! $canCreatePosts)
    <div class="card mb-3 border-0 shadow-sm community-surface-card community-reader-card">
        <div class="card-body py-3">
            <h6 class="mb-2"><i class="fas fa-hand-sparkles me-2 text-primary"></i>Welcome to the team feed</h6>
            <p class="small text-muted mb-2">
                You can <strong>react</strong>, <strong>comment</strong>, and respond to <strong>event notices</strong>.
                Staff and admin share updates here — stay connected with your care team.
            </p>
            <p class="small mb-0 text-muted">
                <i class="fas fa-user-circle me-1"></i>
                Add a profile photo from <a href="{{ route('profile.index') }}">My Profile</a> so others recognize you in comments.
            </p>
        </div>
    </div>
@endif

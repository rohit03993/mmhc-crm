@extends('auth::layout')

@section('title', 'Patient Rewards - Staff Dashboard')

@section('content')
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Patient Rewards</h5>
    </div>
</div>

<div class="container-fluid px-3 py-4">
    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="{{ route('staff.incentives.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i>View All Incentive Details
            </a>
        </div>
    </div>

    <!-- Stats Banner -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-warning">
                <div class="stats-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ number_format($stats['total_points']) }}</div>
                    <div class="stats-label">Total Points</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-success">
                <div class="stats-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">₹{{ number_format($stats['total_amount'], 2) }}</div>
                    <div class="stats-label">Total Earnings</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card-modern bg-info">
                <div class="stats-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">{{ $stats['total_submissions'] }}</div>
                    <div class="stats-label">Total Submissions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="action-card-modern">
                <div class="action-card-header">
                    <i class="fas fa-plus-circle me-2"></i>
                    <h5 class="mb-0">Submit Patient Details</h5>
                </div>
                <div class="action-card-body">
                    <p class="mb-3">Earn <strong>1 point (₹10)</strong> for each valid patient detail submission.</p>
                    <a href="{{ route('rewards.create') }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards List -->
    <div class="row">
        <div class="col-12">
            <div class="section-header mb-3">
                <h5 class="section-title">
                    <i class="fas fa-history me-2"></i>Reward History
                </h5>
            </div>

            @if($rewards->count() > 0)
                <div class="rewards-list-cards">
                    @foreach($rewards as $reward)
                        <div class="reward-entry-card-modern">
                            <div class="reward-entry-header-modern">
                                <div class="reward-entry-info">
                                    <div class="reward-entry-name">{{ $reward->patient_name }}</div>
                                    <div class="reward-entry-meta">
                                        <i class="fas fa-phone me-1"></i>{{ $reward->patient_phone }}
                                        @if($reward->patient_age)
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-birthday-cake me-1"></i>Age: {{ $reward->patient_age }}
                                        @endif
                                    </div>
                                </div>
                                <div class="reward-entry-badge-modern">
                                    <span class="badge-points">+{{ $reward->reward_points }} pts</span>
                                    <span class="badge-amount">₹{{ number_format($reward->reward_amount, 2) }}</span>
                                    <span class="badge bg-{{ ($reward->verification_status ?? 'verified') === 'verified' ? 'success' : 'warning text-dark' }}">
                                        {{ ucfirst($reward->verification_status ?? 'verified') }}
                                    </span>
                                </div>
                            </div>
                            <div class="reward-entry-details-modern">
                                @if($reward->hospital_name)
                                <div class="detail-row">
                                    <i class="fas fa-hospital me-2 text-primary"></i>
                                    <span>{{ $reward->hospital_name }}</span>
                                </div>
                                @endif
                                @if($reward->patient_address)
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                    <span>{{ $reward->patient_address }}</span>
                                </div>
                                @endif
                                <div class="detail-row">
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    <span>{{ $reward->created_at->format('M d, Y') }} • {{ $reward->created_at->diffForHumans() }}</span>
                                </div>
                                @if(($reward->verification_status ?? 'verified') !== 'verified')
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="sendRewardOtp({{ $reward->id }})">Send OTP</button>
                                    <button type="button" class="btn btn-sm btn-success" onclick="verifyRewardOtp({{ $reward->id }})">Verify OTP</button>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $rewards->links() }}
                </div>
            @else
                <div class="empty-state-modern">
                    <div class="empty-state-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h5>No Rewards Yet</h5>
                    <p class="text-muted">Start submitting patient details to earn rewards!</p>
                    <a href="{{ route('rewards.create') }}" class="btn btn-warning mt-3">
                        <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@include('auth::components.bottom-nav')

<style>
.stats-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    border-top: 4px solid;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
}

.bg-warning .stats-icon { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); }
.bg-success .stats-icon { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.bg-info .stats-icon { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); }

.stats-content {
    flex: 1;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    line-height: 1.2;
}

.stats-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.action-card-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.action-card-header {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    font-weight: 600;
}

.action-card-body {
    padding: 1.5rem;
}

.reward-entry-card-modern {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    border-left: 4px solid #ffc107;
}

.reward-entry-card-modern:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.reward-entry-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.reward-entry-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.reward-entry-meta {
    font-size: 0.85rem;
    color: #6c757d;
}

.reward-entry-badge-modern {
    text-align: right;
}

.badge-points {
    display: block;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.badge-amount {
    display: block;
    color: #28a745;
    font-weight: 700;
    font-size: 1rem;
}

.reward-entry-details-modern {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
}

.empty-state-modern {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto 1.5rem;
}

@media (max-width: 768px) {
    .stats-card-modern {
        padding: 1.25rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .stats-value {
        font-size: 1.5rem;
    }
}
</style>
<script>
function sendRewardOtp(rewardId) {
    fetch(`/rewards/${rewardId}/send-otp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        alert(data.message || (data.success ? 'OTP sent' : 'Failed to send OTP'));
        if (data.success) location.reload();
    }).catch(() => alert('Failed to send OTP'));
}
function verifyRewardOtp(rewardId) {
    const otp = prompt('Enter patient OTP (6 digits):');
    if (!otp) return;
    fetch(`/rewards/${rewardId}/verify-otp`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ otp_code: otp })
    }).then(r => r.json()).then(data => {
        alert(data.message || (data.success ? 'Verified' : 'Verification failed'));
        if (data.success) location.reload();
    }).catch(() => alert('Failed to verify OTP'));
}
</script>
@endsection


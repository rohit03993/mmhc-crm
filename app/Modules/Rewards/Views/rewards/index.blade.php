@extends('auth::layout')

@section('title', 'My Reward Entries')

@section('content')
<!-- Mobile App View for Rewards -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <div class="app-user-avatar rewards-avatar">
                    <i class="fas fa-gift"></i>
                </div>
                <div class="app-user-info">
                    <div class="app-user-name">My Rewards</div>
                    <div class="app-user-id">{{ $totalPoints }} Points</div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('rewards.create') }}" class="app-header-icon">
                    <i class="fas fa-plus-circle"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="mb-0">My Reward Entries</h2>
            @if(!empty($pendingVerificationCount) && $pendingVerificationCount > 0)
                <p class="text-warning mb-0 mt-1"><i class="fas fa-exclamation-triangle me-1"></i>{{ $pendingVerificationCount }} entries are pending patient SMS OTP and not yet credited.</p>
            @endif
            @if(!empty($heldEarningsDueToUnverifiedMobile))
                <p class="text-warning mb-0 mt-1"><i class="fas fa-mobile-alt me-1"></i>₹{{ number_format((float) $heldEarningsDueToUnverifiedMobile['total'], 2) }} in verified earnings is on hold until you verify your account mobile in Profile.</p>
            @elseif(!empty($staffNeedsMobileVerification))
                <p class="text-warning mb-0 mt-1"><i class="fas fa-mobile-alt me-1"></i>Verify your account mobile in Profile to unlock reward payouts.</p>
            @endif
        </div>

        <!-- Total Points Card - Mobile Optimized -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="reward-summary-card">
                    <div class="reward-summary-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="reward-summary-content">
                        <div class="reward-summary-label">Total Reward Points</div>
                        <div class="reward-summary-value">{{ $totalPoints }}</div>
                        <div class="reward-summary-amount">Reward Value: ₹{{ number_format($totalAmount, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8">
                <div class="reward-action-card">
                    <div class="reward-action-header">
                        <i class="fas fa-plus-circle me-2"></i>
                        <span>Submit New Patient Details</span>
                    </div>
                    <div class="reward-action-body">
                        <p class="reward-action-text">Each valid submission earns <strong>1 point</strong> (₹10).</p>
                        <form method="GET" action="{{ route('rewards.create') }}">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions - Card Format for Mobile -->
        <div class="rewards-list-section">
            <div class="rewards-list-header">
                <h5 class="rewards-list-title">
                    <i class="fas fa-history me-2"></i>Recent Submissions
                </h5>
            </div>

            @if($rewards->count() > 0)
                <div class="rewards-list-cards">
                    @foreach($rewards as $reward)
                        <div class="reward-entry-card">
                            <div class="reward-entry-header">
                                <div class="reward-entry-patient">
                                    <div class="reward-entry-name">{{ $reward->patient_name }}</div>
                                    @if($reward->patient_age)
                                        <div class="reward-entry-meta">
                                            <i class="fas fa-birthday-cake me-1"></i>Age: {{ $reward->patient_age }}
                                        </div>
                                    @endif
                                </div>
                                <div class="reward-entry-badge">
                                    @php
                                        $staffMobileOk = empty($staffNeedsMobileVerification);
                                        $rewardBlockers = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardBlockers($reward, $staffMobileOk);
                                        $statusMessages = \App\Modules\Payments\Services\StaffEarningStatusResolver::detailMessagesForBlockers(
                                            $rewardBlockers,
                                            \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardMaskedPhone($reward)
                                        );
                                        $showRewardAmount = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardCountsForStaff($reward, $staffMobileOk)
                                            || $reward->payment_processed;
                                    @endphp
                                    @if($showRewardAmount)
                                        <span class="badge bg-success">+{{ $reward->reward_points }} pt</span>
                                        <small class="d-block text-success mt-1">₹{{ number_format($reward->reward_amount, 2) }}</small>
                                    @else
                                        <span class="badge bg-secondary">0 pts · not credited</span>
                                        <small class="d-block text-muted mt-1">Complete patient SMS OTP + Profile mobile</small>
                                    @endif
                                    <div class="mt-1">
                                        @include('services::staff.partials.payout-status-blockers', ['blockers' => $rewardBlockers, 'compact' => true, 'align' => 'end'])
                                    </div>
                                    @foreach($statusMessages as $statusMessage)
                                        <small class="d-block text-warning mt-1">{{ $statusMessage }}</small>
                                    @endforeach
                                </div>
                            </div>

                            <div class="reward-entry-details">
                                <div class="reward-entry-detail-item">
                                    <i class="fas fa-phone text-primary"></i>
                                    <span>{{ $reward->patient_phone }}</span>
                                </div>
                                @if($reward->patientUser?->unique_id)
                                <div class="reward-entry-detail-item">
                                    <i class="fas fa-id-card text-success"></i>
                                    <span><strong>{{ $reward->patientUser->unique_id }}</strong> <span class="text-muted small">(patient login)</span></span>
                                </div>
                                @endif
                                @if($reward->patient_address)
                                    <div class="reward-entry-detail-item">
                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                        <span>{{ Str::limit($reward->patient_address, 40) }}</span>
                                    </div>
                                @endif
                                @if($reward->patient_pincode)
                                    <div class="reward-entry-detail-item">
                                        <i class="fas fa-map-pin text-info"></i>
                                        <span>PIN: {{ $reward->patient_pincode }}</span>
                                    </div>
                                @endif
                                <div class="reward-entry-detail-item">
                                    <i class="fas fa-hospital text-success"></i>
                                    <span>{{ $reward->hospital_name }}</span>
                                </div>
                                @if($reward->treatment_details)
                                    <div class="reward-entry-detail-item">
                                        <i class="fas fa-stethoscope text-warning"></i>
                                        <span>{{ Str::limit($reward->treatment_details, 50) }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="reward-entry-footer">
                                <div class="reward-entry-date">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $reward->created_at->format('d M Y, h:i A') }}
                                </div>
                                @if(in_array(\App\Modules\Payments\Services\StaffEarningStatusResolver::PENDING_PATIENT_OTP, $rewardBlockers, true))
                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="sendOtp({{ $reward->id }})">Resend SMS OTP</button>
                                    <button class="btn btn-sm btn-success" type="button" onclick="verifyOtp({{ $reward->id }})">Verify OTP</button>
                                    @if($reward->canChangePatientPhone())
                                    <button class="btn btn-sm btn-outline-secondary btn-change-patient-phone" type="button"
                                            data-reward-id="{{ $reward->id }}"
                                            data-current-phone="{{ substr(preg_replace('/\D/', '', (string) $reward->patient_phone), -10) }}">
                                        <i class="fas fa-edit me-1"></i>Change number
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($rewards->hasPages())
                    <div class="rewards-pagination">
                        {{ $rewards->links() }}
                    </div>
                @endif
            @else
                <div class="rewards-empty-state">
                    <div class="rewards-empty-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h5 class="rewards-empty-title">No Reward Submissions Yet</h5>
                    <p class="rewards-empty-text">Start earning rewards by submitting patient details!</p>
                    <a href="{{ route('rewards.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Patient Details
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Navigation Bar (Mobile Only) -->
</div>

<!-- Mobile-First Styling -->
<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 140px !important;
    margin-top: 0;
}

@media (max-width: 767px) {
    .mobile-app-container {
        padding-bottom: 160px !important;
    }
}

/* App Content */
.app-content {
    position: relative;
    padding: 16px;
    padding-bottom: 20px;
}

@media (max-width: 767px) {
    .app-content {
        padding-bottom: 40px;
    }
}

/* App Header Mobile */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.app-user-avatar.rewards-avatar {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.app-user-info {
    display: flex;
    flex-direction: column;
}

.app-user-name {
    font-size: 0.95rem;
    font-weight: 700;
    color: #212529 !important;
    line-height: 1.2;
}

.app-user-id {
    font-size: 0.75rem;
    color: #495057 !important;
    line-height: 1.2;
    font-weight: 500;
}

.app-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    text-decoration: none;
    transition: all 0.2s ease;
}

.app-header-icon:hover,
.app-header-icon:active {
    background: #e9ecef;
    transform: scale(1.05);
}

/* Reward Summary Card */
.reward-summary-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s ease;
}

.reward-summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.reward-summary-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    flex-shrink: 0;
}

.reward-summary-content {
    flex: 1;
}

.reward-summary-label {
    font-size: 0.85rem;
    color: #6c757d !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.reward-summary-value {
    font-size: 2rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.25rem;
    line-height: 1;
}

.reward-summary-amount {
    font-size: 0.9rem;
    color: #28a745 !important;
    font-weight: 600;
}

/* Reward Action Card */
.reward-action-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

.reward-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.reward-action-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.reward-action-body {
    padding: 1.5rem;
}

.reward-action-text {
    color: #495057 !important;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.reward-action-text strong {
    color: #212529 !important;
    font-weight: 700;
}

/* Rewards List Section */
.rewards-list-section {
    margin-top: 1.5rem;
}

.rewards-list-header {
    margin-bottom: 1rem;
    padding: 0 0.5rem;
}

.rewards-list-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #212529 !important;
    margin: 0;
    display: flex;
    align-items: center;
}

.rewards-list-title i {
    color: #667eea !important;
}

/* Reward Entry Cards */
.rewards-list-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.reward-entry-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.reward-entry-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    border-color: #667eea;
}

.reward-entry-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.reward-entry-patient {
    flex: 1;
}

.reward-entry-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.25rem;
}

.reward-entry-meta {
    font-size: 0.85rem;
    color: #6c757d !important;
    display: flex;
    align-items: center;
}

.reward-entry-badge {
    text-align: right;
}

.reward-entry-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.reward-entry-detail-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: 0.9rem;
    color: #495057 !important;
}

.reward-entry-detail-item i {
    width: 20px;
    text-align: center;
    flex-shrink: 0;
    margin-top: 2px;
}

.reward-entry-footer {
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.reward-entry-date {
    font-size: 0.8rem;
    color: #6c757d !important;
    display: flex;
    align-items: center;
}

/* Empty State */
.rewards-empty-state {
    background: white;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.rewards-empty-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: white;
    box-shadow: 0 4px 20px rgba(255, 193, 7, 0.3);
}

.rewards-empty-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #212529 !important;
    margin-bottom: 0.75rem;
}

.rewards-empty-text {
    color: #6c757d !important;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

/* Pagination */
.rewards-pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Responsive */
@media (max-width: 576px) {
    .reward-summary-card {
        padding: 1.25rem;
    }
    
    .reward-summary-icon {
        width: 56px;
        height: 56px;
        font-size: 1.75rem;
    }
    
    .reward-summary-value {
        font-size: 1.75rem;
    }
    
    .reward-entry-card {
        padding: 1rem;
    }
    
    .rewards-empty-state {
        padding: 2rem 1rem;
    }
    
    .rewards-empty-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }
}
</style>
<script>
function sendOtp(rewardId) {
    fetch(`/rewards/${rewardId}/send-otp`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    }).then(r => r.json()).then(data => {
        alert(data.message || (data.success ? 'OTP sent.' : 'Failed to send OTP.'));
        if (data.success) location.reload();
    }).catch(() => alert('Failed to send OTP.'));
}
function verifyOtp(rewardId) {
    const otp = prompt('Enter 6-digit OTP:');
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
        alert(data.message || (data.success ? 'OTP verified.' : 'OTP verification failed.'));
        if (data.success) location.reload();
    }).catch(() => alert('Failed to verify OTP.'));
}
function changeRewardPatientPhone(rewardId, currentPhone) {
    const digits = (currentPhone || '').replace(/\D/g, '').slice(-10);
    const next = prompt('Enter correct 10-digit patient mobile:', digits);
    if (!next) return;
    const cleaned = next.replace(/\D/g, '');
    if (!/^[6-9][0-9]{9}$/.test(cleaned)) {
        alert('Enter a valid 10-digit Indian mobile number.');
        return;
    }
    fetch(`/rewards/${rewardId}/update-patient-phone`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ patient_phone: cleaned })
    }).then(r => r.json()).then(data => {
        alert(data.message || (data.success ? 'Mobile updated' : 'Update failed'));
        if (data.success) location.reload();
    }).catch(() => alert('Failed to update mobile'));
}
document.querySelectorAll('.btn-change-patient-phone').forEach(function (btn) {
    btn.addEventListener('click', function () {
        changeRewardPatientPhone(
            parseInt(btn.getAttribute('data-reward-id'), 10),
            btn.getAttribute('data-current-phone') || ''
        );
    });
});
</script>
@endsection

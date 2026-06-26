@extends('auth::layout')

@section('title', 'Patient Rewards - Staff Dashboard')
@section('page-title', 'Patient Rewards')

@section('head')
@include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container hc-mobile-shell" data-mmhc-ptr>
<!-- Mobile Header -->
<div class="app-mobile-header d-md-none">
    <div class="d-flex align-items-center">
        <a href="{{ route('staff.dashboard') }}" class="btn btn-link text-white p-0 me-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="text-white mb-0">Patient Rewards</h5>
    </div>
</div>

@include('services::partials.staff-referrals-assets')

<div class="container-fluid px-3 py-4">
    @if(isset($stats))
    <div class="hc-stat-chips hc-stat-chips--3 d-md-none mb-3">
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">{{ number_format($stats['total_points']) }}</span>
            <span class="hc-stat-chip__lbl">Points</span>
        </div>
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">₹{{ number_format($stats['payable_amount'], 0) }}</span>
            <span class="hc-stat-chip__lbl">Payable</span>
        </div>
        <div class="hc-stat-chip">
            <span class="hc-stat-chip__val">{{ $stats['total_submissions'] }}</span>
            <span class="hc-stat-chip__lbl">Entries</span>
        </div>
    </div>
    @endif

    @include('services::partials.staff-earnings-nav', ['activeTab' => 'rewards'])

    <div class="row mb-3">
        <div class="col-12 text-end">
            <a href="{{ route('staff.incentives.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i>View All Incentive Details
            </a>
        </div>
    </div>

    @include('services::staff.partials.verification-steps-explainer')

    <!-- Stats Banner -->
    <div class="row g-3 mb-4">
        @if(!$staffMobileVerified && (($stats['held_amount'] ?? 0) > 0 || ($stats['total_points'] ?? 0) > 0))
        <div class="col-12">
            <div class="alert alert-warning mb-0 py-2">
                <i class="fas fa-mobile-alt me-1"></i>
                @if(($stats['held_amount'] ?? 0) > 0)
                    ₹{{ number_format((float) $stats['held_amount'], 2) }} is earned but not payable until <strong>your Profile mobile</strong> is WhatsApp-verified (separate from patient WhatsApp OTP on the form).
                @else
                    Verify your account mobile in Profile to unlock reward payouts.
                @endif
            </div>
        </div>
        @endif
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
            <div class="stats-card-modern {{ (!$staffMobileVerified && ($stats['earned_amount'] ?? 0) > 0) ? 'bg-warning' : 'bg-success' }}">
                <div class="stats-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <div class="stats-value">₹{{ number_format($stats['payable_amount'], 2) }}</div>
                    <div class="stats-label">Payable Earnings</div>
                    @if(!$staffMobileVerified && ($stats['earned_amount'] ?? 0) > 0)
                        <div class="small text-warning mt-1">₹{{ number_format((float) $stats['earned_amount'], 2) }} earned · payout on hold</div>
                    @endif
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
                                        <i class="fas fa-phone me-1"></i>{{ $reward->display_patient_phone }}
                                        @if($reward->patient_age)
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-birthday-cake me-1"></i>Age: {{ $reward->patient_age }}
                                        @endif
                                    </div>
                                    @if($reward->patientUser?->unique_id)
                                        <div class="reward-entry-meta mt-1">
                                            <i class="fas fa-id-card me-1 text-success"></i>
                                            <strong>{{ $reward->patientUser->unique_id }}</strong>
                                            <span class="text-muted small">— patient can log in with this mobile</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="reward-entry-badge-modern">
                                    @php
                                        $rewardBlockers = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardBlockers($reward, $staffMobileVerified);
                                        $payoutStatus = \App\Modules\Payments\Services\StaffEarningStatusResolver::primaryStatus($rewardBlockers);
                                        $statusMessages = \App\Modules\Payments\Services\StaffEarningStatusResolver::detailMessagesForBlockers(
                                            $rewardBlockers,
                                            \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardMaskedPhone($reward)
                                        );
                                        $showRewardAmount = \App\Modules\Payments\Services\StaffEarningStatusResolver::patientRewardCountsForStaff($reward, $staffMobileVerified)
                                            || $reward->payment_processed;
                                    @endphp
                                    @if($showRewardAmount)
                                        <span class="badge-points">+{{ $reward->reward_points }} pts</span>
                                        <span class="badge-amount">₹{{ number_format($reward->reward_amount, 2) }}</span>
                                    @else
                                        <span class="badge-points badge-points--muted">0 pts</span>
                                        <span class="badge-amount text-muted">Not credited yet</span>
                                    @endif
                                    @include('services::staff.partials.payout-status-blockers', ['blockers' => $rewardBlockers, 'compact' => true])
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
                                @if(in_array(\App\Modules\Payments\Services\StaffEarningStatusResolver::PENDING_PATIENT_OTP, $rewardBlockers, true))
                                <div class="mt-3 p-3 rounded border bg-light reward-otp-panel" data-reward-id="{{ $reward->id }}">
                                    <div class="small fw-semibold mb-1 text-primary">Patient mobile OTP</div>
                                    <div class="small text-muted mb-2">Step 1: Resend OTP (or change number) → Step 2: Enter code from patient’s phone → Step 3: Verify</div>
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="sendRewardOtp({{ $reward->id }}, this)">
                                            Resend OTP to patient mobile
                                        </button>
                                        @if($reward->canChangePatientPhone())
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary btn-change-patient-phone"
                                                data-bs-toggle="modal"
                                                data-bs-target="#changePatientPhoneModal"
                                                data-reward-id="{{ $reward->id }}"
                                                data-patient-name="{{ $reward->patient_name }}"
                                                data-current-phone="{{ $reward->patient_phone_ten_digits }}">
                                            <i class="fas fa-edit me-1"></i>Change number
                                        </button>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 align-items-stretch">
                                        <input type="text"
                                               class="form-control form-control-sm reward-otp-input"
                                               id="reward-otp-{{ $reward->id }}"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               inputmode="numeric"
                                               placeholder="6-digit OTP from patient"
                                               style="max-width: 180px;">
                                        <button type="button" class="btn btn-sm btn-success" onclick="verifyRewardOtp({{ $reward->id }})">
                                            Verify patient OTP
                                        </button>
                                    </div>
                                    <div class="small mt-2 reward-otp-feedback text-muted" id="reward-otp-feedback-{{ $reward->id }}"></div>
                                </div>
                                @endif
                                @foreach($statusMessages as $statusMessage)
                                <div class="detail-row mt-2">
                                    <i class="fas fa-info-circle me-2 text-warning"></i>
                                    <span>{{ $statusMessage }}</span>
                                </div>
                                @endforeach
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
                    <div class="empty-state-icon empty-state-icon--rewards">
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

<div class="modal fade" id="changePatientPhoneModal" tabindex="-1" aria-labelledby="changePatientPhoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="changePatientPhoneModalLabel">
                    <i class="fas fa-mobile-alt me-2 text-primary"></i>Change patient mobile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3" id="changePatientPhoneModalHint">
                    Update the mobile for this patient. A new OTP will be sent to the new number.
                </p>
                <label for="changePatientPhoneInput" class="form-label fw-semibold small">New 10-digit mobile</label>
                <div class="input-group">
                    <span class="input-group-text">+91</span>
                    <input type="tel"
                           class="form-control"
                           id="changePatientPhoneInput"
                           maxlength="10"
                           pattern="[6-9][0-9]{9}"
                           inputmode="numeric"
                           placeholder="9876543210"
                           autocomplete="tel">
                </div>
                <div id="changePatientPhoneModalError" class="small text-danger mt-2 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="changePatientPhoneSubmitBtn">
                    <i class="fas fa-paper-plane me-1"></i>Update &amp; send OTP
                </button>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
function rewardOtpFeedback(rewardId, message, isError) {
    const el = document.getElementById('reward-otp-feedback-' + rewardId);
    if (!el) return;
    el.textContent = message || '';
    el.className = 'small mt-2 reward-otp-feedback ' + (isError ? 'text-danger' : 'text-success');
}
async function rewardApiPost(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body ? JSON.stringify(body) : '{}'
    });
    let data = {};
    try {
        data = await res.json();
    } catch (e) {
        throw new Error(res.status === 419 ? 'Session expired — refresh the page and try again.' : 'Server error (' + res.status + ').');
    }
    if (!res.ok) {
        if (!data.message && data.errors) {
            const first = Object.values(data.errors).flat()[0];
            if (first) data.message = first;
        }
        if (!data.message) {
            data.message = 'Request failed (' + res.status + ').';
        }
        data.success = false;
    }
    return data;
}
let changePhoneRewardId = null;

function getChangePatientPhoneModal() {
    const el = document.getElementById('changePatientPhoneModal');
    if (!el || typeof bootstrap === 'undefined') {
        return null;
    }
    return bootstrap.Modal.getOrCreateInstance(el);
}

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('changePatientPhoneModal');
    if (modalEl && modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn || !btn.classList.contains('btn-change-patient-phone')) {
                return;
            }
            changePhoneRewardId = parseInt(btn.getAttribute('data-reward-id'), 10);
            const current = btn.getAttribute('data-current-phone') || '';
            const name = btn.getAttribute('data-patient-name') || 'Patient';
            const input = document.getElementById('changePatientPhoneInput');
            const hint = document.getElementById('changePatientPhoneModalHint');
            const err = document.getElementById('changePatientPhoneModalError');
            if (input) {
                input.value = current;
            }
            if (hint) {
                hint.textContent = 'Update mobile for ' + name + '. A new OTP will be sent to the new number.';
            }
            if (err) {
                err.textContent = '';
                err.classList.add('d-none');
            }
            setTimeout(function () {
                input && input.focus();
            }, 200);
        });
    }

    document.getElementById('changePatientPhoneSubmitBtn')?.addEventListener('click', function () {
        if (!changePhoneRewardId) {
            return;
        }
        submitChangePatientPhone(changePhoneRewardId);
    });

    const changePhoneInputEl = document.getElementById('changePatientPhoneInput');
    changePhoneInputEl?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    });
    changePhoneInputEl?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (changePhoneRewardId) {
                submitChangePatientPhone(changePhoneRewardId);
            }
        }
    });
});
async function sendRewardOtp(rewardId, btn) {
    if (btn) btn.disabled = true;
    rewardOtpFeedback(rewardId, 'Sending OTP…', false);
    try {
        const data = await rewardApiPost('/rewards/' + rewardId + '/send-otp');
        if (data.success) {
            rewardOtpFeedback(rewardId, data.message || 'OTP sent.', false);
            if (data.dev_otp) {
                const input = document.getElementById('reward-otp-' + rewardId);
                if (input) input.value = data.dev_otp;
            }
        } else {
            rewardOtpFeedback(rewardId, data.message || 'Failed to send OTP.', true);
        }
    } catch (e) {
        rewardOtpFeedback(rewardId, e.message || 'Failed to send OTP.', true);
    } finally {
        if (btn) btn.disabled = false;
    }
}
async function verifyRewardOtp(rewardId) {
    const input = document.getElementById('reward-otp-' + rewardId);
    const otp = (input?.value || '').replace(/\D/g, '');
    if (otp.length !== 6) {
        rewardOtpFeedback(rewardId, 'Enter the 6-digit OTP from the patient’s WhatsApp.', true);
        return;
    }
    rewardOtpFeedback(rewardId, 'Verifying…', false);
    try {
        const data = await rewardApiPost('/rewards/' + rewardId + '/verify-otp', { otp_code: otp });
        if (data.success) {
            rewardOtpFeedback(rewardId, data.message || 'Verified!', false);
            setTimeout(() => location.reload(), 800);
        } else {
            rewardOtpFeedback(rewardId, data.message || 'Verification failed.', true);
        }
    } catch (e) {
        rewardOtpFeedback(rewardId, e.message || 'Failed to verify OTP.', true);
    }
}
async function submitChangePatientPhone(rewardId) {
    const input = document.getElementById('changePatientPhoneInput');
    const errEl = document.getElementById('changePatientPhoneModalError');
    const submitBtn = document.getElementById('changePatientPhoneSubmitBtn');
    const cleaned = (input?.value || '').replace(/\D/g, '');
    if (!/^[6-9][0-9]{9}$/.test(cleaned)) {
        if (errEl) {
            errEl.textContent = 'Enter a valid 10-digit Indian mobile (first digit 6–9).';
            errEl.classList.remove('d-none');
        }
        return;
    }
    if (errEl) errEl.classList.add('d-none');
    if (submitBtn) submitBtn.disabled = true;
    try {
        const data = await rewardApiPost('/rewards/' + rewardId + '/update-patient-phone', { patient_phone: cleaned });
        if (data.success) {
            getChangePatientPhoneModal()?.hide();
            if (data.dev_otp) {
                const otpInput = document.getElementById('reward-otp-' + rewardId);
                if (otpInput) otpInput.value = data.dev_otp;
            }
            setTimeout(function () { location.reload(); }, data.dev_otp ? 400 : 800);
        } else {
            if (errEl) {
                errEl.textContent = data.message || 'Update failed.';
                errEl.classList.remove('d-none');
            }
        }
    } catch (e) {
        if (errEl) {
            errEl.textContent = e.message || 'Failed to update mobile.';
            errEl.classList.remove('d-none');
        }
    } finally {
        if (submitBtn) submitBtn.disabled = false;
    }
}
</script>
@endsection


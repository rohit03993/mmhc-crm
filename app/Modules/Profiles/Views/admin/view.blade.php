@extends('auth::layout')

@section('title', $user->name.' — Profile')
@section('page-title', $user->name)

@section('head')
    @isset($studentAcademic)
        @include('academics::reports.partials.student-report-styles')
    @endisset
@endsection

@section('content')
@php
    $roleBadge = match ($user->role) {
        'admin' => 'danger',
        'nurse' => 'info',
        'caregiver' => 'primary',
        'patient' => 'success',
        default => 'secondary',
    };
@endphp

<div class="apv pb-5 mb-3">
    <div class="apv__toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <a href="{{ route('admin.users') }}" class="btn btn-light border rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>User management
            </a>
            <a href="{{ route('admin.profiles') }}" class="btn btn-link text-muted">All profiles</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="apv-card apv-card--hero text-center">
                <div class="apv-avatar mx-auto mb-3">
                    @if($profile && $profile->avatar_path)
                        <img src="{{ Storage::url($profile->avatar_path) }}" class="rounded-circle" width="112" height="112" alt="">
                    @else
                        <div class="apv-avatar-placeholder rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                </div>
                <h1 class="h4 fw-bold mb-1">{{ $user->name }}</h1>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                    <span class="badge rounded-pill bg-{{ $roleBadge }} px-3">{{ ucfirst($user->role) }}</span>
                    <span class="badge rounded-pill bg-light text-dark border px-3">{{ $user->unique_id }}</span>
                    <span class="badge rounded-pill {{ $user->is_active ? 'bg-success text-white' : 'bg-warning text-dark' }} px-3">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                @if($profile)
                    <div class="px-1">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Profile completion</span>
                            <span class="fw-semibold">{{ $profile->getCompletionPercentage() }}%</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-success rounded-pill" style="width: {{ $profile->getCompletionPercentage() }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            @if($user->hasAcademicRole())
                <div class="apv-card mt-4 text-start">
                    <h2 class="apv-card__title"><i class="fas fa-graduation-cap me-2 text-primary"></i>Academics</h2>
                    @if($user->role === 'student')
                        <p class="small text-muted mb-3">Documents, attendance, assignments, and SPI are on <strong>this page</strong> below. Use the button for a dedicated full-width report.</p>
                        <a href="{{ route('academics.reports.student', $user) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                            <i class="fas fa-external-link-alt me-1"></i>Open academics report page
                        </a>
                    @else
                        <p class="small text-muted mb-3">Use the button below for <strong>college-specific</strong> data.</p>
                    @endif
                    @if($user->role !== 'student')
                        <a href="{{ route('academics.people.show', $user) }}" class="btn btn-primary btn-sm w-100 rounded-pill">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Academic overview
                        </a>
                        <p class="small text-muted mt-2 mb-0">Batches, subjects taught, college context.</p>
                    @endif
                    @if($user->academic_institution_id)
                        <a href="{{ route('academics.institutions.show', $user->academic_institution_id) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill mt-2">
                            <i class="fas fa-university me-1"></i>College overview
                        </a>
                    @endif
                    @isset($academicAdminSummary)
                        <div class="mt-3 pt-3 border-top small">
                            <p class="fw-semibold mb-2 text-dark">College data (from academics seed)</p>
                            <ul class="list-unstyled mb-2 text-muted">
                                <li class="mb-1"><span class="text-dark fw-medium">Batches:</span> {{ $academicAdminSummary['batches']->isEmpty() ? '—' : $academicAdminSummary['batches']->pluck('name')->join(', ') }}</li>
                                <li class="mb-1"><span class="text-dark fw-medium">Subjects:</span>
                                    @if($academicAdminSummary['subjects']->isEmpty())
                                        —
                                    @else
                                        {{ $academicAdminSummary['subjects']->map(fn ($s) => $s->name.' ('.($s->batch->name ?? '—').')')->join('; ') }}
                                    @endif
                                </li>
                                <li class="mb-1"><span class="text-dark fw-medium">Assignments (topics in scope):</span> {{ $academicAdminSummary['assignments_count'] }}</li>
                                <li class="mb-0"><span class="text-dark fw-medium">Quizzes / exams (matched scope):</span> {{ $academicAdminSummary['exams_count'] }}</li>
                            </ul>
                            <a href="{{ route('academics.exams.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100 mb-1">Quizzes &amp; exams (admin)</a>
                            <p class="text-muted mb-0" style="font-size: 0.75rem;">Full batch &amp; subject lists: use <strong>Academic overview</strong> above.</p>
                        </div>
                    @endisset
                </div>
            @endif

            @if($user->isStaff() && $profileStats['staff'])
                @php $s = $profileStats['staff']; @endphp
                <div class="apv-card mt-4">
                    <h2 class="apv-card__title"><i class="fas fa-chart-pie me-2 text-success"></i>Quick stats</h2>
                    <p class="small text-muted mb-2">Healthcare CRM (nurse / caregiver field work) — not college coursework.</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Service requests</span>
                                <span class="apv-stat__val">{{ $s['services_total'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Completed</span>
                                <span class="apv-stat__val">{{ $s['services_completed'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Approved pay</span>
                                <span class="apv-stat__val">{{ $s['services_approved'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Staff referrals</span>
                                <span class="apv-stat__val">{{ $s['referrals_completed'] }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="apv-stat apv-stat--accent">
                                <span class="apv-stat__label">Total earnings</span>
                                <span class="apv-stat__val text-success">₹{{ number_format($s['combined_earnings'], 2) }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted pt-1">
                                <div class="d-flex justify-content-between py-1 border-bottom border-opacity-25"><span>Services</span><span class="text-dark fw-semibold">₹{{ number_format($s['incentive_service'], 2) }}</span></div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-opacity-25"><span>Subscriptions</span><span class="text-dark fw-semibold">₹{{ number_format($s['incentive_subscription'], 2) }}</span></div>
                                <div class="d-flex justify-content-between py-1 border-bottom border-opacity-25"><span>Staff referrals</span><span class="text-dark fw-semibold">₹{{ number_format($s['incentive_referral'], 2) }}</span></div>
                                <div class="d-flex justify-content-between py-1"><span>Patient rewards</span><span class="text-dark fw-semibold">₹{{ number_format($s['patient_rewards_total'], 2) }}</span></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Unsettled (ledger)</span>
                                <span class="apv-stat__val">₹{{ number_format($s['incentive_unsettled'], 2) }}</span>
                            </div>
                        </div>
                        @if(!empty($staffPaymentPending) && round((float) ($staffPaymentPending['total'] ?? 0), 2) >= 0.01)
                        <div class="col-12">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Pending payout</span>
                                <span class="apv-stat__val text-primary fw-bold">₹{{ number_format((float) $staffPaymentPending['total'], 2) }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    <hr class="my-3 opacity-25">
                    <div class="small text-muted">
                        <div class="d-flex justify-content-between py-1"><span>Subscription sales (count)</span><span class="text-dark fw-semibold">{{ $s['subscription_sales_count'] }}</span></div>
                    </div>
                    @if(!empty($staffPaymentPending) && round((float) ($staffPaymentPending['total'] ?? 0), 2) >= 0.01)
                        @php $pp = $staffPaymentPending; @endphp
                        <div class="apv-payblock mt-3 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <span class="apv-payblock__title"><i class="fas fa-money-check-alt me-1 text-primary"></i>Record payout</span>
                                <a href="{{ route('admin.payments.index') }}" class="small text-decoration-none text-muted">All staff payments →</a>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if(round((float) ($pp['service_request']['amount'] ?? 0), 2) >= 0.01)
                                    <a href="{{ route('admin.payments.form', ['staff' => $user->id, 'type' => 'service_request']) }}" class="btn btn-sm btn-outline-primary rounded-pill">Services <span class="fw-semibold">₹{{ number_format((float) $pp['service_request']['amount'], 2) }}</span></a>
                                @endif
                                @if(round((float) ($pp['patient_reward']['amount'] ?? 0), 2) >= 0.01)
                                    <a href="{{ route('admin.payments.form', ['staff' => $user->id, 'type' => 'patient_reward']) }}" class="btn btn-sm btn-outline-warning rounded-pill">Patient rewards <span class="fw-semibold">₹{{ number_format((float) $pp['patient_reward']['amount'], 2) }}</span></a>
                                @endif
                                @if(round((float) ($pp['subscription_referral']['amount'] ?? 0), 2) >= 0.01)
                                    <a href="{{ route('admin.payments.form', ['staff' => $user->id, 'type' => 'subscription_referral']) }}" class="btn btn-sm btn-outline-success rounded-pill">Subscription ref. <span class="fw-semibold">₹{{ number_format((float) $pp['subscription_referral']['amount'], 2) }}</span></a>
                                @endif
                                @if(round((float) ($pp['staff_referral']['amount'] ?? 0), 2) >= 0.01)
                                    <a href="{{ route('admin.payments.form', ['staff' => $user->id, 'type' => 'staff_referral']) }}" class="btn btn-sm btn-outline-info rounded-pill">Staff referrals <span class="fw-semibold">₹{{ number_format((float) $pp['staff_referral']['amount'], 2) }}</span></a>
                                @endif
                            </div>
                            <p class="small text-muted mb-0 mt-2">Opens the payment form for this staff member; use <strong>Payment category</strong> there to switch type and reload amounts.</p>
                        </div>
                    @endif
                </div>
            @endif

            @if($user->isPatient() && $profileStats['patient'])
                @php $p = $profileStats['patient']; @endphp
                <div class="apv-card mt-4">
                    <h2 class="apv-card__title"><i class="fas fa-heartbeat me-2 text-danger"></i>Quick stats</h2>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Service requests</span>
                                <span class="apv-stat__val">{{ $p['services_total'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Open / in progress</span>
                                <span class="apv-stat__val">{{ $p['services_open'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Completed</span>
                                <span class="apv-stat__val">{{ $p['services_completed'] }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="apv-stat">
                                <span class="apv-stat__label">Subscriptions</span>
                                <span class="apv-stat__val">{{ $p['subscriptions_total'] }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="apv-stat apv-stat--accent">
                                <span class="apv-stat__label">Active subscription</span>
                                <span class="apv-stat__val">{{ $p['has_active_subscription'] ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8 apv-main-rail">
            <div class="apv-card mb-4">
                <h2 class="apv-card__title"><i class="fas fa-id-card me-2 text-primary"></i>Account details</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="apv-k">Phone</span>
                        <p class="apv-v mb-0">{{ $user->phone ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="apv-k">Date of birth</span>
                        <p class="apv-v mb-0">{{ $user->getFormattedDateOfBirth() }}</p>
                    </div>
                    <div class="col-12">
                        <span class="apv-k">Address</span>
                        <p class="apv-v mb-0">{{ $user->address ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="apv-k">Pincode</span>
                        <p class="apv-v mb-0">{{ $user->pincode ?? '—' }}</p>
                    </div>
                    @if($user->qualification || $user->experience)
                    <div class="col-md-6">
                        <span class="apv-k">Qualification / experience</span>
                        <p class="apv-v mb-0">{{ $user->qualification ?? '—' }} @if($user->experience)<span class="text-muted">·</span> {{ $user->experience }}@endif</p>
                    </div>
                    @endif
                </div>
            </div>

            @isset($studentAcademic)
                <div class="mb-4">
                    <h2 class="h6 text-uppercase text-muted fw-semibold mb-3" style="letter-spacing: .06em;">Student record</h2>
                    @include('academics::reports.partials.student-report-body', $studentAcademic)
                </div>
            @endisset

            @if($profile)
                @if($user->role === 'caregiver' || ($profile->bio))
                <div class="apv-card mb-4">
                    <h2 class="apv-card__title"><i class="fas fa-briefcase me-2 text-info"></i>Professional</h2>
                    @if($user->role === 'caregiver')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="apv-k">Experience (profile)</span>
                            <p class="apv-v mb-0">{{ $profile->experience_years !== null ? $profile->experience_years.' years' : '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="apv-k">Specialization</span>
                            <p class="apv-v mb-0">{{ $profile->specialization ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="apv-k">Availability</span>
                            <p class="mb-0">
                                <span class="badge rounded-pill
                                    @if($profile->availability_status === 'available') bg-success
                                    @elseif($profile->availability_status === 'busy') bg-warning text-dark
                                    @else bg-secondary @endif">
                                    {{ ucfirst($profile->availability_status ?? '—') }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @endif
                    @if($profile->bio)
                        <div class="mt-3 pt-3 border-top">
                            <span class="apv-k">Bio</span>
                            <p class="apv-v mb-0 text-muted">{{ $profile->bio }}</p>
                        </div>
                    @endif
                </div>
                @endif
            @else
                <div class="alert alert-warning rounded-4 border-0 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>No extended profile record yet.
                </div>
            @endif

            @if($user->role !== 'student' && $profileDocumentsPaginator)
            <div class="apv-card mb-4">
                <h2 class="apv-card__title d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-folder-open me-2 text-warning"></i>Documents</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ $profileDocumentsPaginator->total() }}</span>
                </h2>
                @if($profileDocumentsPaginator->total() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 apv-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profileDocumentsPaginator as $document)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="apv-doc-icon"><i class="{{ $document->file_icon }}"></i></span>
                                                <div>
                                                    <div class="fw-semibold">{{ $document->document_name }}</div>
                                                    <small class="text-muted">{{ $document->original_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge rounded-pill bg-secondary text-white">{{ $document->document_type_display }}</span></td>
                                        <td>
                                            <span class="badge rounded-pill
                                                @if($document->status == 'verified') bg-success
                                                @elseif($document->status == 'rejected') bg-danger
                                                @else bg-warning text-dark @endif">
                                                {{ $document->status_display }}
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('documents.view', $document->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1" target="_blank" rel="noopener">View</a>
                                            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-1">
                        {{ $profileDocumentsPaginator->links('pagination.modern') }}
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-file-alt fa-2x mb-3 opacity-50"></i>
                        <p class="mb-0">No documents uploaded.</p>
                    </div>
                @endif
            </div>
            @endif

            @if($incentiveDetailsData)
                <div class="apv-card mb-4 apv-card--incentive apv-incentive-wrap">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="apv-card__title mb-1"><i class="fas fa-chart-line me-2 text-success"></i>Incentive details</h2>
                            <p class="small text-muted mb-0">Visit-wise services, subscriptions, referrals, and patient rewards for this staff member.</p>
                        </div>
                        <a href="{{ route('admin.staff.incentives', $user) }}" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm">Open full page</a>
                    </div>
                    @include('services::staff.partials.incentive-details-inner', array_merge($incentiveDetailsData, [
                        'tabIdPrefix' => 'profinv',
                        'incentiveEmbed' => true,
                    ]))
                </div>
            @endif

            @if($user->isStaff())
                <div class="apv-card mb-4">
                    <h2 class="apv-card__title d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span><i class="fas fa-wallet me-2 text-primary"></i>Past staff payouts</span>
                        <span class="badge bg-light text-dark border rounded-pill">{{ $staffPaymentHistory->count() }}</span>
                    </h2>
                    @if($staffPaymentHistory->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 apv-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffPaymentHistory as $payment)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ optional($payment->paid_at)->format('d M Y, h:i A') ?? '—' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill bg-secondary text-white">
                                                    {{ ucwords(str_replace('_', ' ', (string) $payment->payment_type)) }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold">₹{{ number_format((float) $payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge rounded-pill {{ $payment->payment_mode === 'razorpayx' ? 'bg-info text-dark' : 'bg-light text-dark border' }}">
                                                    {{ strtoupper((string) ($payment->payment_mode ?: 'manual')) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill {{ ($payment->gateway_status === 'processed' || $payment->gateway_status === 'captured') ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ ucfirst((string) ($payment->gateway_status ?: 'recorded')) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $payment->gateway_reference_id ?: $payment->transaction_id ?: '—' }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="small text-muted mt-2 mb-0">Shows the latest 20 payouts for this staff profile, including RazorpayX and manual records.</p>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">No payouts recorded for this staff member yet.</p>
                        </div>
                    @endif
                </div>
            @endif

            <details class="apv-card apv-card--cred apv-cred-details">
                <summary class="apv-cred-summary">
                    <span class="d-flex align-items-center gap-2"><i class="fas fa-key text-secondary"></i><strong>Admin</strong> — login credentials</span>
                    <span class="apv-cred-hint small text-muted d-inline-flex align-items-center gap-1">Tap to expand <i class="fas fa-chevron-down apv-cred-chevron small"></i></span>
                </summary>
                <div class="apv-cred-body pt-3 mt-2 border-top">
                    <p class="small text-muted mb-2">For admin use only; store and share passwords securely.</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control font-monospace" id="apvPlainPassword" readonly value="{{ $user->decrypted_password ?? 'Not available' }}">
                        <button class="btn btn-outline-secondary" type="button" onclick="apvCopyPassword()"><i class="fas fa-copy"></i></button>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm mt-2 rounded-pill" onclick="apvResetPassword({{ $user->id }})">
                        <i class="fas fa-sync-alt me-1"></i>Generate new password
                    </button>
                </div>
            </details>
        </div>
    </div>
</div>

<style>
.apv { max-width: 1200px; margin: 0 auto; }
.apv-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}
.apv-card--hero { padding-top: 1.75rem; padding-bottom: 1.75rem; }
.apv-main-rail { padding-bottom: 2.5rem; }
.apv-card--incentive {
    background: linear-gradient(145deg, #f8fafc 0%, #ffffff 45%, #f1f5f9 100%);
    border-color: #94a3b8;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
    border-left: 4px solid #0d9488;
}
.apv-incentive-wrap { position: relative; }
.apv-card--cred {
    background: #fafafa;
    border-color: #e2e8f0;
    border-style: solid;
    margin-bottom: 2.75rem;
    margin-top: 0.25rem;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.apv-cred-details[open] { margin-bottom: 3.25rem; }
.apv-cred-summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
    color: #475569;
    font-size: 0.9rem;
    padding: 0.35rem 0.15rem 0.5rem;
}
.apv-cred-summary::-webkit-details-marker { display: none; }
.apv-cred-chevron { transition: transform 0.2s ease; }
.apv-cred-details[open] .apv-cred-chevron { transform: rotate(180deg); }
.apv-cred-details[open] .apv-cred-hint { opacity: 0.75; }
.apv-cred-body { animation: apv-fade 0.2s ease; }
@keyframes apv-fade { from { opacity: 0.6; } to { opacity: 1; } }
.apv-card__title { font-size: 1.05rem; font-weight: 700; margin: 0 0 1rem; color: #0f172a; letter-spacing: -0.02em; }
.apv-avatar-placeholder {
    width: 112px; height: 112px;
    background: linear-gradient(135deg, #0d9488, #2563eb);
}
.apv-k { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b; }
.apv-v { font-weight: 600; color: #0f172a; margin-top: 0.2rem; }
.apv-stat {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.65rem 0.85rem;
    height: 100%;
}
.apv-stat--accent { background: #ecfdf5; border-color: #a7f3d0; }
.apv-stat__label { display: block; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; }
.apv-stat__val { display: block; font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem; }
.apv-doc-icon {
    width: 2.25rem; height: 2.25rem; border-radius: 0.5rem;
    background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; color: #475569;
}
.apv-table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; border-bottom-width: 1px; }
.apv-payblock__title { font-size: 0.72rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #475569; }
</style>

<script>
function apvCopyPassword() {
    const el = document.getElementById('apvPlainPassword');
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value).then(function () {
        alert('Copied to clipboard.');
    }, function () {
        document.execCommand('copy');
        alert('Copied to clipboard.');
    });
}
function apvResetPassword(userId) {
    if (!confirm('Generate a new random password for this user?')) return;
    fetch('{{ url('/admin/users') }}/' + userId + '/reset-password', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                document.getElementById('apvPlainPassword').value = data.new_password;
                alert('New password: ' + data.new_password);
            } else {
                alert('Failed to reset password.');
            }
        })
        .catch(function () { alert('Request failed.'); });
}
</script>
@endsection

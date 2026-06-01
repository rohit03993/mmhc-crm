@extends('auth::layout')

@section('title', $title . ' — Payout detail')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="fas fa-arrow-left me-1"></i>Back to dashboard
        </a>
        <h2 class="h4 mb-1 mt-2">{{ $title }}</h2>
        <p class="text-muted mb-2">{{ $subtitle }}</p>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            @if($status === 'all')
                <span class="badge bg-danger fs-6">Combined: ₹{{ number_format($displayTotal, 2) }}</span>
                <span class="badge bg-light text-dark border">Paid: ₹{{ number_format($paidTotal, 2) }}</span>
                <span class="badge bg-warning text-dark">Pending: ₹{{ number_format($pendingTotal, 2) }}</span>
            @elseif($status === 'paid')
                <span class="badge bg-danger fs-6">Paid: ₹{{ number_format($paidTotal, 2) }}</span>
            @else
                <span class="badge bg-warning text-dark fs-6">Pending: ₹{{ number_format($pendingTotal, 2) }}</span>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.financial.payout-detail', ['type' => $type, 'status' => 'all', 'period' => $period]) }}"
                   class="btn {{ $status === 'all' ? 'btn-danger' : 'btn-outline-danger' }}">All</a>
                <a href="{{ route('admin.financial.payout-detail', ['type' => $type, 'status' => 'paid', 'period' => $period]) }}"
                   class="btn {{ $status === 'paid' ? 'btn-danger' : 'btn-outline-danger' }}">Paid only</a>
                <a href="{{ route('admin.financial.payout-detail', ['type' => $type, 'status' => 'pending', 'period' => $period]) }}"
                   class="btn {{ $status === 'pending' ? 'btn-danger' : 'btn-outline-danger' }}">Pending only</a>
            </div>
            @if($status !== 'pending')
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('admin.financial.payout-detail', ['type' => $type, 'status' => $status, 'period' => 'all']) }}"
                       class="btn {{ $period === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All time</a>
                    <a href="{{ route('admin.financial.payout-detail', ['type' => $type, 'status' => $status, 'period' => 'month']) }}"
                       class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">This month</a>
                </div>
            @endif
            <a href="{{ route('admin.payments.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-wallet me-1"></i>Process payouts
            </a>
        </div>
    </div>

    @if($status === 'all' || $status === 'paid')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-check-circle text-danger me-1"></i> Already paid
                <span class="text-muted fw-normal">— ₹{{ number_format($paidTotal, 2) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Staff</th>
                                <th class="text-end">Amount</th>
                                <th>Transaction</th>
                                <th>Recorded by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paidPaginator ?? [] as $payment)
                                <tr>
                                    <td>{{ optional($payment->paid_at)->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($payment->staff)
                                            <a href="{{ route('admin.profiles.view', $payment->staff) }}" class="fw-semibold text-decoration-none">{{ $payment->staff->name }}</a>
                                            <div class="small text-muted">{{ ucfirst($payment->staff->role) }} · {{ $payment->staff->unique_id ?? '' }}</div>
                                        @else — @endif
                                    </td>
                                    <td class="text-end fw-semibold text-danger">₹{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td class="small">{{ $payment->transaction_id ?: ($payment->gateway_reference_id ?? '—') }}</td>
                                    <td class="small text-muted">{{ $payment->admin->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No recorded payouts for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($paidPaginator && $paidPaginator->hasPages())
                <div class="card-footer bg-white">{{ $paidPaginator->links() }}</div>
            @endif
        </div>
    @endif

    @if($status === 'all' || $status === 'pending')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="fas fa-clock text-warning me-1"></i> Pending (payable now)
                <span class="text-muted fw-normal">— ₹{{ number_format($pendingTotal, 2) }}</span>
            </div>
            <div class="card-body p-0">
                @if($type === 'service_request' && $pendingServicePaginator)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Approved</th>
                                    <th>Staff</th>
                                    <th>Patient</th>
                                    <th>Service</th>
                                    <th class="text-end">Payable</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingServicePaginator as $visit)
                                    <tr>
                                        <td>{{ optional($visit->admin_approved_at)->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            @if($visit->assignedStaff)
                                                <a href="{{ route('admin.profiles.view', $visit->assignedStaff) }}" class="text-decoration-none fw-semibold">{{ $visit->assignedStaff->name }}</a>
                                            @else — @endif
                                        </td>
                                        <td>
                                            @if($visit->patient)
                                                <a href="{{ route('admin.profiles.view', $visit->patient) }}" class="text-decoration-none">{{ $visit->patient->name }}</a>
                                            @else — @endif
                                        </td>
                                        <td>{{ $visit->serviceType->name ?? 'Service' }}</td>
                                        <td class="text-end fw-semibold text-warning">₹{{ number_format((float) $visit->total_staff_payout, 2) }}</td>
                                        <td class="text-end">
                                            @if($visit->assignedStaff)
                                                <a href="{{ route('admin.payments.form', ['staff' => $visit->assignedStaff->id, 'type' => 'service_request']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Nothing pending in this category.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($pendingServicePaginator->hasPages())
                        <div class="card-footer bg-white">{{ $pendingServicePaginator->links() }}</div>
                    @endif
                @elseif($type === 'patient_reward' && $pendingRewardPaginator)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Verified</th>
                                    <th>Staff</th>
                                    <th>Patient</th>
                                    <th class="text-end">Payable</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingRewardPaginator as $reward)
                                    <tr>
                                        <td>{{ optional($reward->verified_at)->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            @if($reward->user)
                                                <a href="{{ route('admin.profiles.view', $reward->user) }}" class="fw-semibold text-decoration-none">{{ $reward->user->name }}</a>
                                            @else — @endif
                                        </td>
                                        <td>{{ $reward->patient_name ?? '—' }}</td>
                                        <td class="text-end fw-semibold text-warning">₹{{ number_format((float) $reward->reward_amount, 2) }}</td>
                                        <td class="text-end">
                                            @if($reward->user)
                                                <a href="{{ route('admin.payments.form', ['staff' => $reward->user->id, 'type' => 'patient_reward']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Nothing pending in this category.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($pendingRewardPaginator->hasPages())
                        <div class="card-footer bg-white">{{ $pendingRewardPaginator->links() }}</div>
                    @endif
                @elseif($type === 'staff_referral')
                    @if($pendingLedgerPaginator && $pendingLedgerPaginator->total() > 0)
                        <p class="small text-muted px-3 pt-3 mb-0">Incentive ledger</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Source ID</th>
                                        <th class="text-end">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingLedgerPaginator as $row)
                                        <tr>
                                            <td>
                                                @if($row->staff)
                                                    <a href="{{ route('admin.profiles.view', $row->staff) }}" class="text-decoration-none">{{ $row->staff->name }}</a>
                                                @else — @endif
                                            </td>
                                            <td class="small text-muted">Referral #{{ $row->source_id }}</td>
                                            <td class="text-end fw-semibold">₹{{ number_format((float) $row->final_amount, 2) }}</td>
                                            <td class="text-end">
                                                @if($row->staff)
                                                    <a href="{{ route('admin.payments.form', ['staff' => $row->staff->id, 'type' => 'staff_referral']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingLedgerPaginator->hasPages())
                            <div class="px-3 pb-2">{{ $pendingLedgerPaginator->links() }}</div>
                        @endif
                    @endif
                    @if($pendingLegacyReferralPaginator && $pendingLegacyReferralPaginator->total() > 0)
                        <p class="small text-muted px-3 pt-3 mb-0">Legacy referrals (pre-ledger)</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Completed</th>
                                        <th>Referrer</th>
                                        <th>Referred</th>
                                        <th class="text-end">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingLegacyReferralPaginator as $ref)
                                        <tr>
                                            <td>{{ optional($ref->completed_at)->format('d M Y') ?? '—' }}</td>
                                            <td>
                                                @if($ref->referrer)
                                                    <a href="{{ route('admin.profiles.view', $ref->referrer) }}" class="text-decoration-none">{{ $ref->referrer->name }}</a>
                                                @else — @endif
                                            </td>
                                            <td>{{ $ref->referred->name ?? '—' }}</td>
                                            <td class="text-end fw-semibold">₹{{ number_format((float) $ref->reward_amount, 2) }}</td>
                                            <td class="text-end">
                                                @if($ref->referrer)
                                                    <a href="{{ route('admin.payments.form', ['staff' => $ref->referrer->id, 'type' => 'staff_referral']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingLegacyReferralPaginator->hasPages())
                            <div class="px-3 pb-2">{{ $pendingLegacyReferralPaginator->links() }}</div>
                        @endif
                    @endif
                    @if(($pendingLedgerPaginator?->total() ?? 0) + ($pendingLegacyReferralPaginator?->total() ?? 0) === 0)
                        <p class="text-center text-muted py-4 mb-0">Nothing pending in this category.</p>
                    @endif
                @elseif($type === 'subscription_referral')
                    @if($pendingLedgerPaginator && $pendingLedgerPaginator->total() > 0)
                        <p class="small text-muted px-3 pt-3 mb-0">Incentive ledger</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Subscription</th>
                                        <th class="text-end">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingLedgerPaginator as $row)
                                        <tr>
                                            <td>
                                                @if($row->staff)
                                                    <a href="{{ route('admin.profiles.view', $row->staff) }}" class="text-decoration-none">{{ $row->staff->name }}</a>
                                                @else — @endif
                                            </td>
                                            <td class="small">
                                                {{ $row->sourceSubscription->plan->name ?? 'Plan' }}
                                                @if($row->sourceSubscription?->user)
                                                    · {{ $row->sourceSubscription->user->name }}
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold">₹{{ number_format((float) $row->final_amount, 2) }}</td>
                                            <td class="text-end">
                                                @if($row->staff)
                                                    <a href="{{ route('admin.payments.form', ['staff' => $row->staff->id, 'type' => 'subscription_referral']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingLedgerPaginator->hasPages())
                            <div class="px-3 pb-2">{{ $pendingLedgerPaginator->links() }}</div>
                        @endif
                    @endif
                    @if($pendingLegacySubscriptionPaginator && $pendingLegacySubscriptionPaginator->total() > 0)
                        <p class="small text-muted px-3 pt-3 mb-0">Legacy subscription referrals</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff (referrer)</th>
                                        <th>Subscriber</th>
                                        <th>Plan</th>
                                        <th class="text-end">Commission</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingLegacySubscriptionPaginator as $sub)
                                        <tr>
                                            <td>
                                                @if($sub->referrer)
                                                    <a href="{{ route('admin.profiles.view', $sub->referrer) }}" class="text-decoration-none">{{ $sub->referrer->name }}</a>
                                                @else — @endif
                                            </td>
                                            <td>{{ $sub->user->name ?? '—' }}</td>
                                            <td>{{ $sub->plan->name ?? '—' }}</td>
                                            <td class="text-end fw-semibold">₹{{ number_format((float) $sub->referral_commission_amount, 2) }}</td>
                                            <td class="text-end">
                                                @if($sub->referrer)
                                                    <a href="{{ route('admin.payments.form', ['staff' => $sub->referrer->id, 'type' => 'subscription_referral']) }}" class="btn btn-sm btn-danger rounded-pill">Pay</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingLegacySubscriptionPaginator->hasPages())
                            <div class="px-3 pb-2">{{ $pendingLegacySubscriptionPaginator->links() }}</div>
                        @endif
                    @endif
                    @if(($pendingLedgerPaginator?->total() ?? 0) + ($pendingLegacySubscriptionPaginator?->total() ?? 0) === 0)
                        <p class="text-center text-muted py-4 mb-0">Nothing pending in this category.</p>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <p class="small text-muted mt-3 mb-0">
        Pending amounts require staff <strong>mobile verified</strong> (SMS OTP). Use <strong>Pay</strong> to open the payout form, or
        <a href="{{ route('admin.payments.index', ['type' => $type]) }}">Staff payments</a> from the sidebar.
    </p>
</div>
@endsection

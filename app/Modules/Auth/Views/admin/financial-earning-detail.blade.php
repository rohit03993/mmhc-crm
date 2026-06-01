@extends('auth::layout')

@section('title', $title)

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="fas fa-arrow-left me-1"></i>Back to dashboard
        </a>
        <h2 class="h4 mb-1 mt-2">{{ $title }}</h2>
        <p class="text-muted mb-2">{{ $subtitle }}</p>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-success fs-6">Total: ₹{{ number_format($totalAmount, 2) }}</span>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.financial.earning-detail', ['type' => $type, 'period' => 'all']) }}"
                   class="btn {{ $period === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All time</a>
                <a href="{{ route('admin.financial.earning-detail', ['type' => $type, 'period' => 'month']) }}"
                   class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">This month</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @if(in_array($type, ['student-subscriptions', 'patient-subscriptions'], true))
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Role</th>
                                <th>Plan</th>
                                <th class="text-end">Paid</th>
                                <th>Invoice</th>
                            @elseif($type === 'services')
                                <th>Date</th>
                                <th>Patient (paid)</th>
                                <th>Assigned staff</th>
                                <th>Service</th>
                                <th class="text-end">Collected</th>
                                <th class="text-end">Visit total</th>
                                <th></th>
                            @else
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Staff</th>
                                <th>Service</th>
                                <th class="text-end">Due</th>
                                <th class="text-end">Prepaid</th>
                                <th></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginator as $row)
                            @if(in_array($type, ['student-subscriptions', 'patient-subscriptions'], true))
                                <tr>
                                    <td>{{ optional($row->paid_at)->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($row->user)
                                            <a href="{{ route('admin.profiles.view', $row->user) }}" class="fw-semibold text-decoration-none">{{ $row->user->name }}</a>
                                            <div class="small text-muted">{{ $row->user->email }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($row->user->role ?? '—') }}</span></td>
                                    <td>{{ $row->subscription->plan->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold text-success">₹{{ number_format((float) $row->amount, 2) }}</td>
                                    <td>
                                        @if($row->subscription)
                                            <a href="{{ route('subscriptions.invoice', $row->subscription) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener">Invoice</a>
                                        @endif
                                    </td>
                                </tr>
                            @elseif($type === 'services')
                                @php
                                    $balance = max(0, (float) $row->total_amount - (float) $row->prepaid_amount);
                                @endphp
                                <tr>
                                    <td>{{ $row->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($row->patient)
                                            <a href="{{ route('admin.profiles.view', $row->patient) }}" class="fw-semibold text-decoration-none">{{ $row->patient->name }}</a>
                                            <div class="small text-muted">{{ ucfirst($row->patient->role) }} · {{ $row->patient->unique_id ?? '' }}</div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->assignedStaff)
                                            <a href="{{ route('admin.profiles.view', $row->assignedStaff) }}" class="text-decoration-none">{{ $row->assignedStaff->name }}</a>
                                            <div class="small text-muted">{{ $row->assignedStaff->isNurse() ? 'Nurse' : 'Caregiver' }}</div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->serviceType->name ?? 'Service' }}</td>
                                    <td class="text-end fw-semibold text-success">₹{{ number_format((float) $row->prepaid_amount, 2) }}</td>
                                    <td class="text-end text-muted">₹{{ number_format((float) $row->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.service-requests') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Request</a>
                                    </td>
                                </tr>
                            @else
                                @php $due = max(0, (float) $row->total_amount - (float) $row->prepaid_amount); @endphp
                                <tr>
                                    <td>{{ $row->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($row->patient)
                                            <a href="{{ route('admin.profiles.view', $row->patient) }}" class="fw-semibold text-decoration-none">{{ $row->patient->name }}</a>
                                        @else — @endif
                                    </td>
                                    <td>
                                        @if($row->assignedStaff)
                                            <a href="{{ route('admin.profiles.view', $row->assignedStaff) }}" class="text-decoration-none">{{ $row->assignedStaff->name }}</a>
                                        @else — @endif
                                    </td>
                                    <td>{{ $row->serviceType->name ?? '—' }}</td>
                                    <td class="text-end fw-semibold text-warning">₹{{ number_format($due, 2) }}</td>
                                    <td class="text-end text-muted">₹{{ number_format((float) $row->prepaid_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">No records for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($paginator->hasPages())
            <div class="card-footer bg-white">{{ $paginator->links() }}</div>
        @endif
    </div>

    <p class="small text-muted mt-3 mb-0">
        @if($type === 'services')
            Only visits with <strong>prepaid_amount &gt; 0</strong> are counted in dashboard “Service requests” earning. Nurses and caregivers are shown as assigned staff; payment to them is under <strong>Payout</strong> on the dashboard.
        @elseif($type === 'services-due')
            These balances are listed under <strong>Expected → Service balances due</strong> on the dashboard.
        @else
            Subscription payments match <strong>Customer Payments</strong> and paid <code>subscriptions</code> rows.
        @endif
    </p>
</div>
@endsection

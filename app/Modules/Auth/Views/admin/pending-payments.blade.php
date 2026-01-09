@extends('auth::layout')

@section('title', 'Pending Payments - Admin Dashboard')

@section('head')
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-clock me-2 text-warning"></i>Pending Payments
            </h2>
            <p class="text-muted mb-0">Money owed TO COMPANY by patients/customers</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-warning">₹{{ number_format($totalPending, 2) }}</div>
                    <div class="small text-muted">Total Pending</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-primary">₹{{ number_format($totalPendingSubscriptions, 2) }}</div>
                    <div class="small text-muted">Subscriptions ({{ $totalSubscriptionsCount ?? 0 }})</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-info">₹{{ number_format($totalPendingServices, 2) }}</div>
                    <div class="small text-muted">Services ({{ $totalServicesCount ?? 0 }})</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="h3 mb-0 text-success">{{ ($totalSubscriptionsCount ?? 0) + ($totalServicesCount ?? 0) }}</div>
                    <div class="small text-muted">Total Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $filterType === 'all' ? 'active' : '' }}" 
               href="{{ route('admin.pending-payments', ['type' => 'all']) }}">
                <i class="fas fa-list me-1"></i>All Pending ({{ ($totalSubscriptionsCount ?? 0) + ($totalServicesCount ?? 0) }})
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $filterType === 'subscriptions' ? 'active' : '' }}" 
               href="{{ route('admin.pending-payments', ['type' => 'subscriptions']) }}">
                <i class="fas fa-crown me-1"></i>Subscriptions ({{ $totalSubscriptionsCount ?? 0 }})
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $filterType === 'services' ? 'active' : '' }}" 
               href="{{ route('admin.pending-payments', ['type' => 'services']) }}">
                <i class="fas fa-clipboard-list me-1"></i>Services ({{ $totalServicesCount ?? 0 }})
            </a>
        </li>
    </ul>

    <!-- Pending Subscriptions -->
    @if($filterType === 'all' || $filterType === 'subscriptions')
    @if($pendingSubscriptions->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-crown me-2"></i>Pending Subscription Payments
                <span class="badge bg-light text-dark ms-2">{{ $pendingSubscriptions->count() }} of {{ $totalSubscriptionsCount ?? 0 }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment Proof</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingSubscriptions as $subscription)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $subscription->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $subscription->user->email ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $subscription->plan->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <strong class="text-primary">₹{{ number_format($subscription->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($subscription->payment_screenshot || $subscription->transaction_id)
                                    <span class="badge bg-warning">Payment Proof Submitted</span>
                                @else
                                    <span class="badge bg-secondary">No Payment Proof</span>
                                @endif
                            </td>
                            <td>
                                @if($subscription->payment_screenshot)
                                    <a href="{{ route('subscriptions.payment-screenshot', $subscription->id) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image me-1"></i>View Screenshot
                                    </a>
                                @endif
                                @if($subscription->transaction_id)
                                    <code class="small">{{ \Illuminate\Support\Str::limit($subscription->transaction_id, 15) }}</code>
                                @endif
                                @if(!$subscription->payment_screenshot && !$subscription->transaction_id)
                                    <span class="text-muted small">No proof yet</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $subscription->created_at->format('M d, Y') }}</small><br>
                                <small class="text-muted">{{ $subscription->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.subscriptions.view', $subscription->id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        No pending subscription payments found.
    </div>
    @endif
    @endif

    <!-- Pending Service Payments -->
    @if($filterType === 'all' || $filterType === 'services')
    @if($pendingServices->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-list me-2"></i>Pending Service Payments
                <span class="badge bg-light text-dark ms-2">{{ $pendingServices->count() }} of {{ $totalServicesCount ?? 0 }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Service Type</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Unpaid Balance</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingServices as $service)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $service->patient->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $service->patient->phone ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $service->serviceType->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <strong>₹{{ number_format($service->total_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <span class="text-success">₹{{ number_format($service->prepaid_amount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <strong class="text-danger">₹{{ number_format($service->unpaid_balance ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $service->status === 'completed' ? 'success' : ($service->status === 'in_progress' ? 'info' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $service->status)) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $service->created_at->format('M d, Y') }}</small><br>
                                <small class="text-muted">{{ $service->created_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.service-requests') }}?filter={{ $service->id }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        No pending service payments found.
    </div>
    @endif
    @endif

    @if(($totalSubscriptionsCount ?? 0) === 0 && ($totalServicesCount ?? 0) === 0)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5>No Pending Payments</h5>
            <p class="text-muted">All payments have been received. Great job! 🎉</p>
        </div>
    </div>
    @endif
</div>

<style>
.clickable-card-hover {
    cursor: pointer;
    transition: all 0.3s ease;
}

.clickable-card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #dee2e6;
    color: #495057;
}

.nav-tabs .nav-link.active {
    color: #495057;
    border-bottom-color: #0d6efd;
    font-weight: 600;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem;
    }
}
</style>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection


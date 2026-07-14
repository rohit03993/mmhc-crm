@extends('auth::layout')

@section('title', 'Visit refunds')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1"><i class="fas fa-undo-alt me-2 text-warning"></i>Visit refunds</h2>
            <p class="text-muted small mb-0">
                Cancelled bookings where the patient paid a visit fee. Pay the patient offline (Razorpay / UPI / bank), then mark refunded here.
                This is separate from <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">staff payouts</a>.
            </p>
        </div>
        <a href="{{ route('admin.service-requests', ['status' => 'cancelled']) }}" class="btn btn-outline-secondary btn-sm">
            Cancelled bookings
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Refund due</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['due_count'] }}</div>
                    <div class="small text-muted">₹{{ number_format($stats['due_amount'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Already refunded</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['refunded_count'] }}</div>
                    <div class="small text-muted">₹{{ number_format($stats['refunded_amount'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.visit-refunds', ['status' => 'due']) }}"
           class="btn btn-sm {{ $status === 'due' ? 'btn-warning' : 'btn-outline-warning' }}">
            Refund due
        </a>
        <a href="{{ route('admin.visit-refunds', ['status' => 'refunded']) }}"
           class="btn btn-sm {{ $status === 'refunded' ? 'btn-success' : 'btn-outline-success' }}">
            Refunded history
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Cancelled</th>
                        @if($status === 'due')
                            <th>Pay &amp; mark</th>
                        @else
                            <th>Refunded</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $item->id }}</div>
                                <div class="small text-muted">{{ $item->serviceType->name ?? 'Service' }}</div>
                                @if($item->razorpay_payment_id)
                                    <div class="small text-muted">RZP: {{ $item->razorpay_payment_id }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $item->patient->name ?? '—' }}</div>
                                <div class="small text-muted">{{ $item->patientContactPhone() ?? ($item->patient->email ?? '') }}</div>
                            </td>
                            <td class="fw-semibold">₹{{ number_format((float) ($item->refund_amount ?? $item->total_amount), 2) }}</td>
                            <td>
                                <div class="small">
                                    {{ $item->cancelled_at?->format('M d, Y g:i A') ?? '—' }}
                                </div>
                                <div class="small text-muted">
                                    by {{ $item->cancelledByUser->name ?? '—' }}
                                    @if($item->cancellation_reason)
                                        <br>{{ \Illuminate\Support\Str::limit($item->cancellation_reason, 80) }}
                                    @endif
                                </div>
                            </td>
                            @if($status === 'due')
                                <td style="min-width: 240px;">
                                    <form method="POST" action="{{ route('admin.visit-refunds.mark', $item) }}"
                                          onsubmit="return confirm('Confirm you already paid the patient offline, then mark this refunded in CRM?');">
                                        @csrf
                                        <input type="text"
                                               name="refund_reference"
                                               class="form-control form-control-sm mb-2"
                                               maxlength="191"
                                               placeholder="UPI / Razorpay refund ref (optional)"
                                               value="{{ old('refund_reference') }}">
                                        <textarea name="refund_note"
                                                  class="form-control form-control-sm mb-2"
                                                  rows="2"
                                                  maxlength="1000"
                                                  placeholder="Note (optional)">{{ old('refund_note') }}</textarea>
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fas fa-check me-1"></i>Mark refunded
                                        </button>
                                    </form>
                                </td>
                            @else
                                <td>
                                    <div class="small">{{ $item->refunded_at?->format('M d, Y g:i A') ?? '—' }}</div>
                                    <div class="small text-muted">by {{ $item->refundedByUser->name ?? '—' }}</div>
                                    @if($item->refund_reference)
                                        <div class="small">Ref: {{ $item->refund_reference }}</div>
                                    @endif
                                    @if($item->refund_note)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($item->refund_note, 80) }}</div>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                @if($status === 'due')
                                    No visit refunds waiting.
                                @else
                                    No refunded visit records yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($refunds->hasPages())
            <div class="card-body border-top">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

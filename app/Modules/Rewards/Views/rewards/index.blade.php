@extends('auth::layout')

@section('title', 'My Reward Entries')
@section('page-title', 'My Reward Entries')

@section('content')
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Total Reward Points</h5>
                    <h2 class="display-6 text-primary">{{ $totalPoints }}</h2>
                    <p class="text-muted mb-0">Reward Value: ₹{{ number_format($totalAmount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Submit New Patient Details</h5>
                    <p class="text-muted">Each valid submission earns <strong>1 point</strong> (₹10).</p>
                    <form method="GET" action="{{ route('rewards.create') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Add Patient Details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>Recent Submissions
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Pincode</th>
                            <th>Hospital</th>
                            <th>Reward</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rewards as $reward)
                            <tr>
                                <td>
                                    <strong>{{ $reward->patient_name }}</strong>
                                    @if($reward->treatment_details)
                                        <br><small class="text-muted">{{ Str::limit($reward->treatment_details, 30) }}</small>
                                    @endif
                                </td>
                                <td>{{ $reward->patient_age ?? '—' }}</td>
                                <td>{{ $reward->patient_phone }}</td>
                                <td>
                                    <small>{{ Str::limit($reward->patient_address ?? '—', 25) }}</small>
                                </td>
                                <td>{{ $reward->patient_pincode ?? '—' }}</td>
                                <td>{{ $reward->hospital_name }}</td>
                                <td><span class="badge bg-success">+1 pt (₹{{ number_format($reward->reward_amount, 2) }})</span></td>
                                <td>{{ $reward->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No reward submissions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rewards->hasPages())
            <div class="card-footer bg-white">
                {{ $rewards->links() }}
            </div>
        @endif
    </div>
@endsection


@extends('auth::layout')

@section('title', 'Reward Submissions')
@section('page-title', 'Caregiver & Nurse Rewards')

@section('content')
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Entries</h6>
                    <h3 class="mb-0">{{ number_format($rewards->total()) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Reward Points Issued</h6>
                    <h3 class="mb-0">{{ number_format($totalPoints) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Reward Value (₹)</h6>
                    <h3 class="mb-0">₹{{ number_format($totalAmount, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-gift me-2"></i>Submitted Patient Details
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Submitted By</th>
                            <th>Role</th>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Hospital</th>
                            <th>Reward</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rewards as $reward)
                            <tr>
                                <td>
                                    <strong>{{ $reward->user->name }}</strong><br>
                                    <small class="text-muted">{{ $reward->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($reward->user->role) }}</span>
                                </td>
                                <td>{{ $reward->patient_name }}</td>
                                <td>{{ $reward->patient_phone }}</td>
                                <td>{{ $reward->hospital_name }}</td>
                                <td>+{{ $reward->reward_points }} pts (₹{{ number_format($reward->reward_amount, 2) }})</td>
                                <td>{{ $reward->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
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


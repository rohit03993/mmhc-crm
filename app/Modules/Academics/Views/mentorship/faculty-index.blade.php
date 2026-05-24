@extends('auth::layout')

@section('title', 'Mentorship - Academics')
@section('page-title', 'Mentorship')

@section('content')
<div class="container-fluid py-3">
    <h2 class="h5 mb-1">My mentees</h2>
    @if(isset($fpiBreakdown))
        <p class="text-muted small mb-4">
            Active mentees: <strong>{{ $menteeCount }}</strong>
            · Your FPI: <strong>{{ $fpiBreakdown['percent'] }}%</strong>
            · Mentorship: <strong>{{ $fpiBreakdown['mentorship_percent'] }}%</strong>
            (+10 pts per mentee, +8 pts per rating)
        </p>
    @else
        <p class="text-muted small mb-4">Active mentees: <strong>{{ $menteeCount }}</strong></p>
    @endif

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($pendingRequests->isNotEmpty())
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-warning bg-opacity-10">Pending requests</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tbody>
                @foreach($pendingRequests as $req)
                <tr>
                    <td>
                        @if($req->mentee)
                            <a href="{{ route('academics.mentorship.profile', $req->mentee) }}" class="fw-medium text-decoration-none">{{ $req->mentee->name }}</a>
                            <span class="text-muted small">({{ $req->mentee->role }})</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="small">{{ Str::limit($req->request_message, 80) }}</td>
                    <td class="text-end">
                        <form action="{{ route('academics.mentorship.respond', $req) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="accept">
                            <button class="btn btn-sm btn-success">Accept</button>
                        </form>
                        <form action="{{ route('academics.mentorship.respond', $req) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="decline">
                            <button class="btn btn-sm btn-outline-danger">Decline</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($pendingReviews->isNotEmpty())
    <div class="card shadow-sm mb-3">
        <div class="card-header">Submissions awaiting your rating</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <tbody>
                @foreach($pendingReviews as $share)
                <tr>
                    <td>
                        @if($share->submission?->user)
                            <a href="{{ route('academics.mentorship.profile', $share->submission->user) }}" class="fw-medium text-decoration-none">{{ $share->submission->user->name }}</a>
                        @else
                            Student
                        @endif
                    </td>
                    <td>{{ $share->submission->assignment->title ?? 'Assignment' }}</td>
                    <td class="text-end"><a href="{{ route('academics.mentorship.reviews.show', $share) }}" class="btn btn-sm btn-primary">Review</a></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">Active mentees</div>
        <div class="card-body p-0">
            @if($activeMentees->isEmpty())
                <p class="text-muted p-4 mb-0">No active mentees yet.</p>
            @else
                <table class="table mb-0">
                    <tbody>
                    @foreach($activeMentees as $m)
                    <tr>
                        <td>
                            @if($m->mentee)
                                <a href="{{ route('academics.mentorship.profile', $m->mentee) }}" class="fw-medium text-decoration-none">{{ $m->mentee->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-muted small">{{ ucfirst($m->mentee->role ?? '') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

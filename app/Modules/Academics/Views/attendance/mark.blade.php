@extends('auth::layout')

@section('title', 'Mark Attendance - ' . $batch->name . ' - Academics')
@section('page-title', 'Mark attendance: ' . $batch->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 academics-page-toolbar">
        <h2 class="h5 mb-0">{{ $batch->name }} — {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h2>
        <a href="{{ route('academics.attendance.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($students->isEmpty())
                <p class="text-muted mb-0">No students in this batch.</p>
            @else
                <form action="{{ route('academics.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                <tr>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        @php
                                            $rec = $existing->get($student->id);
                                            $current = $rec ? $rec->status : \App\Modules\Academics\Models\Attendance::STATUS_PRESENT;
                                        @endphp
                                        <select name="attendance[{{ $student->id }}]" class="form-select form-select-sm w-auto">
                                            @foreach(\App\Modules\Academics\Models\Attendance::statuses() as $value => $label)
                                                <option value="{{ $value }}" {{ $current === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save attendance</button>
                        <a href="{{ route('academics.attendance.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

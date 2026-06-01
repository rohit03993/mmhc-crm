@php
    $balance = $serviceRequest->balanceDue();
@endphp
@if((float) $serviceRequest->total_amount > 0 && $balance > 0)
<div class="sr-card mb-4 border-warning">
    <h3 class="sr-card__title"><i class="fas fa-hand-holding-usd me-2 text-warning"></i>Record patient payment</h3>
    <p class="text-muted small mb-3">
        Adds to <strong>prepaid amount</strong> so dashboard service earning matches money collected.
        Balance due: <strong class="text-danger">₹{{ number_format($balance, 0) }}</strong>
    </p>
    <form method="POST" action="{{ route('admin.service-requests.record-collection', $serviceRequest) }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-4">
            <label class="form-label small mb-1" for="collection_amount_{{ $serviceRequest->id }}">Amount received (₹)</label>
            <input type="number"
                   name="amount"
                   id="collection_amount_{{ $serviceRequest->id }}"
                   class="form-control"
                   step="0.01"
                   min="0.01"
                   max="{{ $balance }}"
                   value="{{ $balance }}"
                   required>
        </div>
        <div class="col-md-5">
            <label class="form-label small mb-1" for="collection_note_{{ $serviceRequest->id }}">Note (optional)</label>
            <input type="text"
                   name="collection_note"
                   id="collection_note_{{ $serviceRequest->id }}"
                   class="form-control"
                   maxlength="500"
                   placeholder="Cash, UPI ref, etc.">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-warning w-100 fw-semibold"
                    onclick="return confirm('Record this patient payment?');">
                <i class="fas fa-check me-1"></i>Record
            </button>
        </div>
    </form>
</div>
@elseif((float) $serviceRequest->total_amount > 0 && $balance <= 0)
<div class="alert alert-success small mb-4">
    <i class="fas fa-check-circle me-1"></i>Patient charge fully collected (₹{{ number_format($serviceRequest->prepaid_amount, 0) }}).
</div>
@endif

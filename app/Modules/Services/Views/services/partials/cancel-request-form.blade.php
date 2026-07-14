{{-- Patient cancel form — pending / pending_approval only --}}
@if($serviceRequest->canBeCancelledByPatient())
<form method="POST"
      action="{{ route('services.cancel', $serviceRequest) }}"
      class="mmhc-cancel-request-form {{ $compact ?? false ? 'mmhc-cancel-request-form--compact' : '' }}"
      onsubmit="return confirm('Cancel this service request? This cannot be undone.');">
    @csrf
    @unless($compact ?? false)
        <div class="mb-2">
            <label for="cancellation_reason_{{ $serviceRequest->id }}" class="form-label small text-muted mb-1">
                Reason (optional)
            </label>
            <textarea name="cancellation_reason"
                      id="cancellation_reason_{{ $serviceRequest->id }}"
                      class="form-control form-control-sm"
                      rows="2"
                      maxlength="500"
                      placeholder="Why are you cancelling?">{{ old('cancellation_reason') }}</textarea>
        </div>
    @endunless
    <button type="submit"
            class="btn {{ ($compact ?? false) ? 'btn-outline-warning btn-sm' : 'btn-warning w-100 rounded-3 py-3 fw-semibold' }}">
        <i class="fas fa-times me-1"></i>@if($compact ?? false) @else Cancel request @endif
    </button>
</form>
@endif

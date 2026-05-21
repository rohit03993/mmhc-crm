@php
    $modalId = $modalId ?? 'staffIdCardModal';
    $isAdminView = auth()->user()->isAdmin() && auth()->id() !== $user->id;
    $viewUrl = ($isAdminView ? route('admin.staff.id-card', $user) : route('profile.id-card')).'?embed=1';
    $printUrl = $isAdminView ? route('admin.staff.id-card', $user) : route('profile.id-card');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="{{ $modalId }}Label">
                        <i class="fas fa-id-card text-primary me-2"></i>Staff ID card
                    </h5>
                    <p class="small text-muted mb-0">{{ $user->name }} · {{ $user->unique_id }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2 pb-3 px-3" style="background: #e2e8f0;">
                <iframe
                    src="{{ $viewUrl }}"
                    title="ID card preview for {{ $user->name }}"
                    class="d-block w-100 border-0 rounded-3"
                    style="min-height: 320px; height: 42vh; max-height: 420px; background: #e2e8f0;"
                    loading="lazy"
                ></iframe>
                <p class="small text-muted text-center mt-2 mb-0">Preview size is enlarged for screen viewing. Print uses standard CR80 card size.</p>
            </div>
            <div class="modal-footer border-0 pt-0 flex-wrap gap-2">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Close</button>
                <a href="{{ $printUrl }}" class="btn btn-primary rounded-pill" target="_blank" rel="noopener">
                    <i class="fas fa-print me-1"></i> Open print view
                </a>
            </div>
        </div>
    </div>
</div>

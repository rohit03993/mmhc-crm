{{-- Expects: $inputId, $linkUrl, optional $theme ('staff'|'subscription'), $whatsappText --}}
@php
    $inputId = $inputId ?? 'referralLink';
    $theme = $theme ?? 'staff';
    $copyBtnClass = $theme === 'subscription' ? 'btn-success' : 'btn-primary';
@endphp
<div class="input-group-modern mb-2">
    <input type="text"
           class="form-control form-control-lg"
           id="{{ $inputId }}"
           value="{{ $linkUrl }}"
           readonly
           aria-label="Referral link">
    <div class="mmhc-share-actions">
        <button type="button"
                class="btn {{ $copyBtnClass }} btn-lg"
                data-mmhc-copy="{{ $inputId }}"
                data-mmhc-btn-class="btn {{ $copyBtnClass }} btn-lg">
            <i class="fas fa-copy me-1"></i>Copy
        </button>
        <button type="button"
                class="btn btn-outline-success btn-lg"
                data-mmhc-whatsapp="{{ $inputId }}"
                data-mmhc-whatsapp-text="{{ $whatsappText ?? 'Join MMHC using my link: ' }}">
            <i class="fab fa-whatsapp me-1"></i>WhatsApp
        </button>
    </div>
</div>

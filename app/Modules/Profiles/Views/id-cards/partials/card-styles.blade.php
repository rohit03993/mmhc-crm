<style>
    :root {
        --mmhc-indigo: #312e81;
        --mmhc-blue: #1d4ed8;
        --mmhc-slate: #0f172a;
        --mmhc-nurse: #2563eb;
        --mmhc-nurse-light: #dbeafe;
        --mmhc-caregiver: #059669;
        --mmhc-caregiver-light: #d1fae5;
        --id-preview-scale: {{ $previewScale ?? 2.35 }};
    }

    .id-card-wrap {
        width: 85.6mm;
        height: 54mm;
    }

    .id-card-scaler {
        width: calc(85.6mm * var(--id-preview-scale));
        height: calc(54mm * var(--id-preview-scale));
        margin-left: auto;
        margin-right: auto;
    }

    .id-card-scaler .id-card-wrap {
        transform: scale(var(--id-preview-scale));
        transform-origin: top left;
    }

    .id-card {
        width: 100%;
        height: 100%;
        background: #fff;
        border-radius: 3.2mm;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .id-card--inactive::after {
        content: 'INACTIVE';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-18deg);
        font-size: 7mm;
        font-weight: 800;
        color: rgba(220, 38, 38, 0.22);
        letter-spacing: 0.15em;
        pointer-events: none;
        z-index: 2;
    }

    .id-card__header {
        background: linear-gradient(120deg, var(--mmhc-indigo) 0%, var(--mmhc-blue) 55%, var(--mmhc-slate) 100%);
        color: #fff;
        padding: 1.6mm 2.4mm;
        display: flex;
        align-items: center;
        gap: 1.8mm;
        min-height: 11mm;
    }

    .id-card__logo {
        width: 8mm;
        height: 8mm;
        object-fit: contain;
        background: rgba(255,255,255,0.95);
        border-radius: 1.2mm;
        padding: 0.4mm;
        flex-shrink: 0;
    }

    .id-card__brand { flex: 1; min-width: 0; }

    .id-card__brand-name {
        font-size: 2.5mm;
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .id-card__brand-tag {
        font-size: 1.7mm;
        opacity: 0.9;
        font-weight: 500;
    }

    .id-card__role {
        font-size: 2mm;
        font-weight: 800;
        letter-spacing: 0.06em;
        padding: 0.8mm 1.6mm;
        border-radius: 999px;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .id-card__role--nurse { background: var(--mmhc-nurse-light); color: var(--mmhc-nurse); }
    .id-card__role--caregiver { background: var(--mmhc-caregiver-light); color: var(--mmhc-caregiver); }

    .id-card__body {
        flex: 1;
        display: flex;
        padding: 2mm 2.4mm 1.6mm;
        gap: 2.4mm;
        min-height: 0;
    }

    .id-card__photo-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.2mm;
        flex-shrink: 0;
    }

    .id-card__photo {
        width: 18mm;
        height: 18mm;
        border-radius: 2mm;
        object-fit: cover;
        border: 0.35mm solid #e2e8f0;
        background: #f1f5f9;
    }

    .id-card__initials {
        width: 18mm;
        height: 18mm;
        border-radius: 2mm;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 6.5mm;
        font-weight: 800;
        color: #fff;
        border: 0.35mm solid rgba(255,255,255,0.3);
    }

    .id-card__initials--nurse { background: linear-gradient(145deg, #3b82f6, var(--mmhc-nurse)); }
    .id-card__initials--caregiver { background: linear-gradient(145deg, #34d399, var(--mmhc-caregiver)); }

    .id-card__qr canvas {
        display: block;
        width: 11mm !important;
        height: 11mm !important;
    }

    .id-card__details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.5mm;
    }

    .id-card__name {
        font-size: 3.4mm;
        font-weight: 800;
        color: var(--mmhc-slate);
        line-height: 1.15;
        word-break: break-word;
    }

    .id-card__uid {
        font-size: 2.1mm;
        font-weight: 700;
        color: var(--mmhc-blue);
        margin-bottom: 0.6mm;
    }

    .id-card__row {
        display: flex;
        gap: 1mm;
        font-size: 2mm;
        line-height: 1.25;
    }

    .id-card__label {
        font-weight: 700;
        color: #64748b;
        flex-shrink: 0;
        width: 11mm;
    }

    .id-card__value {
        color: #1e293b;
        font-weight: 500;
        word-break: break-word;
    }

    .id-card__footer {
        background: #f8fafc;
        border-top: 0.25mm solid #e2e8f0;
        padding: 1.2mm 2.4mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        font-size: 1.7mm;
        color: #64748b;
        font-weight: 600;
    }

    .id-card__footer strong { color: var(--mmhc-indigo); }

    @media print {
        @page { size: 85.6mm 54mm; margin: 0; }

        .id-card-scaler {
            width: 85.6mm !important;
            height: 54mm !important;
        }

        .id-card-scaler .id-card-wrap {
            transform: none !important;
        }

        .id-card {
            box-shadow: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

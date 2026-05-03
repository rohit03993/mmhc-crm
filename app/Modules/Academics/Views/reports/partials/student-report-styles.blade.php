{{-- KPI tiles: auth layout forces white text on .bg-success etc.; keep these readable --}}
<style>
    .mmhc-kpi-tint {
        border-radius: 0.75rem;
        padding: 0.65rem 0.5rem;
        text-align: center;
        border-width: 1px;
        border-style: solid;
    }
    .mmhc-kpi-tint__label {
        color: #64748b !important;
        font-size: 0.8125rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        letter-spacing: 0.02em;
    }
    .mmhc-kpi-tint__value {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1.2;
        color: #0f172a !important;
    }
    .mmhc-kpi-tint--neutral {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    .mmhc-kpi-tint--present {
        background: rgba(22, 163, 74, 0.14);
        border-color: rgba(22, 163, 74, 0.4);
    }
    .mmhc-kpi-tint--present .mmhc-kpi-tint__value { color: #166534 !important; }
    .mmhc-kpi-tint--absent {
        background: rgba(220, 38, 38, 0.12);
        border-color: rgba(220, 38, 38, 0.38);
    }
    .mmhc-kpi-tint--absent .mmhc-kpi-tint__value { color: #b91c1c !important; }
    .mmhc-kpi-tint--leave {
        background: rgba(202, 138, 4, 0.14);
        border-color: rgba(180, 130, 8, 0.45);
    }
    .mmhc-kpi-tint--leave .mmhc-kpi-tint__value { color: #a16207 !important; }
    .mmhc-kpi-tint--pct {
        background: rgba(37, 99, 235, 0.12);
        border-color: rgba(37, 99, 235, 0.38);
    }
    .mmhc-kpi-tint--pct .mmhc-kpi-tint__value { color: #1d4ed8 !important; }
</style>

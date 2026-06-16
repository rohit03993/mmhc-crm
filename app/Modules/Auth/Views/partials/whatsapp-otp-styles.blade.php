<style>
    /* WhatsApp OTP sign-in flow */
    .wa-otp-flow { margin-bottom: 0.5rem; }

    .wa-otp-steps {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 1.25rem;
        padding: 0.35rem;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
    }
    .wa-otp-step {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.55rem 0.5rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #94a3b8;
        border-radius: 10px;
        transition: all 0.25s ease;
    }
    .wa-otp-step-num {
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        background: #e2e8f0;
        color: #64748b;
        flex-shrink: 0;
    }
    .wa-otp-step.is-active {
        color: #0f766e;
        background: #ecfdf5;
    }
    .wa-otp-step.is-active .wa-otp-step-num {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 211, 102, 0.35);
    }
    .wa-otp-step.is-done {
        color: #059669;
    }
    .wa-otp-step.is-done .wa-otp-step-num {
        background: #059669;
        color: #fff;
    }
    .wa-otp-step-line {
        width: 1.25rem;
        height: 2px;
        background: #cbd5e1;
        flex-shrink: 0;
    }

    .wa-otp-banner {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        font-size: 0.88rem;
        line-height: 1.45;
    }
    .wa-otp-banner__icon {
        width: 2rem;
        height: 2rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }
    .wa-otp-banner--info {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1px solid #bbf7d0;
        color: #14532d;
    }
    .wa-otp-banner--info .wa-otp-banner__icon {
        background: #25d366;
        color: #fff;
    }
    .wa-otp-banner--success {
        background: linear-gradient(135deg, #eff6ff 0%, #ecfdf5 100%);
        border: 1px solid #a7f3d0;
        color: #134e4a;
    }
    .wa-otp-banner--success .wa-otp-banner__icon {
        background: #128c7e;
        color: #fff;
    }
    .wa-otp-banner--warn {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .wa-otp-number-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
    }
    .wa-otp-number-card__wa {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .wa-otp-number-card__label {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
    }
    .wa-otp-number-card__value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: 0.02em;
    }
    .wa-otp-number-card__hint {
        font-size: 0.78rem;
        color: #64748b;
        margin: 0.15rem 0 0;
    }

    .wa-otp-input {
        text-align: center;
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.45em !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        font-variant-numeric: tabular-nums;
    }
    #login_phone { padding-left: 3.25rem !important; }
    .wa-phone-prefix {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 600;
        color: #64748b;
        z-index: 3;
        pointer-events: none;
        font-size: 0.95rem;
    }
    .wa-phone-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #25d366;
        z-index: 3;
        pointer-events: none;
        font-size: 1.15rem;
        opacity: 0.85;
    }

    .btn-wa {
        width: 100%;
        height: 56px;
        border: none;
        border-radius: 14px;
        color: #fff;
        font-size: 1.02rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }
    .btn-wa:hover:not(:disabled) {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(37, 211, 102, 0.45);
    }
    .btn-wa:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .wa-otp-resend-row {
        margin-top: 0.75rem;
        text-align: center;
    }
    .wa-otp-resend-btn {
        background: none;
        border: none;
        color: #128c7e;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.35rem 0.5rem;
        cursor: pointer;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .wa-otp-resend-btn:disabled {
        color: #94a3b8;
        cursor: not-allowed;
        text-decoration: none;
    }
    .wa-otp-resend-timer {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 0.25rem;
    }
    .wa-otp-alt-link {
        display: block;
        text-align: center;
        margin-top: 0.65rem;
        font-size: 0.88rem;
        color: #64748b;
        text-decoration: none;
    }
    .wa-otp-alt-link:hover { color: #475569; text-decoration: underline; }

    .login-tabs .nav-link.tab-wa.active {
        background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
        color: #fff;
    }
</style>

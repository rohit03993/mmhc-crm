@extends('auth::layout')

@section('title', 'Sign in - MMHC CRM')

@section('head')
<style>
    .form-floating-modern {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-floating-modern label {
        display: block;
        margin-bottom: 0.5rem;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .form-floating-modern input:focus ~ label {
        color: #667eea;
    }

    .login-tabs .nav-link { border-radius: 12px; font-weight: 600; color: #64748b; }
    .login-tabs .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    #login_phone { padding-left: 3rem; }

    .form-floating-modern .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        z-index: 3;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .form-floating-modern input:focus ~ .input-icon {
        color: #667eea;
        transform: translateY(-50%) scale(1.1);
    }

    .form-floating-modern input {
        width: 100%;
        height: 56px;
        padding: 1rem 1rem 1rem 52px;
        font-size: 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.3s ease;
        background: #ffffff;
        color: #1e293b;
    }

    .form-floating-modern input::placeholder {
        color: #cbd5e1;
    }

    .form-floating-modern input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-floating-modern input.is-invalid {
        border-color: #ef4444;
    }

    .form-floating-modern input.is-invalid ~ label {
        color: #ef4444;
    }

    .password-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        z-index: 3;
        padding: 0.5rem;
        transition: all 0.3s ease;
    }

    .password-toggle:hover {
        color: #667eea;
        transform: translateY(-50%) scale(1.1);
    }

    .remember-me-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.75rem;
    }

    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-check-modern input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #667eea;
        border-radius: 6px;
    }

    .form-check-modern label {
        color: #475569;
        font-size: 0.95rem;
        cursor: pointer;
        margin: 0;
        user-select: none;
    }

    .forgot-password-link {
        color: #667eea;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .forgot-password-link:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    .btn-login {
        width: 100%;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 14px;
        color: white;
        font-size: 1.05rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        position: relative;
        overflow: hidden;
    }

    .btn-login::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s ease;
    }

    .btn-login:hover::before {
        left: 100%;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        background: #fee2e2;
        color: #991b1b;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
    }

    .alert-modern ul {
        margin: 0;
        padding-left: 1.25rem;
    }

    .alert-modern li {
        margin-bottom: 0.25rem;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 2rem 0;
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider::before {
        margin-right: 1rem;
    }

    .divider::after {
        margin-left: 1rem;
    }

    .signup-link {
        text-align: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .signup-link p {
        color: #64748b;
        margin: 0;
        font-size: 0.95rem;
    }

    .signup-link a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .signup-link a:hover {
        color: #5568d3;
        text-decoration: underline;
    }

    /* Email tab: dual signup paths (healthcare vs academics) */
    .login-signup-section {
        margin-top: 1.75rem;
        padding-top: 0.25rem;
    }
    .login-signup-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e2e8f0 15%, #e2e8f0 85%, transparent);
        margin-bottom: 1.5rem;
    }
    .login-signup-head {
        text-align: left;
        margin-bottom: 1.25rem;
    }
    .login-signup-kicker {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .login-signup-title {
        font-size: 1.2rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin: 0 0 0.35rem;
        line-height: 1.25;
    }
    .login-signup-lead {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.5;
        margin: 0;
        max-width: 36rem;
    }
    .login-signup-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 520px) {
        .login-signup-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.875rem;
        }
    }
    .login-signup-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        text-decoration: none;
        color: inherit;
        padding: 1.15rem 1.1rem 1.05rem;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(165deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        outline: none;
    }
    .login-signup-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
        border-color: #cbd5e1;
    }
    .login-signup-card:focus-visible {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.35), 0 12px 28px rgba(15, 23, 42, 0.08);
    }
    .login-signup-card-glow {
        position: absolute;
        top: -40%;
        right: -30%;
        width: 70%;
        height: 80%;
        border-radius: 50%;
        opacity: 0.12;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .login-signup-card:hover .login-signup-card-glow {
        opacity: 0.2;
    }
    .login-signup-card--healthcare .login-signup-card-glow {
        background: radial-gradient(circle, #667eea 0%, #764ba2 60%, transparent 70%);
    }
    .login-signup-card--academics .login-signup-card-glow {
        background: radial-gradient(circle, #0ea5e9 0%, #6366f1 55%, transparent 70%);
    }
    .login-signup-card--healthcare {
        border-color: rgba(102, 126, 234, 0.22);
    }
    .login-signup-card--healthcare:hover {
        border-color: rgba(102, 126, 234, 0.45);
    }
    .login-signup-card--academics {
        border-color: rgba(14, 165, 233, 0.25);
    }
    .login-signup-card--academics:hover {
        border-color: rgba(14, 165, 233, 0.5);
    }
    .login-signup-card-icon {
        position: relative;
        z-index: 1;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 0.65rem;
    }
    .login-signup-card--healthcare .login-signup-card-icon {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.12) 100%);
        color: #5b21b6;
    }
    .login-signup-card--academics .login-signup-card-icon {
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.18) 0%, rgba(99, 102, 241, 0.12) 100%);
        color: #0369a1;
    }
    .login-signup-card-eyebrow {
        position: relative;
        z-index: 1;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .login-signup-card-name {
        position: relative;
        z-index: 1;
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #0f172a;
        margin-bottom: 0.35rem;
        line-height: 1.2;
    }
    .login-signup-card-desc {
        position: relative;
        z-index: 1;
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.45;
        margin: 0 0 0.5rem;
    }
    .login-signup-card-list {
        position: relative;
        z-index: 1;
        margin: 0 0 0.75rem;
        padding-left: 1rem;
        font-size: 0.72rem;
        color: #475569;
        line-height: 1.45;
    }
    .login-signup-card-list li {
        margin-bottom: 0.2rem;
    }
    .login-signup-card-list li::marker {
        color: #94a3b8;
    }
    .login-signup-card--healthcare .login-signup-card-list li::marker {
        color: #a78bfa;
    }
    .login-signup-card--academics .login-signup-card-list li::marker {
        color: #38bdf8;
    }
    .login-signup-card-cta {
        position: relative;
        z-index: 1;
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .login-signup-card--healthcare .login-signup-card-cta {
        color: #5b21b6;
    }
    .login-signup-card--academics .login-signup-card-cta {
        color: #0369a1;
    }
    .login-signup-card-cta i {
        font-size: 0.72rem;
        transition: transform 0.2s ease;
    }
    .login-signup-card:hover .login-signup-card-cta i {
        transform: translateX(4px);
    }

    /* Desktop split: dense layout so email tab + signup fits without scroll */
    @media (min-width: 992px) {
        .academics-form-wrap {
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .academics-form-wrap .login-tabs {
            margin-bottom: 0.45rem !important;
        }
        .academics-form-wrap .login-tabs .nav-link {
            padding: 0.4rem 0.55rem;
            font-size: 0.8rem;
            border-radius: 10px;
        }
        .academics-form-wrap #loginTabContent {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .academics-form-wrap #loginTabContent > .tab-pane.fade.show {
            display: flex !important;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: 100%;
            overflow: hidden;
        }
        .academics-login-right .form-floating-modern {
            margin-bottom: 0.5rem;
        }
        .academics-login-right .form-floating-modern label {
            font-size: 0.78rem;
            margin-bottom: 0.2rem;
        }
        .academics-login-right .form-floating-modern input {
            height: 44px;
            padding: 0.45rem 0.55rem 0.45rem 2.25rem;
            font-size: 0.9rem;
            border-radius: 11px;
            border-width: 1px;
        }
        .academics-login-right .form-floating-modern .input-icon {
            left: 12px;
            font-size: 0.85rem;
        }
        .academics-login-right .password-toggle {
            right: 8px;
            padding: 0.3rem;
        }
        .academics-login-right .remember-me-container {
            margin-bottom: 0.45rem;
        }
        .academics-login-right .form-check-modern label {
            font-size: 0.8rem;
        }
        .academics-login-right .form-check-modern input[type="checkbox"] {
            width: 17px;
            height: 17px;
        }
        .academics-login-right .forgot-password-link {
            font-size: 0.8rem;
        }
        .academics-login-right .btn-login {
            height: 46px;
            font-size: 0.95rem;
            border-radius: 11px;
            letter-spacing: 0.02em;
        }
        .academics-login-right .btn-login:hover {
            transform: translateY(-1px);
        }
        .academics-login-right .alert-modern {
            padding: 0.5rem 0.65rem;
            margin-bottom: 0.45rem;
            font-size: 0.8rem;
        }
        .academics-login-right .login-signup-section {
            margin-top: 0.45rem;
            padding-top: 0;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .academics-login-right .login-signup-divider {
            margin-bottom: 0.45rem;
        }
        .academics-login-right .login-signup-head {
            margin-bottom: 0.45rem;
        }
        .academics-login-right .login-signup-kicker {
            font-size: 0.65rem;
            margin-bottom: 0.1rem;
        }
        .academics-login-right .login-signup-title {
            font-size: 0.98rem;
            margin-bottom: 0.1rem;
        }
        .academics-login-right .login-signup-lead {
            font-size: 0.72rem;
            line-height: 1.35;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .academics-login-right .login-signup-grid {
            gap: 0.65rem;
            flex: 1 1 auto;
            min-height: 0;
        }
        .academics-login-right .login-signup-card {
            padding: 0.65rem 0.65rem 0.55rem;
            border-radius: 12px;
        }
        .academics-login-right .login-signup-card-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
        }
        .academics-login-right .login-signup-card-eyebrow {
            font-size: 0.6rem;
            letter-spacing: 0.07em;
        }
        .academics-login-right .login-signup-card-name {
            font-size: 0.88rem;
            margin-bottom: 0.2rem;
        }
        .academics-login-right .login-signup-card-desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            font-size: 0.72rem;
            line-height: 1.35;
            margin: 0 0 0.35rem;
            color: #64748b;
        }
        .academics-login-right .login-signup-card-list {
            font-size: 0.68rem;
            margin: 0 0 0.3rem;
            padding-left: 0.9rem;
            line-height: 1.35;
        }
        .academics-login-right .login-signup-card-list li {
            margin-bottom: 0.08rem;
        }
        .academics-login-right .login-signup-card-cta {
            font-size: 0.74rem;
            margin-top: 0.15rem;
        }
        .academics-login-brand-stack {
            gap: 0.45rem;
        }
        .academics-login-left-img-wrap {
            padding: 0.55rem 0.75rem;
            min-height: 0;
            margin-bottom: 0;
            border-radius: 14px;
        }
        .academics-login-left-img {
            max-height: min(12vh, 100px);
        }
        .academics-hero-block {
            margin-bottom: 0;
        }
        .academics-hero-kicker {
            margin-bottom: 0.25rem;
        }
        .academics-hero-title {
            font-size: clamp(1rem, 2.2vh, 1.35rem);
            margin-bottom: 0.2rem;
        }
        .academics-hero-sub {
            font-size: 0.78rem;
            margin-bottom: 0.3rem;
        }
        .academics-hero-desc {
            font-size: 0.68rem;
            line-height: 1.4;
        }
        .academics-badges {
            margin-top: 0.15rem;
            gap: 0.35rem;
        }
        .academics-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.55rem;
        }
        .academics-login-footer {
            flex-shrink: 0;
        }
    }

    @media (min-width: 992px) and (max-height: 720px) {
        .academics-login-wrapper {
            font-size: 97%;
        }
        .academics-login-right .login-signup-lead {
            -webkit-line-clamp: 1;
        }
        .academics-login-right .login-signup-card-list {
            display: none;
        }
        .academics-login-right .login-signup-card-cta {
            margin-top: 0.2rem;
        }
    }

    /* Split login: no page scroll, fit viewport, gallery on left */
    body:has(.academics-login-wrapper),
    html:has(.academics-login-wrapper) {
        overflow: hidden !important;
        height: 100dvh !important;
        max-height: 100dvh !important;
    }
    .academics-login-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        height: 100dvh;
        max-height: 100dvh;
        display: flex;
        background: #f8fafc;
        z-index: 10;
        overflow: hidden;
    }
    .academics-login-left {
        flex: 0 0 50%;
        position: relative;
        isolation: isolate;
        background: linear-gradient(152deg, #0c1222 0%, #142947 38%, #0a2744 72%, #051525 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(1.5rem, 4vw, 2.75rem);
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }
    @media (min-width: 992px) {
        .academics-login-left {
            overflow-y: hidden;
            padding: clamp(0.6rem, 1.8vh, 1.25rem) 1rem;
        }
    }
    .academics-login-left::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 90% 60% at 15% 25%, rgba(14, 165, 233, 0.28), transparent 55%),
            radial-gradient(ellipse 70% 50% at 88% 75%, rgba(99, 102, 241, 0.22), transparent 50%),
            radial-gradient(ellipse 50% 40% at 50% 100%, rgba(6, 182, 212, 0.12), transparent 45%);
        pointer-events: none;
        z-index: 0;
    }
    .academics-login-left::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
        background-size: 56px 56px;
        mask-image: linear-gradient(165deg, black 0%, black 45%, transparent 92%);
        opacity: 0.65;
        pointer-events: none;
        z-index: 0;
    }
    .academics-login-left-inner {
        position: relative;
        z-index: 1;
        width: 100%;
        min-height: min-content;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1.25rem;
    }
    .academics-login-brand-stack {
        width: 100%;
        max-width: min(520px, 100%);
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }
    .academics-left-featured {
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 0;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow:
            0 24px 48px rgba(0, 0, 0, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.1);
        flex: 0 0 auto;
        min-height: 52vh;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    @media (min-width: 992px) {
        /* Hero carousel: one large featured image + compact thumbnails (like VPS, larger hero than before) */
        .academics-left-featured {
            min-height: min(36vh, 340px);
            max-height: min(54vh, 520px);
            flex: 1 1 auto;
        }
        .academics-left-featured-img {
            min-height: min(30vh, 280px);
            max-height: min(48vh, 460px);
            width: 100%;
            object-fit: contain;
        }
        .academics-login-left-inner {
            gap: 0.5rem;
            max-height: 100%;
            overflow: hidden;
        }
        .academics-left-thumb-card {
            flex: 0 0 calc(16.66% - 0.35rem);
            max-width: calc(16.66% - 0.35rem);
        }
        .academics-left-thumb-card img {
            max-height: 40px;
            object-fit: cover;
        }
        .academics-left-thumb-card .name {
            font-size: 0.6rem;
            padding: 0.25rem 0.2rem;
        }
    }
    .academics-left-featured .academics-left-slides {
        border-radius: 12px;
        overflow: hidden;
    }
    .academics-left-slides {
        position: relative;
        flex: 1;
        min-height: 0;
    }
    .academics-left-slide {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        pointer-events: none;
    }
    .academics-left-slide.academics-slide-active {
        opacity: 1;
        pointer-events: auto;
    }
    .academics-left-featured-img {
        width: 100%;
        height: 100%;
        min-height: 42vh;
        flex: 1;
        object-fit: contain;
        object-position: center;
        display: block;
        background: rgba(0,0,0,0.15);
    }
    .academics-left-featured-caption {
        padding: 0.85rem 1.1rem;
        color: #fff;
        text-align: center;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.12) 0%, rgba(0, 0, 0, 0.35) 100%);
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .academics-left-featured-caption .name { font-size: 0.95rem; font-weight: 600; display: block; letter-spacing: -0.01em; }
    .academics-left-featured-caption .sub { font-size: 0.8rem; opacity: 0.9; }
    .academics-left-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        flex-shrink: 0;
    }
    .academics-left-thumb-card {
        flex: 0 0 calc(25% - 0.5rem);
        max-width: calc(25% - 0.5rem);
        background: rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.22);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .academics-thumb-btn {
        cursor: pointer;
        padding: 0;
        border: none;
        text-align: left;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .academics-thumb-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
    }
    .academics-thumb-btn.active {
        border-color: rgba(14, 165, 233, 0.65);
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.35);
    }
    .academics-left-thumb-card img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
        object-position: center;
        display: block;
        background: rgba(0,0,0,0.1);
    }
    .academics-left-thumb-card .name {
        padding: 0.5rem 0.35rem;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.2;
    }
    @media (max-width: 991px) {
        .academics-left-thumb-card { flex: 0 0 calc(50% - 0.35rem); max-width: calc(50% - 0.35rem); }
    }
    @media (max-width: 400px) {
        .academics-left-thumb-card { flex: 0 0 calc(50% - 0.25rem); max-width: calc(50% - 0.25rem); }
    }
    .academics-login-left-img-wrap {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-radius: 20px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        box-shadow:
            0 12px 40px rgba(0, 0, 0, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    .academics-login-left-img {
        max-width: 100%;
        max-height: 22vh;
        width: auto;
        height: auto;
        object-fit: contain;
        display: block;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.25));
    }
    .academics-login-left-img-placeholder { text-align: center; color: #e2e8f0; padding: 1rem; }
    .academics-placeholder-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; color: #7dd3fc; opacity: 0.95; }
    .academics-placeholder-text { font-size: 1rem; font-weight: 600; color: #f1f5f9; }
    .academics-hero-block {
        color: #fff;
        margin-bottom: 0.25rem;
    }
    .academics-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(224, 242, 254, 0.85);
        margin-bottom: 0.75rem;
    }
    .academics-hero-kicker::before {
        content: '';
        width: 28px;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, #38bdf8, transparent);
    }
    .academics-hero-title {
        font-size: clamp(1.55rem, 2.8vw, 2.15rem);
        font-weight: 800;
        margin-bottom: 0.5rem;
        letter-spacing: -0.035em;
        line-height: 1.15;
        background: linear-gradient(135deg, #ffffff 0%, #e0f2fe 55%, #bae6fd 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .academics-hero-sub {
        font-size: clamp(1rem, 1.6vw, 1.15rem);
        font-weight: 500;
        color: rgba(226, 232, 240, 0.95);
        margin-bottom: 1rem;
        letter-spacing: -0.01em;
    }
    .academics-hero-desc {
        font-size: 0.925rem;
        line-height: 1.65;
        color: rgba(203, 213, 225, 0.92);
        margin-bottom: 0;
    }
    .academics-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.25rem;
    }
    .academics-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.09);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        color: #f1f5f9;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
    }
    .academics-badge i {
        font-size: 0.75rem;
        opacity: 0.9;
        color: #7dd3fc;
    }
    .academics-login-right {
        flex: 0 0 50%;
        background: #fff;
        display: flex;
        align-items: stretch;
        justify-content: center;
        padding: 1.25rem 2rem;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
    }
    @media (min-width: 992px) {
        .academics-login-right {
            overflow-y: hidden;
            overflow-x: hidden;
            padding: 0.55rem 1.25rem 0.45rem;
            align-items: center;
        }
    }
    .academics-login-right-inner {
        width: 100%;
        max-width: 520px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        min-height: 0;
        max-height: 100%;
    }
    @media (min-width: 992px) {
        .academics-login-right-inner {
            flex: 1 1 auto;
            max-height: 100dvh;
            justify-content: center;
        }
    }
    .academics-logo {
        max-width: 140px;
        height: auto;
        margin-bottom: 0.5rem;
        display: block;
    }
    @media (min-width: 992px) {
        .academics-logo {
            max-width: 132px;
            max-height: 44px;
            width: auto;
            object-fit: contain;
            margin-bottom: 0.3rem;
        }
    }
    .academics-portal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
        padding-bottom: 0.35rem;
        border-bottom: 3px solid #667eea;
        display: inline-block;
    }
    @media (min-width: 992px) {
        .academics-portal-title {
            font-size: 1.35rem;
            padding-bottom: 0.2rem;
            border-bottom-width: 2px;
            margin-bottom: 0.2rem;
        }
    }
    .academics-portal-desc {
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.35rem;
    }
    @media (min-width: 992px) {
        .academics-portal-desc {
            font-size: 0.82rem;
            line-height: 1.4;
            margin-bottom: 0.35rem;
        }
    }
    .academics-login-footer {
        margin-top: 1.25rem;
        padding-top: 0.75rem;
        border-top: 1px solid #e2e8f0;
        font-size: 0.75rem;
        color: #64748b;
    }
    @media (min-width: 992px) {
        .academics-login-footer {
            margin-top: 0.45rem;
            padding-top: 0.4rem;
            font-size: 0.72rem;
            line-height: 1.4;
        }
        .academics-powered {
            margin-top: 0.15rem;
            display: inline;
        }
    }
    .academics-login-footer a {
        color: #667eea;
        text-decoration: none;
        margin-left: 0.5rem;
    }
    .academics-login-footer a:hover { text-decoration: underline; }
    .academics-powered {
        margin-top: 0.5rem;
        margin-bottom: 0;
    }
    @media (max-width: 991px) {
        .academics-login-wrapper { flex-direction: column; overflow-y: auto; }
        .academics-login-left {
            flex: none;
            order: 2;
            min-height: 0;
            max-height: none;
            padding: 1rem;
            overflow: visible;
        }
        .academics-login-right {
            flex: none;
            order: 1;
            padding: 1.25rem 1rem;
            min-height: 0;
        }
        .academics-left-featured { flex: none; min-height: 240px; }
        .academics-left-featured-img {
            min-height: 220px;
            max-height: 55vh;
            height: auto;
            flex: none;
            object-fit: contain;
        }
        .academics-left-thumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 0.75rem;
        }
        .academics-left-thumb-card .name { font-size: 0.65rem; }
        .academics-login-left-img-wrap { min-height: 90px; padding: 0.75rem; margin-bottom: 0.75rem; }
        .academics-login-left-img { max-height: 16vh; }
        .academics-hero-title { font-size: 1.35rem; }
        .academics-hero-sub, .academics-hero-desc { font-size: 0.85rem; }
    }
    /* Fallback when :has() not supported - use JS to add class */
    body.academics-login-page,
    body.academics-login-page html { overflow: hidden !important; height: 100% !important; }
</style>
@endsection

@section('content')
{{-- Unified sign-in: achievements / brand left, form right --}}
<div class="academics-login-wrapper">
    <div class="academics-login-left">
        <div class="academics-login-left-inner">
            @if(isset($achievementMedia) && $achievementMedia->isNotEmpty())
            {{-- Carousel: same images as Achievements & Media Coverage – slides one after another --}}
            <div class="academics-left-featured" id="academicsLeftCarousel" data-total="{{ $achievementMedia->count() }}">
                <div class="academics-left-slides">
                    @foreach($achievementMedia as $index => $item)
                    <div class="academics-left-slide {{ $index === 0 ? 'academics-slide-active' : '' }}" data-academics-slide="{{ $index }}">
                        <img src="{{ storage_asset($item->image_path) ?? '#' }}" alt="{{ $item->caption ?? 'Achievement' }}" class="academics-left-featured-img" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22 viewBox=%220 0 400 200%22%3E%3Crect fill=%22%23f3f4f6%22 width=%22400%22 height=%22200%22/%3E%3Ctext fill=%22%239ca3af%22 font-size=%2216%22 x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22%3EAchievement%3C/text%3E%3C/svg%3E';">
                        <div class="academics-left-featured-caption">
                            <span class="name">{{ $item->caption ?? 'Achievements & Media' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="academics-left-thumbs" id="academicsLeftThumbs">
                @foreach($achievementMedia->take(6) as $index => $item)
                <button type="button" class="academics-left-thumb-card academics-thumb-btn {{ $index === 0 ? 'active' : '' }}" data-academics-goto="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}">
                    <img src="{{ storage_asset($item->image_path) ?? '#' }}" alt="" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22%3E%3Crect fill=%22%23e5e7eb%22 width=%22100%22 height=%22100%22/%3E%3C/svg%3E';">
                    <div class="name">{{ Str::limit($item->caption ?? 'Media', 12) }}</div>
                </button>
                @endforeach
            </div>
            @else
            <div class="academics-login-brand-stack">
                <div class="academics-login-left-img-wrap">
                    <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="academics-login-left-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="academics-login-left-img-placeholder" style="display:none;" aria-hidden="true">
                        <span class="academics-placeholder-icon"><i class="fas fa-heartbeat"></i></span>
                        <span class="academics-placeholder-text">MeD Miracle Health Care</span>
                    </div>
                </div>
                <div class="academics-hero-block">
                    <p class="academics-hero-kicker">MMHC portal</p>
                    <h3 class="academics-hero-title">MeD Miracle Health Care</h3>
                    <p class="academics-hero-sub">Care, community &amp; learning</p>
                    <p class="academics-hero-desc">One secure sign-in for patients, caregivers, staff, and academic roles. Your dashboard opens based on your account type.</p>
                </div>
                <div class="academics-badges">
                    <span class="academics-badge"><i class="fas fa-shield-heart" aria-hidden="true"></i> Trusted care</span>
                    <span class="academics-badge"><i class="fas fa-fingerprint" aria-hidden="true"></i> One secure sign-in</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="academics-login-right">
        <div class="academics-login-right-inner">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="academics-logo">
            <h1 class="academics-portal-title">Sign in</h1>
            <p class="academics-portal-desc">Sign in with SMS OTP on your mobile (default). Existing members may use email and password on the second tab.</p>
            <div class="academics-form-wrap">
                @include('auth::partials.login-form')
            </div>
            <footer class="academics-login-footer">
                <span>©{{ date('Y') }}, {{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
                <a href="{{ url('/') }}">Home</a>
                <p class="academics-powered">Powered by <strong>MeD Miracle</strong></p>
            </footer>
        </div>
    </div>
</div>

<script>
(function() {
    var w = document.querySelector('.academics-login-wrapper');
    if (w) { document.body.classList.add('academics-login-page'); }
})();

(function() {
    var carousel = document.getElementById('academicsLeftCarousel');
    if (!carousel) return;
    var slides = carousel.querySelectorAll('.academics-left-slide');
    var total = slides.length;
    if (total <= 1) return;
    var current = 0;
    var interval = 5000;

    function goToSlide(index) {
        current = (index + total) % total;
        slides.forEach(function(s, i) {
            s.classList.toggle('academics-slide-active', i === current);
        });
        var thumbs = document.querySelectorAll('#academicsLeftThumbs .academics-thumb-btn');
        thumbs.forEach(function(t, i) {
            t.classList.toggle('active', i === current);
        });
    }

    var tid = setInterval(function() { goToSlide(current + 1); }, interval);

    document.getElementById('academicsLeftThumbs') && document.getElementById('academicsLeftThumbs').addEventListener('click', function(e) {
        var btn = e.target.closest('.academics-thumb-btn');
        if (!btn || btn.getAttribute('data-academics-goto') === null) return;
        goToSlide(parseInt(btn.getAttribute('data-academics-goto'), 10));
        clearInterval(tid);
        tid = setInterval(function() { goToSlide(current + 1); }, interval);
    });
})();

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordToggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggleIcon.classList.remove('fa-eye');
        passwordToggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordToggleIcon.classList.remove('fa-eye-slash');
        passwordToggleIcon.classList.add('fa-eye');
    }
}

// Auto-focus email field on page load
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput && !emailInput.value) {
        emailInput.focus();
    }
});

// Add smooth form submission for email form
var loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        var submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...'; }
    });
}
// Activate phone tab on load if session says so
if (document.querySelector('#tab-phone.active')) {
    document.querySelector('#login_phone') && document.querySelector('#login_phone').focus();
}
if (document.getElementById('otp')) {
    document.getElementById('otp').focus();
}
</script>
@endsection

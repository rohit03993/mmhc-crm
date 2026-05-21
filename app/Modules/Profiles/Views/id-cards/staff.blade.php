@extends('profiles::id-cards.layout-print')

@section('title', $card['name'].' — Staff ID')

@push('head')
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    .id-print-body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background: linear-gradient(180deg, #e2e8f0 0%, #cbd5e1 100%);
        height: 100vh;
        height: 100dvh;
        max-height: 100dvh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        padding: 0.75rem 1rem;
    }

    .id-top {
        flex-shrink: 0;
        text-align: center;
        padding-bottom: 0.65rem;
    }

    .id-top h1 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .id-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
        max-width: 360px;
        margin: 0 auto;
        width: 100%;
    }

    .id-toolbar button,
    .id-toolbar a {
        flex: 1;
        min-width: 120px;
        padding: 0.55rem 0.9rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        border: none;
    }

    .id-btn-print {
        background: linear-gradient(135deg, #1d4ed8, #312e81);
        color: #fff;
    }

    .id-btn-back {
        background: #fff;
        color: #0f172a;
        border: 1px solid #cbd5e1 !important;
    }

    .id-card-stage {
        flex: 1;
        min-height: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 0.25rem 0;
    }

    @media print {
        .id-print-body {
            background: #fff;
            padding: 0;
            display: block;
            height: auto;
            max-height: none;
            overflow: visible;
        }
        .id-top { display: none !important; }
        .id-card-stage {
            padding: 0;
            display: block;
        }
    }
</style>
@include('profiles::id-cards.partials.card-styles', ['previewScale' => 1])
@endpush

@section('content')
@php
    $backUrl = auth()->user()->isAdmin() && auth()->id() !== $card['user']->id
        ? route('admin.profiles.view', $card['user'])
        : route('profile.index');
@endphp

<div class="id-top no-print">
    <h1>{{ $card['name'] }} · {{ $card['role_tag'] }}</h1>
    <div class="id-toolbar">
        <button type="button" class="id-btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ $backUrl }}" class="id-btn-back">Back</a>
    </div>
</div>

<div class="id-card-stage" id="idCardStage">
    <div class="id-card-scaler" id="idCardScaler">
        @include('profiles::id-cards.partials.card')
    </div>
</div>
@endsection

@push('scripts')
@include('profiles::id-cards.partials.qr-script')
<script>
(function () {
    var mmToPx = 96 / 25.4;
    var cardW = 85.6 * mmToPx;
    var cardH = 54 * mmToPx;

    function fitIdCard() {
        var stage = document.getElementById('idCardStage');
        var scaler = document.getElementById('idCardScaler');
        if (!stage || !scaler) return;

        var pad = 16;
        var scale = Math.min(
            2.5,
            (stage.clientWidth - pad) / cardW,
            (stage.clientHeight - pad) / cardH
        );
        scale = Math.max(scale, 1);

        scaler.style.setProperty('--id-preview-scale', scale);
        scaler.style.width = (cardW * scale) + 'px';
        scaler.style.height = (cardH * scale) + 'px';
    }

    fitIdCard();
    window.addEventListener('resize', fitIdCard);
})();
</script>
@endpush

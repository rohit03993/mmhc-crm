@php
    $accent = $card['accent'];
    $qrElementId = $qrElementId ?? 'idCardQr';
@endphp

<div class="id-card-wrap">
    <article class="id-card {{ $card['is_active'] ? '' : 'id-card--inactive' }}" id="staffIdCard">
        <header class="id-card__header">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="" class="id-card__logo">
            <div class="id-card__brand">
                <div class="id-card__brand-name">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</div>
                <div class="id-card__brand-tag">Authorized Healthcare Staff</div>
            </div>
            <span class="id-card__role id-card__role--{{ $accent }}">{{ $card['role_tag'] }}</span>
        </header>

        <div class="id-card__body">
            <div class="id-card__photo-col">
                @if($card['avatar_url'])
                    <img src="{{ $card['avatar_url'] }}" alt="{{ $card['name'] }}" class="id-card__photo">
                @else
                    <div class="id-card__initials id-card__initials--{{ $accent }}">{{ $card['initials'] }}</div>
                @endif
                <div class="id-card__qr" id="{{ $qrElementId }}" data-verify-url="{{ $card['verify_url'] }}"></div>
            </div>

            <div class="id-card__details">
                <h1 class="id-card__name">{{ $card['name'] }}</h1>
                <div class="id-card__uid">{{ $card['unique_id'] }}</div>
                <div class="id-card__row">
                    <span class="id-card__label">Mobile</span>
                    <span class="id-card__value">{{ $card['phone'] }}</span>
                </div>
                <div class="id-card__row">
                    <span class="id-card__label">DOB</span>
                    <span class="id-card__value">{{ $card['date_of_birth'] }}</span>
                </div>
                <div class="id-card__row">
                    <span class="id-card__label">Address</span>
                    <span class="id-card__value">{{ $card['address'] }}</span>
                </div>
            </div>
        </div>

        <footer class="id-card__footer">
            <span>Scan QR to <strong>verify</strong></span>
            <span>{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'themmhc.com' }}</span>
        </footer>
    </article>
</div>

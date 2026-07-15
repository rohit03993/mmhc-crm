{{-- Progressive Web App — head tags (manifest, iOS, theme) --}}
@php
    $pwaIconVersion = \App\Models\SiteSetting::get('pwa_icon_version');
    $pwaManifestUrl = url('/manifest.webmanifest') . ($pwaIconVersion ? '?v=' . urlencode((string) $pwaIconVersion) : '');
    $pwaAppleIcon = app(\App\Services\PwaIconService::class)->iconUrl(180);
    $pwaTheme = '#2E48A2';
@endphp
<link rel="manifest" href="{{ $pwaManifestUrl }}">
<meta name="theme-color" content="{{ $pwaTheme }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $siteCompanyName ?? 'MeD Miracle' }}">
<link rel="apple-touch-icon" href="{{ $pwaAppleIcon }}">
<link rel="stylesheet" href="{{ asset('css/pwa-install.css') }}?v=20260714b">

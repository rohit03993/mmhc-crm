{{-- Shared mobile assets for legacy Tailwind admin CMS pages (standalone HTML). --}}
@include('partials.pwa-head')
<link rel="stylesheet" href="{{ asset('css/mobile-crm.css') }}">
<link rel="stylesheet" href="{{ asset('css/capacitor-app.css') }}">
<script src="{{ asset('js/mobile-crm.js') }}" defer></script>
<script src="{{ asset('js/capacitor-app.js') }}" defer></script>
@include('partials.pwa-scripts')

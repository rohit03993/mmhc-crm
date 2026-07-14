{{-- Shared mobile assets for legacy Tailwind admin CMS pages (standalone HTML). --}}
@include('partials.pwa-head')
<link rel="stylesheet" href="{{ asset('css/mobile-crm.css') }}?v=20260714i">
<link rel="stylesheet" href="{{ asset('css/capacitor-app.css') }}">
<script>window.mmhcAdminDashboardUrl = @json(route('admin.dashboard'));</script>
<script src="{{ asset('js/mobile-crm.js') }}?v=20260714g" defer></script>
<script src="{{ asset('js/capacitor-app.js') }}" defer></script>
@include('partials.pwa-scripts')

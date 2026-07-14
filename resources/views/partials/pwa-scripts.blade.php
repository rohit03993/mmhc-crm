{{-- Progressive Web App — service worker + smart install prompt + Web Push --}}
<script src="{{ asset('js/pwa-install.js') }}?v=20260714i" defer></script>
@auth
<script src="{{ asset('js/pwa-push.js') }}?v=20260714i" defer></script>
@endauth

@php
    $qrElementId = $qrElementId ?? 'idCardQr';
@endphp
<script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
<script>
(function () {
    var el = document.getElementById(@json($qrElementId));
    if (!el) return;
    var url = el.getAttribute('data-verify-url');
    if (!url) return;

    function render() {
        if (el.querySelector('canvas')) return;
        var canvas = document.createElement('canvas');
        el.appendChild(canvas);
        new QRious({
            element: canvas,
            value: url,
            size: 132,
            level: 'M',
            background: '#ffffff',
            foreground: '#0f172a'
        });
    }

    if (typeof QRious === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js';
        s.onload = render;
        document.head.appendChild(s);
    } else {
        render();
    }
})();
</script>

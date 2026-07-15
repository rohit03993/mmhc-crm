<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install MeD Miracle App</title>
    @include('partials.pwa-head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        :root { --med-blue: #2E48A2; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: linear-gradient(160deg, #1e347a 0%, var(--med-blue) 45%, #3d63c7 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 20px;
            padding: 28px 24px 24px;
            text-align: center;
            backdrop-filter: blur(8px);
        }
        .card img {
            width: 88px;
            height: 88px;
            border-radius: 20px;
            object-fit: cover;
            background: #fff;
            margin-bottom: 16px;
        }
        .badge {
            display: none;
            margin: 0 auto 14px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #22c55e;
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 1.45rem;
            font-weight: 700;
        }
        p {
            margin: 0 0 20px;
            opacity: 0.92;
            line-height: 1.45;
            font-size: 0.95rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-primary {
            background: #fff;
            color: var(--med-blue);
        }
        .btn-ghost {
            margin-top: 10px;
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
        }
        .hint {
            margin-top: 16px;
            font-size: 0.8rem;
            opacity: 0.8;
        }
        body.is-installed .badge { display: inline-flex; }
        body.is-installed .btn-primary,
        body.is-installed .hint-install { display: none; }
        body.is-installed .hint-installed { display: block; }
        .hint-installed { display: none; }
    </style>
</head>
<body data-mmhc-pwa-autostart="1">
    <main class="card">
        <div class="badge" aria-hidden="true">✓</div>
        <img src="{{ app(\App\Services\PwaIconService::class)->iconUrl(192) }}" alt="MeD Miracle">
        <h1 id="mmhcInstallTitle">Install MeD Miracle</h1>
        <p id="mmhcInstallText">Add the app to your phone home screen for faster access — no Play Store wait.</p>
        <button type="button" class="btn btn-primary" id="mmhcInstallBtn" data-mmhc-pwa-install>
            Install / Download App
        </button>
        <a class="btn btn-ghost" href="{{ url('/dashboard') }}">Open dashboard</a>
        <a class="btn btn-ghost" href="{{ url('/') }}">Back to website</a>
        <p class="hint hint-install">On iPhone: use Share → Add to Home Screen. On Android Chrome: tap Install when prompted.</p>
        <p class="hint hint-installed">You’re all set — open MeD Miracle from your phone’s Home screen anytime.</p>
    </main>
    @include('partials.pwa-scripts')
    <script>
        (function () {
            function markInstalledUi() {
                document.body.classList.add('is-installed', 'mmhc-pwa-standalone');
                var title = document.getElementById('mmhcInstallTitle');
                var text = document.getElementById('mmhcInstallText');
                if (title) title.textContent = 'App already installed';
                if (text) text.textContent = 'MeD Miracle is already on this phone. Use the Home screen icon to open it — no need to install again.';
                document.body.removeAttribute('data-mmhc-pwa-autostart');
            }

            function isStandalone() {
                if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return true;
                if (window.matchMedia && window.matchMedia('(display-mode: minimal-ui)').matches) return true;
                if (typeof navigator.standalone === 'boolean' && navigator.standalone) return true;
                return false;
            }

            if (isStandalone()) {
                markInstalledUi();
            }

            window.addEventListener('mmhc-pwa-already-installed', markInstalledUi);

            // If Chrome knows the PWA is installed (browse mode), show the same message
            if (navigator.getInstalledRelatedApps) {
                navigator.getInstalledRelatedApps().then(function (apps) {
                    if (apps && apps.length) markInstalledUi();
                }).catch(function () {});
            }

            try {
                if (localStorage.getItem('mmhc_pwa_installed') === '1' && !document.body.classList.contains('is-installed')) {
                    // Soft hint only — install button still available if they removed the icon
                    var text = document.getElementById('mmhcInstallText');
                    if (text) {
                        text.textContent = 'Looks like you installed MeD Miracle before. If the Home screen icon is still there, use that. Otherwise tap Install again.';
                    }
                }
            } catch (e) {}
        })();
    </script>
</body>
</html>

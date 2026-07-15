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
        :root {
            --med-blue: #2E48A2;
        }
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
        body.mmhc-pwa-standalone .btn-primary {
            display: none;
        }
    </style>
</head>
<body data-mmhc-pwa-autostart="1">
    <main class="card">
        <img src="{{ app(\App\Services\PwaIconService::class)->iconUrl(192) }}" alt="MeD Miracle">
        <h1>Install MeD Miracle</h1>
        <p>Add the app to your phone home screen for faster access — no Play Store wait.</p>
        <button type="button" class="btn btn-primary" data-mmhc-pwa-install>
            Install / Download App
        </button>
        <a class="btn btn-ghost" href="{{ url('/') }}">Back to website</a>
        <p class="hint">On iPhone: use Share → Add to Home Screen. On Android Chrome: tap Install when prompted.</p>
    </main>
    @include('partials.pwa-scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify staff — {{ $card['unique_id'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo: #312e81;
            --blue: #1d4ed8;
            --slate: #0f172a;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(165deg, var(--indigo) 0%, var(--blue) 45%, var(--slate) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .verify-card {
            background: #fff;
            border-radius: 1.25rem;
            max-width: 420px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 24px 48px rgba(0,0,0,0.25);
        }
        .verify-header {
            background: linear-gradient(120deg, var(--indigo), var(--blue));
            color: #fff;
            padding: 1.25rem 1.5rem;
            text-align: center;
        }
        .verify-header img {
            height: 48px;
            margin-bottom: 0.5rem;
            background: #fff;
            border-radius: 8px;
            padding: 4px;
        }
        .verify-header h1 {
            font-size: 1rem;
            font-weight: 800;
        }
        .verify-header p {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }
        .verify-body {
            padding: 1.5rem;
            text-align: center;
        }
        .verify-photo {
            width: 96px;
            height: 96px;
            border-radius: 1rem;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 3px solid #e2e8f0;
        }
        .verify-initials {
            width: 96px;
            height: 96px;
            border-radius: 1rem;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
        }
        .verify-initials--nurse { background: linear-gradient(145deg, #3b82f6, #2563eb); }
        .verify-initials--caregiver { background: linear-gradient(145deg, #34d399, #059669); }
        .verify-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--slate);
            margin-bottom: 0.35rem;
        }
        .verify-uid {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--blue);
            margin-bottom: 0.75rem;
        }
        .verify-role {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }
        .verify-role--nurse { background: #dbeafe; color: #2563eb; }
        .verify-role--caregiver { background: #d1fae5; color: #059669; }
        .verify-status {
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .verify-status--ok {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .verify-status--bad {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .verify-footer {
            padding: 1rem 1.5rem 1.25rem;
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            border-top: 1px solid #f1f5f9;
        }
        .verify-footer a {
            color: var(--blue);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <header class="verify-header">
            <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}">
            <h1>{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</h1>
            <p>Staff identity verification</p>
        </header>

        <div class="verify-body">
            @if($card['avatar_url'])
                <img src="{{ $card['avatar_url'] }}" alt="" class="verify-photo">
            @else
                <div class="verify-initials verify-initials--{{ $card['accent'] }}">{{ $card['initials'] }}</div>
            @endif

            <h2 class="verify-name">{{ $card['name'] }}</h2>
            <div class="verify-uid">{{ $card['unique_id'] }}</div>
            <span class="verify-role verify-role--{{ $card['accent'] }}">{{ $card['role_tag'] }}</span>

            @if($verified)
                <div class="verify-status verify-status--ok">
                    ✓ Verified active MMHC staff member
                </div>
            @else
                <div class="verify-status verify-status--bad">
                    @if(empty($phoneVerified))
                        Mobile number is not verified. This ID card is not valid — staff must verify mobile on Profile (WhatsApp OTP) first.
                    @else
                        This staff account is not active. Contact MMHC if you have concerns.
                    @endif
                </div>
            @endif
        </div>

        <footer class="verify-footer">
            <p>Scan was generated from an official MMHC staff ID card.</p>
            <p class="mt-1"><a href="{{ url('/') }}">themmhc.com</a></p>
        </footer>
    </div>
</body>
</html>

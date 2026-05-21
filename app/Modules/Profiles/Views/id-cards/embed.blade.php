<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $card['name'] }} — ID preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 1.25rem;
            font-family: Inter, system-ui, sans-serif;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
    @include('profiles::id-cards.partials.card-styles', ['previewScale' => 2.15])
</head>
<body>
    <div class="id-card-scaler">
        @include('profiles::id-cards.partials.card', ['qrElementId' => 'idCardQrEmbed'])
    </div>
    @include('profiles::id-cards.partials.qr-script', ['qrElementId' => 'idCardQrEmbed'])
</body>
</html>

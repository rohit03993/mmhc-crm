<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>@yield('title') — Med Miracle Health Care</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0066CC 0%, #00A86B 100%); }
        .legal-prose h2 { margin-top: 2rem; margin-bottom: 0.75rem; font-size: 1.125rem; font-weight: 700; color: #1e293b; }
        .legal-prose h3 { margin-top: 1.25rem; margin-bottom: 0.5rem; font-size: 1rem; font-weight: 600; color: #334155; }
        .legal-prose p, .legal-prose li { color: #475569; line-height: 1.7; }
        .legal-prose ul { list-style: disc; padding-left: 1.25rem; margin: 0.75rem 0; }
        .legal-prose ul li { margin-bottom: 0.35rem; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-2 text-gray-800 font-semibold hover:text-blue-600 transition">
                <span class="w-9 h-9 rounded-lg gradient-bg flex items-center justify-center text-white text-sm font-bold">M</span>
                <span class="hidden sm:inline">Med Miracle Health Care</span>
            </a>
            <a href="{{ route('landing') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                <i class="fas fa-arrow-left me-1"></i> Back to home
            </a>
        </div>
    </header>

    <main class="flex-grow">
        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white py-6 mt-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-sm text-gray-400">
            <span>© {{ date('Y') }} Med Miracle Health Care (MMHC). All rights reserved.</span>
            <a href="{{ route('legal.privacy') }}" class="hover:text-white transition">Privacy Policy</a>
        </div>
    </footer>
</body>
</html>

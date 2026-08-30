<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Med Miracle Health Care - Your Health is Our Priority</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @include('partials.pwa-head')
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom gradient */
        .gradient-bg {
            background: linear-gradient(135deg, #0066CC 0%, #00A86B 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #0066CC 0%, #00A86B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Hover effects */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Landing plan cards — modern, soft, professional */
        .mmhc-plans-section {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 55%, #eef2f7 100%);
        }
        .mmhc-plan-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.35rem;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.045);
            padding: 1.85rem 1.4rem 1.5rem;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .mmhc-plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.09);
            border-color: #cbd5e1;
        }
        .mmhc-plan-card.is-popular {
            border-color: #99f6e4;
            box-shadow: 0 10px 32px rgba(15, 118, 110, 0.12);
            background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 100%);
        }
        .mmhc-plan-badge {
            position: absolute;
            top: -0.7rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #0f766e, #115e59);
            color: #f8fafc;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.35rem 0.95rem;
            border-radius: 999px;
            white-space: nowrap;
            box-shadow: 0 6px 14px rgba(15, 118, 110, 0.28);
        }
        .mmhc-plan-icon {
            width: 3.35rem;
            height: 3.35rem;
            margin: 0 auto 1rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #0f766e;
            font-size: 1.2rem;
        }
        .mmhc-plan-card.is-popular .mmhc-plan-icon {
            background: #ccfbf1;
            color: #0f766e;
        }
        .mmhc-plan-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.4rem;
            line-height: 1.3;
        }
        .mmhc-plan-desc {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 1.1rem;
            min-height: 2.75rem;
        }
        .mmhc-plan-tier-switch {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.35rem;
            padding: 0.3rem;
            margin-bottom: 1rem;
            background: #f1f5f9;
            border-radius: 0.85rem;
            border: 1px solid #e2e8f0;
        }
        .mmhc-plan-tier-btn {
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.2;
            padding: 0.55rem 0.35rem;
            border-radius: 0.65rem;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        .mmhc-plan-tier-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.7);
        }
        .mmhc-plan-tier-btn.is-active {
            background: #ffffff;
            color: #0f766e;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        }
        .mmhc-plan-tier-btn .tier-count {
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            color: inherit;
            margin-bottom: 0.08rem;
        }
        .mmhc-plan-covers {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            text-align: left;
            background: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 0.75rem;
            padding: 0.65rem 0.75rem;
            margin-bottom: 1rem;
            min-height: 3.1rem;
        }
        .mmhc-plan-covers i {
            color: #0f766e;
            margin-top: 0.15rem;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .mmhc-plan-covers-title {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f766e;
            margin-bottom: 0.15rem;
        }
        .mmhc-plan-covers-text {
            font-size: 0.8rem;
            color: #334155;
            line-height: 1.35;
            font-weight: 500;
        }
        .mmhc-plan-monthly-ref {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 0.85rem;
        }
        .mmhc-plan-term-summary {
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.45;
            text-align: left;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.65rem 0.75rem;
            margin-bottom: 1rem;
            min-height: 2.75rem;
        }
        .mmhc-plan-price {
            margin-bottom: 0.35rem;
        }
        .mmhc-plan-price-amount {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            transition: opacity 0.15s ease;
        }
        .mmhc-plan-price-duration {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        .mmhc-plan-members {
            font-size: 0.78rem;
            color: #0f766e;
            font-weight: 600;
            margin-bottom: 1.1rem;
            padding-bottom: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            min-height: 1.2rem;
        }
        .mmhc-plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem;
            text-align: left;
            flex: 1;
        }
        .mmhc-plan-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-bottom: 0.65rem;
            font-size: 0.84rem;
            color: #334155;
            line-height: 1.4;
        }
        .mmhc-plan-features i {
            color: #0f766e;
            margin-top: 0.2rem;
            font-size: 0.7rem;
            opacity: 0.9;
        }
        .mmhc-plan-cta {
            display: block;
            width: 100%;
            text-align: center;
            background: #0f766e;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.8rem 1.25rem;
            border-radius: 0.8rem;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }
        .mmhc-plan-cta:hover {
            background: #0d9488;
            box-shadow: 0 6px 16px rgba(15, 118, 110, 0.25);
            color: #ffffff !important;
        }
        .mmhc-plan-card.is-popular .mmhc-plan-cta {
            background: #115e59;
        }
        .mmhc-plan-card.is-popular .mmhc-plan-cta:hover {
            background: #0f766e;
        }
        .mmhc-plans-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2.75rem 0 1.75rem;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .mmhc-plans-divider::before,
        .mmhc-plans-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }
        .mmhc-plan-card.is-student {
            max-width: 22rem;
            margin-left: auto;
            margin-right: auto;
            border-color: #e2e8f0;
        }

        /* Mobile: one plan at a time, swipe sideways */
        .mmhc-plan-slider {
            position: relative;
        }
        .mmhc-plan-slider-track {
            display: flex;
            gap: 0.85rem;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scroll-padding-inline: 1rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            overscroll-behavior-x: contain;
            padding: 0.85rem 0 0.5rem;
            margin: 0 -1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .mmhc-plan-slider-track::-webkit-scrollbar {
            display: none;
        }
        .mmhc-plan-slide {
            flex: 0 0 86%;
            width: 86%;
            max-width: 22.5rem;
            scroll-snap-align: center;
            scroll-snap-stop: always;
            display: flex;
        }
        .mmhc-plan-slide .mmhc-plan-card {
            width: 100%;
        }
        .mmhc-plan-slider-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .mmhc-plan-slider-btn {
            width: 2.4rem;
            height: 2.4rem;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f766e;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }
        .mmhc-plan-slider-btn:disabled {
            opacity: 0.35;
            cursor: default;
        }
        .mmhc-plan-slider-dots {
            display: flex;
            gap: 0.4rem;
            align-items: center;
        }
        .mmhc-plan-slider-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            border: 0;
            padding: 0;
            background: #cbd5e1;
            cursor: pointer;
        }
        .mmhc-plan-slider-dot.is-active {
            width: 1.35rem;
            background: #0f766e;
        }
        .mmhc-plan-slider-hint {
            text-align: center;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.45rem;
            font-weight: 500;
        }
        @media (min-width: 768px) {
            .mmhc-plan-slider-track {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                overflow: visible;
                scroll-snap-type: none;
                margin: 0;
                padding: 0.85rem 0 0;
                gap: 1.75rem;
            }
            .mmhc-plan-slide {
                flex: none;
                width: auto;
                max-width: none;
            }
            .mmhc-plan-slider-nav,
            .mmhc-plan-slider-hint {
                display: none;
            }
        }
        @media (min-width: 1280px) {
            .mmhc-plan-slider-track {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* Achievements & Media: images from Admin → Achievements & Media only; large section */
        .achievement-media-section {
            margin-bottom: 4rem;
        }
        .achievement-media-inner {
            width: 100%;
        }
        .achievement-media-carousel {
            position: relative;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #f3f4f6;
            overflow: hidden;
            min-height: 420px;
            height: 520px;
        }
        .achievement-media-main-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 0.5rem;
        }
        .achievement-media-thumb {
            width: 100px;
            height: 72px;
            display: block;
            background: #f3f4f6;
        }
        @media (min-width: 768px) {
            .achievement-media-carousel { min-height: 480px; height: 560px; }
            .achievement-media-thumb { width: 120px; height: 84px; }
        }
        @media (max-width: 767px) {
            .achievement-media-carousel { min-height: 340px; height: 400px; }
        }

        [x-cloak] { display: none !important; }

        @media (max-width: 767px) {
            .text-5xl { font-size: 2rem !important; line-height: 1.25 !important; }
            .text-6xl { font-size: 2.25rem !important; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile-crm.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mmhc-public-mobile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/capacitor-app.css') }}">
</head>
<body class="bg-gray-50 mmhc-landing-page">

    <!-- NAVIGATION BAR -->
    <nav class="fixed w-full bg-white shadow-md z-50 mmhc-landing-nav relative" id="mmhcLandingNav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mmhc-landing-nav__inner flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center min-w-0 flex-1">
                    <a href="#home" class="flex items-center min-w-0">
                        <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo-mobile h-12 w-auto md:h-12">
                        <span class="sr-only">{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition">Home</a>
                    <a href="#plans" class="text-gray-700 hover:text-blue-600 font-medium transition">Plans</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 font-medium transition">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium transition">Contact</a>
                    <a href="{{ route('pwa.install') }}" class="text-blue-700 hover:text-blue-800 font-semibold transition mmhc-pwa-install-nav">Install App</a>
                </div>
                
                <!-- Login Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('auth.login') }}" class="px-5 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition">
                        Login
                    </a>
                    <a href="{{ route('auth.register') }}" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg hover:shadow-lg transition">
                        Register
                    </a>
                </div>
                
                <!-- Mobile Menu Button (vanilla JS — reliable in Android WebView) -->
                <button type="button"
                        id="mmhcMobileMenuBtn"
                        class="md:hidden text-gray-700 mmhc-mobile-menu-btn"
                        aria-expanded="false"
                        aria-controls="mmhcMobileMenuPanel">
                    <i class="fas fa-bars text-2xl" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu (Tailwind + mmhc classes — reliable in browser + Capacitor WebView) -->
        <div id="mmhcMobileMenuPanel" class="md:hidden hidden mmhc-landing-mobile-menu bg-white border-t border-gray-200" aria-hidden="true">
            <div class="mmhc-landing-mobile-menu__inner px-4 py-4 space-y-1">
                <a href="#home" class="mmhc-landing-mobile-menu__link flex items-center text-gray-700 hover:text-blue-600 font-medium rounded-xl px-3 py-3">
                    <i class="fas fa-home mr-3 w-5 text-center opacity-75" aria-hidden="true"></i>Home
                </a>
                <a href="#plans" class="mmhc-landing-mobile-menu__link flex items-center text-gray-700 hover:text-blue-600 font-medium rounded-xl px-3 py-3">
                    <i class="fas fa-heartbeat mr-3 w-5 text-center opacity-75" aria-hidden="true"></i>Plans
                </a>
                <a href="#about" class="mmhc-landing-mobile-menu__link flex items-center text-gray-700 hover:text-blue-600 font-medium rounded-xl px-3 py-3">
                    <i class="fas fa-info-circle mr-3 w-5 text-center opacity-75" aria-hidden="true"></i>About
                </a>
                <a href="#contact" class="mmhc-landing-mobile-menu__link flex items-center text-gray-700 hover:text-blue-600 font-medium rounded-xl px-3 py-3">
                    <i class="fas fa-envelope mr-3 w-5 text-center opacity-75" aria-hidden="true"></i>Contact
                </a>
                <a href="{{ route('pwa.install') }}" class="mmhc-landing-mobile-menu__link flex items-center text-blue-700 hover:text-blue-800 font-semibold rounded-xl px-3 py-3 mmhc-pwa-install-nav">
                    <i class="fas fa-mobile-alt mr-3 w-5 text-center opacity-75" aria-hidden="true"></i>Install App
                </a>
                <div class="mmhc-landing-mobile-menu__actions pt-3 mt-2 border-t border-gray-200 space-y-2">
                    <a href="{{ route('auth.login') }}" class="block w-full text-center px-5 py-3 text-blue-600 border-2 border-blue-600 rounded-xl font-semibold hover:bg-blue-50 transition">
                        Login
                    </a>
                    <a href="{{ route('auth.register') }}" class="block w-full text-center px-5 py-3 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-xl font-semibold hover:shadow-lg transition">
                        Create account
                    </a>
                </div>
            </div>
        </div>
        <div id="mmhcMobileMenuBackdrop" class="mmhc-landing-mobile-menu-backdrop md:hidden hidden" aria-hidden="true"></div>
    </nav>

    <!-- HERO SECTION -->
    <section id="home" class="mmhc-landing-hero pt-32 pb-20 gradient-bg relative overflow-hidden">
        <!-- Animated Background Shapes -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Side - Content -->
                <div class="text-white fade-in-up">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        @if(isset($pageContent['hero']))
                            {!! nl2br(e($pageContent['hero']->title)) !!}
                        @else
                            Your Health,<br>
                            <span class="text-yellow-300">Our Priority</span>
                        @endif
                    </h1>
                    <p class="text-xl mb-8 text-gray-100">
                        @if(isset($pageContent['hero']))
                            {{ $pageContent['hero']->subtitle }}
                        @else
                            Professional Healthcare Services at Your Doorstep. Connect with certified caregivers and access quality healthcare plans.
                        @endif
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-10">
                        <a href="{{ route('auth.register') }}?role=patient" class="px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold hover:shadow-xl transition transform hover:scale-105 text-center">
                            <i class="fas fa-user mr-2"></i>I'm a Patient
                        </a>
                        <a href="{{ route('auth.register') }}?warrior=1" class="px-8 py-4 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:shadow-xl transition transform hover:scale-105 text-center">
                            <i class="fas fa-user-nurse mr-2"></i>I'm a Nursing Warrior
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap gap-8 text-white">
                        <div>
                            <div class="text-3xl font-bold">10,000+</div>
                            <div class="text-sm text-gray-200">Successful Outcomes</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">5 Cities</div>
                            <div class="text-sm text-gray-200">Service Locations</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">24/7</div>
                            <div class="text-sm text-gray-200">Home Care Available</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Image/Illustration (desktop only) -->
                <div class="relative fade-in-up hidden lg:block mmhc-landing-hero-visual">
                    <!-- Main Image Container -->
                    <div class="relative z-10 bg-white rounded-3xl shadow-2xl p-4">
                        <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=600&h=600&fit=crop" 
                             alt="Healthcare Professional" 
                             class="rounded-2xl w-full">
                    </div>
                    
                    <!-- Floating Cards - Better positioned -->
                    <div class="absolute top-8 left-8 bg-white p-3 rounded-xl shadow-lg border border-gray-100 hidden lg:block">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">100%</div>
                                <div class="text-xs text-gray-600">Verified</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute bottom-8 right-8 bg-white p-3 rounded-xl shadow-lg border border-gray-100 hidden lg:block">
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                                <i class="fas fa-star text-white"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">4.9★</div>
                                <div class="text-xs text-gray-600">Rating</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Background decoration -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 to-green-100 rounded-3xl -z-10 transform rotate-3"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT US SECTION -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    About <span class="gradient-text">Med Miracle Health Care</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Founded by Mantu Kumar with a vision to make quality healthcare accessible and affordable. India's newest home nursing subscription service with 10,000+ successful patient outcomes.
                </p>
            </div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-20">
                <!-- Left Side - Story -->
                <div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">Our Story</h3>
                    <div class="space-y-6 text-gray-600 leading-relaxed">
                        <p>
                            Meet our founder, Mantu Kumar, the visionary behind Med Miracle Health Care. With a deep commitment to holistic well-being, Mantu Kumar has built the company into a leader in personalized home healthcare.
                        </p>
                        <p>
                            We at Med Miracle Healthcare are committed to making quality healthcare accessible and affordable. What sets us apart is our unique subscription model - starting at just Rs 999/month - that provides comprehensive nursing care at home, regular checkups, and body-mind relaxation sessions.
                        </p>
                        <p>
                            His dedication to providing compassionate, evidence-based care has led to over 10,000 successful patient outcomes, establishing Med Miracle Health Care as a trusted name across Patna, Ranchi, Bhopal, Noida, and Gurgaon. We're proud to be India's first largest superhero nursing association.
                        </p>
                    </div>
                </div>
                
                <!-- Right Side - Image -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=400&fit=crop" 
                         alt="MMHC Team" 
                         class="rounded-2xl shadow-2xl w-full">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-900/20 to-transparent rounded-2xl"></div>
                </div>
            </div>
            
            <!-- Mission & Vision -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-20">
                <!-- Mission -->
                <div class="bg-gradient-to-br from-blue-50 to-green-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bullseye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                    <p class="text-gray-600 leading-relaxed">
                        We at Med Miracle Healthcare are committed to making quality healthcare accessible and affordable through our monthly subscription plans. We provide nursing care at home, regular checkups, mind-body relaxation sessions and much more. Our mission is to solve the problems of non-empathetic healthcare, expensive medical equipment, poor medical environment, and incompetent staff by providing personalized, compassionate care at your doorstep.
                    </p>
                </div>
                
                <!-- Vision -->
                <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600 leading-relaxed">
                        To become India's leading home healthcare provider, expanding our services beyond Patna, Ranchi, Bhopal, Noida, and Gurgaon to more cities. We envision a future where every family has access to affordable, professional healthcare services at home, with trained nursing staff who understand both the medical and psychological needs of patients.
                    </p>
                </div>
            </div>

            <!-- Achievements & Media Coverage: only images added in Admin → Achievements & Media (no logo) -->
            @if(isset($achievementMedia) && $achievementMedia->isNotEmpty())
            <div id="achievement-media" class="achievement-media-section scroll-mt-24">
                <h3 class="text-3xl font-bold text-gray-800 text-center mb-8">Achievements & Media Coverage</h3>
                <div class="max-w-7xl mx-auto px-2 achievement-media-inner"
                     x-data="{
                         active: 0,
                         total: {{ $achievementMedia->count() }},
                         next() { this.active = (this.active + 1) % this.total },
                         prev() { this.active = (this.active - 1 + this.total) % this.total }
                     }"
                     x-init="const advance = () => $data.next(); setInterval(advance, 5000)">
                    <div class="achievement-media-carousel">
                        @foreach($achievementMedia as $index => $item)
                        <div x-show="active === {{ $index }}"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0 flex items-center justify-center p-3">
                            <img src="{{ storage_asset($item->image_path) ?? 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect fill="#f3f4f6" width="400" height="300"/><text fill="#9ca3af" font-family="sans-serif" font-size="18" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle">No image</text></svg>') }}" alt="{{ $item->caption ?? 'Achievement' }}" class="achievement-media-main-img">
                            @if($item->caption)
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-4 py-2 rounded-b-2xl">
                                <p class="text-white font-medium text-sm text-center">{{ $item->caption }}</p>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <button @click="prev()" class="p-2 rounded-full bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-600 transition" aria-label="Previous">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="flex gap-2 items-center flex-wrap justify-center">
                            @foreach($achievementMedia as $index => $item)
                            <button @click="active = {{ $index }}" :class="active === {{ $index }} ? 'ring-2 ring-blue-600 ring-offset-1' : 'opacity-70 hover:opacity-100'" class="achievement-media-thumb rounded-lg overflow-hidden border border-gray-200 flex-shrink-0 transition" aria-label="Go to slide {{ $index + 1 }}">
                                <img src="{{ storage_asset($item->image_path) ?? 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="60" viewBox="0 0 80 60"><rect fill="#f3f4f6" width="80" height="60"/></svg>') }}" alt="" class="w-full h-full object-cover pointer-events-none">
                            </button>
                            @endforeach
                        </div>
                        <button @click="next()" class="p-2 rounded-full bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-600 transition" aria-label="Next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Values -->
            <div class="mb-20">
                <h3 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Core Values</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heart text-blue-600 text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Compassion</h4>
                        <p class="text-gray-600 text-sm">Treating every patient with empathy and understanding</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Excellence</h4>
                        <p class="text-gray-600 text-sm">Maintaining the highest standards in all our services</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Integrity</h4>
                        <p class="text-gray-600 text-sm">Building trust through transparency and honesty</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-lightbulb text-orange-600 text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Innovation</h4>
                        <p class="text-gray-600 text-sm">Continuously improving through technology and creativity</p>
                    </div>
                </div>
            </div>
            
            <!-- Awards & Achievements -->
            <div class="mb-20">
                <h3 class="text-3xl font-bold text-gray-800 text-center mb-12">Awards & Recognition</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Award 1 -->
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center hover-lift">
                        <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 mb-2">Indian Icon of the Year</h4>
                        <p class="text-gray-600 text-sm">Recognized for outstanding contribution to healthcare</p>
                    </div>
                    
                    <!-- Award 2 -->
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center hover-lift">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-award text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 mb-2">India Excellence Award</h4>
                        <p class="text-gray-600 text-sm">Fastest growing provider in healthcare segment</p>
                    </div>
                    
                    <!-- Award 3 -->
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center hover-lift">
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-globe text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 mb-2">International Excellence Award</h4>
                        <p class="text-gray-600 text-sm">Powered by ACS for global healthcare standards</p>
                    </div>
                    
                    <!-- Award 4 -->
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center hover-lift">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-star text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 mb-2">Best Home Nursing Services</h4>
                        <p class="text-gray-600 text-sm">Leading in home healthcare delivery</p>
                    </div>
                    
                    <!-- Award 5 -->
                    <div class="bg-white rounded-lg shadow-lg p-6 text-center hover-lift">
                        <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-heartbeat text-white text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gray-800 mb-2">Global Healthcare & Wellness Award</h4>
                        <p class="text-gray-600 text-sm">Excellence in holistic healthcare approach</p>
                    </div>
                    
                    <!-- Milestone -->
                    <div class="bg-gradient-to-br from-blue-500 to-green-500 rounded-lg shadow-lg p-6 text-center hover-lift text-white">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold mb-2">10,000+ Success Stories</h4>
                        <p class="text-sm">Successful patient outcomes across 5 cities</p>
                    </div>
                </div>
            </div>
            
            <!-- Founder Section -->
            <div class="text-center">
                <h3 class="text-3xl font-bold text-gray-800 mb-12">Meet Our Founder</h3>
                <div class="max-w-2xl mx-auto">
                    <div class="bg-gradient-to-br from-blue-50 to-green-50 rounded-2xl shadow-xl p-8">
                        @php $founderImagePath = \App\Models\SiteSetting::get('founder_image_path'); @endphp
                        <img src="{{ ($founderImagePath && storage_asset($founderImagePath)) ? storage_asset($founderImagePath) : 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=200&h=200&fit=crop&crop=face' }}" 
                             alt="Mantu Kumar - Founder" 
                             class="w-32 h-32 rounded-full mx-auto mb-6 object-cover border-4 border-white shadow-lg">
                        <h4 class="text-3xl font-bold text-gray-800 mb-3">Mantu Kumar</h4>
                        <p class="text-blue-600 font-semibold text-xl mb-4">Founder & Visionary</p>
                        <p class="text-gray-700 leading-relaxed mb-6">
                            With a deep commitment to holistic well-being, Mantu Kumar has built Med Miracle Health Care into a leader in personalized home healthcare. His dedication to providing compassionate, evidence-based care has led to over <strong>10,000 successful patient outcomes</strong>, establishing MMHC as a trusted name in the industry.
                        </p>
                        <div class="flex flex-wrap justify-center gap-4 text-sm text-gray-600">
                            <span class="bg-white px-4 py-2 rounded-full shadow">🏆 Multiple Award Winner</span>
                            <span class="bg-white px-4 py-2 rounded-full shadow">💼 BNI Member</span>
                            <span class="bg-white px-4 py-2 rounded-full shadow">Entrepreneur</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- COMMUNITY PREVIEW SECTION -->
    <section id="community-preview" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Latest From Our <span class="gradient-text">Community</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Real updates from our care network. Login to view all posts, react, comment, and be part of the MMHC community.
                </p>
                @if(($communityPostsCount ?? 0) > 0)
                    <p class="mt-3 text-sm font-semibold text-blue-700">
                        {{ number_format($communityPostsCount) }}+ community posts and growing daily
                    </p>
                @endif
            </div>

            @if(isset($latestCommunityPosts) && $latestCommunityPosts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($latestCommunityPosts as $post)
                        <div class="bg-gray-50 rounded-2xl shadow-lg hover-lift p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-sm text-gray-500">
                                    <span class="font-semibold text-gray-700">{{ $post->user->name ?? 'MMHC Member' }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ ucfirst($post->post_type) }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $post->created_at->diffForHumans() }}</span>
                            </div>

                            @if($post->post_type === 'event')
                                <div class="mb-3 text-sm font-semibold text-purple-700">
                                    <i class="fas fa-calendar-alt mr-1"></i>{{ $post->event_title ?: 'Upcoming Community Event' }}
                                </div>
                            @endif

                            @if($post->image_path)
                                @php
                                    $homeCommunityImageUrl = storage_url($post->image_path) ?? storage_asset($post->image_path);
                                    $homeCommunityFallback = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="640" height="240"><rect width="100%" height="100%" fill="#f1f5f9"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#64748b" font-family="Arial" font-size="22">Image unavailable</text></svg>');
                                @endphp
                                <img src="{{ $homeCommunityImageUrl }}" alt="Community post image" class="w-full h-40 object-cover rounded-lg mb-3" onerror="this.onerror=null;this.src='{{ $homeCommunityFallback }}';">
                            @endif

                            <p class="text-gray-700 leading-relaxed">
                                {{ \Illuminate\Support\Str::limit($post->content ?: 'Community update posted by MMHC team.', 150) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-gray-50 rounded-2xl p-10 text-center shadow-lg">
                    <i class="fas fa-users text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Community Feed is Live</h3>
                    <p class="text-gray-600 max-w-2xl mx-auto">
                        We are actively building conversations among patients, caregivers, and nursing warriors. Login to explore and participate.
                    </p>
                </div>
            @endif

            <div class="text-center mt-10">
                <a href="{{ route('auth.login') }}" class="inline-block bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    Login to View All Posts
                </a>
                <p class="text-sm text-gray-500 mt-3">
                    New here?
                    <a href="{{ route('auth.register') }}" class="text-blue-600 font-semibold hover:text-blue-700">Create account and join community</a>
                </p>
            </div>
        </div>
    </section>

    <!-- STAR PERFORMERS SECTION (Meet Our Expert Nursing Team) -->
    @if(isset($featuredTeam) && $featuredTeam->isNotEmpty())
    <section id="star-performers" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Meet Our <span class="gradient-text">Expert Nursing Team</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Trained & verified nurses and attendants providing all-round medical support with compassion and professionalism. हम समझते हैं आपकी हर जरूरत।
                </p>
            </div>

            <div class="relative" x-data="{ currentSlide: 0, totalSlides: {{ $featuredTeam->count() }} }">
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out" :style="`transform: translateX(-${currentSlide * 100}%)`">
                        @foreach($featuredTeam as $member)
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center">
                                <div class="relative mb-6">
                                    @if($member->image_path && storage_asset($member->image_path))
                                        <img src="{{ storage_asset($member->image_path) }}" alt="{{ $member->name }}" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&h=300&fit=crop&crop=face" alt="{{ $member->name }}" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    @endif
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Verified
                                        </div>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $member->name }}</h3>
                                @if($member->title)
                                <p class="text-blue-600 font-semibold mb-3">{{ $member->title }}</p>
                                @endif
                                @if($member->rating !== null || $member->reviews_count !== null)
                                <div class="flex justify-center items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        @for($s = 1; $s <= 5; $s++)<i class="fas fa-star"></i>@endfor
                                    </div>
                                    <span class="text-gray-600 font-semibold">{{ $member->rating ? number_format((float)$member->rating, 1) : '—' }}@if($member->reviews_count) ({{ $member->reviews_count }} reviews)@endif</span>
                                </div>
                                @endif
                                @if($member->bio)
                                <p class="text-gray-600 mb-6 leading-relaxed">"{{ $member->bio }}"</p>
                                @endif
                                @if(!empty($member->skills_array))
                                <div class="flex flex-wrap justify-center gap-2 mb-6">
                                    @php $skillClasses = ['bg-blue-100 text-blue-800', 'bg-green-100 text-green-800', 'bg-purple-100 text-purple-800', 'bg-red-100 text-red-800', 'bg-orange-100 text-orange-800', 'bg-pink-100 text-pink-800', 'bg-yellow-100 text-yellow-800']; @endphp
                                    @foreach($member->skills_array as $si => $skill)
                                    <span class="{{ $skillClasses[$si % 7] }} px-3 py-1 rounded-full text-sm">{{ trim($skill) }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button @click="currentSlide = (currentSlide - 1 + totalSlides) % totalSlides" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <button @click="currentSlide = (currentSlide + 1) % totalSlides" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="i in totalSlides" :key="i">
                        <button @click="currentSlide = i - 1" :class="currentSlide === i - 1 ? 'bg-blue-600' : 'bg-gray-300'" class="w-3 h-3 rounded-full transition"></button>
                    </template>
                </div>
            </div>

            <div class="text-center mt-12">
                <p class="text-gray-600 mb-6">Want to see more of our amazing caregivers?</p>
                <a href="{{ route('auth.register') }}?warrior=1" class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    Join Our Team
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- WHY CHOOSE MMHC SECTION -->
    <section id="why-choose" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Why Choose <span class="gradient-text">Med Miracle Health Care?</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    India's first largest superhero nursing association providing comprehensive home healthcare with an empathetic approach. We solve problems of expensive equipment, poor medical environment, and incompetent staff.
                </p>
            </div>
            
            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-clock text-blue-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">24x7 Home Health Care</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Round-the-clock personal nursing staff assistance at your doorstep. Free services with quick & easy booking.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-spa text-green-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Body-Mind Relaxation Therapy</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Unique holistic approach with advanced wellness equipment including full body massager, foot reflexology, brain & heart function monitoring at home.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-user-nurse text-purple-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Expert Nursing Staff</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Well-experienced, trained & verified nursing staff for critical patients who understand psychological needs and provide all-round medical support.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-yellow-200 transition-colors">
                        <i class="fas fa-hands-helping text-yellow-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Professional Caretaker Services</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Compassionate caretaker services at home with an empathetic approach, ensuring personal attention to every detail of patient care.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-red-200 transition-colors">
                        <i class="fas fa-baby text-red-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Special Care Programs</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dedicated support for pregnant ladies & newborns, plus regular check-ups and specialized care for senior citizens.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-orange-200 transition-colors">
                        <i class="fas fa-rupee-sign text-orange-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Affordable Subscriptions</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Starting at just Rs 999/month. All-inclusive subscription plans with no expensive equipment costs - everything provided by us.
                    </p>
                </div>
                
            </div>
            
            <!-- Stats Section -->
            <div class="mt-20 bg-white rounded-2xl shadow-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-blue-600 mb-2">10,000+</div>
                        <div class="text-gray-600 font-semibold">Successful Outcomes</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">₹999</div>
                        <div class="text-gray-600 font-semibold">Starting Plan/Month</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-purple-600 mb-2">5 Cities</div>
                        <div class="text-gray-600 font-semibold">Service Locations</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-orange-600 mb-2">24/7</div>
                        <div class="text-gray-600 font-semibold">Home Care Available</div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom CTA -->
            <div class="text-center mt-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Ready to Experience Affordable Home Healthcare?</h3>
                <p class="text-gray-600 mb-8">Join 10,000+ satisfied patients who trust Med Miracle Health Care. Starting at just Rs 999/month.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('auth.register') }}?role=patient" class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        Subscribe Now
                    </a>
                    <a href="#contact" class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- PLANS SECTION -->
    <section id="plans" class="py-20 mmhc-plans-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4">
                    Subscription <span class="gradient-text">Plans</span>
                </h2>
                <p class="text-lg md:text-xl text-slate-500 max-w-3xl mx-auto leading-relaxed">
                    Choose who is covered — 1, 2, 3, or 4 members. After you start, we’ll collect member details and then you pick how to pay (6 months, 1 year, or 3 years).
                </p>
            </div>

            <!-- Household packages: swipe on mobile, grid on desktop -->
            @php $carePackageCount = count($carePackages ?? []); @endphp
            <div
                class="mmhc-plan-slider"
                x-data="{
                    slide: 0,
                    total: {{ $carePackageCount }},
                    go(index) {
                        this.slide = Math.max(0, Math.min(this.total - 1, index));
                        const track = this.$refs.track;
                        if (!track) return;
                        const item = track.children[this.slide];
                        if (item) item.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    },
                    syncFromScroll() {
                        const track = this.$refs.track;
                        if (!track || window.innerWidth >= 768) return;
                        const center = track.scrollLeft + track.clientWidth / 2;
                        let best = 0;
                        let bestDist = Infinity;
                        Array.from(track.children).forEach((el, i) => {
                            const mid = el.offsetLeft + el.offsetWidth / 2;
                            const dist = Math.abs(mid - center);
                            if (dist < bestDist) { bestDist = dist; best = i; }
                        });
                        this.slide = best;
                    }
                }"
            >
                <div
                    class="mmhc-plan-slider-track"
                    x-ref="track"
                    @scroll.passive.debounce.50ms="syncFromScroll()"
                >
                @foreach(($carePackages ?? []) as $pack)
                    @php
                        $registerBase = route('auth.register');
                        $ctaHref = $registerBase.'?role=patient&tier='.$pack['slug'];
                        $startingYearly = $pack['terms'][1]['price_label'] ?? null;
                    @endphp
                    <div class="mmhc-plan-slide">
                    <div class="mmhc-plan-card {{ !empty($pack['popular']) ? 'is-popular' : '' }}">
                        @if(!empty($pack['popular']))
                            <span class="mmhc-plan-badge">{{ $pack['popular_label'] ?? 'Most Popular' }}</span>
                        @endif

                        <div class="text-center flex flex-col flex-1">
                            <div class="mmhc-plan-icon">
                                <i class="fas {{ $pack['icon'] ?? 'fa-users' }}"></i>
                            </div>
                            <h3 class="mmhc-plan-title">{{ $pack['name'] }}</h3>
                            <p class="mmhc-plan-monthly-ref">From {{ $pack['monthly_label'] }}</p>

                            <div class="mmhc-plan-covers">
                                <i class="fas fa-users" aria-hidden="true"></i>
                                <div>
                                    <span class="mmhc-plan-covers-title">{{ $pack['members'] }}</span>
                                    <span class="mmhc-plan-covers-text">{{ $pack['covers'] }}</span>
                                </div>
                            </div>

                            <div class="mmhc-plan-price">
                                <span class="mmhc-plan-price-amount">{{ $pack['monthly_label'] ? str_replace('/month', '', $pack['monthly_label']) : '' }}</span>
                                <span class="mmhc-plan-price-duration">/month</span>
                            </div>
                            <p class="mmhc-plan-members">
                                @if($startingYearly)
                                    Example: {{ $startingYearly }}/year · or choose 6 months / 3 years at checkout
                                @else
                                    Choose 6 months, 1 year, or 3 years at checkout
                                @endif
                            </p>

                            <ul class="mmhc-plan-features">
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $pack['covers'] }}</span>
                                </li>
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>Add member name &amp; age after you start</span>
                                </li>
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>Then pick payment: 6 months, 1 year, or 3 years</span>
                                </li>
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>Home &amp; regular care visits as per care schedule</span>
                                </li>
                                <li>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>Free booking opens from month 4</span>
                                </li>
                            </ul>

                            <a href="{{ $ctaHref }}" class="mmhc-plan-cta">
                                {{ $pack['button_text'] ?? 'Get Started' }}
                            </a>
                        </div>
                    </div>
                    </div>
                @endforeach
                </div>

                @if($carePackageCount > 1)
                    <div class="mmhc-plan-slider-nav" aria-label="Plan slides">
                        <button type="button" class="mmhc-plan-slider-btn" @click="go(slide - 1)" :disabled="slide === 0" aria-label="Previous package">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        </button>
                        <div class="mmhc-plan-slider-dots">
                            @for($i = 0; $i < $carePackageCount; $i++)
                                <button
                                    type="button"
                                    class="mmhc-plan-slider-dot"
                                    :class="{ 'is-active': slide === {{ $i }} }"
                                    @click="go({{ $i }})"
                                    aria-label="Show package {{ $i + 1 }}"
                                ></button>
                            @endfor
                        </div>
                        <button type="button" class="mmhc-plan-slider-btn" @click="go(slide + 1)" :disabled="slide === total - 1" aria-label="Next package">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p class="mmhc-plan-slider-hint">Swipe for the next household package</p>
                @endif
            </div>

            @if($healthcarePlans->isNotEmpty())
                <div class="mmhc-plans-divider">For students</div>
                <div class="grid grid-cols-1 gap-8">
                    @foreach($healthcarePlans as $plan)
                        <div class="mmhc-plan-card is-student {{ $plan->is_popular ? 'is-popular' : '' }}">
                            @if($plan->is_popular)
                                <span class="mmhc-plan-badge">
                                    {{ $plan->popular_label ?: 'Most Popular' }}
                                </span>
                            @endif

                            <div class="text-center flex flex-col flex-1">
                                <div class="mmhc-plan-icon">
                                    <i class="fas {{ $plan->icon_class ?? 'fa-graduation-cap' }}"></i>
                                </div>
                                <h3 class="mmhc-plan-title">{{ $plan->name }}</h3>
                                <p class="mmhc-plan-desc">{{ $plan->description }}</p>
                                <div class="mmhc-plan-price">
                                    <span class="mmhc-plan-price-amount">{{ $plan->formatted_price }}</span>
                                    <span class="mmhc-plan-price-duration">{{ $plan->duration_text }}</span>
                                </div>
                                <p class="mmhc-plan-members">Students only · one-time membership</p>

                                <ul class="mmhc-plan-features">
                                    @foreach($plan->features as $feature)
                                        <li>
                                            <i class="fas fa-check" aria-hidden="true"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @php
                                    $planCtaBase = $plan->button_link ?: route('auth.register');
                                    $planRole = $plan->isStudentPlan() ? 'student' : 'patient';
                                    $planSlug = $plan->slug ?? strtolower(str_replace(' ', '_', $plan->name));
                                    $planCtaHref = $planCtaBase.(str_contains($planCtaBase, '?') ? '&' : '?').'role='.$planRole.'&plan='.$planSlug;
                                @endphp
                                <a href="{{ $planCtaHref }}" class="mmhc-plan-cta">
                                    {{ $plan->button_text ?? 'Subscribe now' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="text-center mt-12">
                <p class="text-slate-500 mb-3">Not sure which plan is right for you?</p>
                <a href="#contact" class="text-teal-700 hover:text-teal-800 font-semibold">
                    Contact our team for a recommendation →
                </a>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS SECTION -->
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    What Our <span class="gradient-text">Patients Say</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Don't just take our word for it. Here's what our patients have to say about their experience with MMHC.
                </p>
            </div>
            
            <!-- Testimonials Carousel (admin-editable: Admin → Website front page → Testimonials) -->
            @php
                $quoteBgClasses = ['bg-blue-100', 'bg-green-100', 'bg-purple-100', 'bg-orange-100'];
                $quoteIconClasses = ['text-blue-600', 'text-green-600', 'text-purple-600', 'text-orange-600'];
                $subtitleClasses = ['text-blue-600', 'text-green-600', 'text-purple-600', 'text-orange-600'];
            @endphp
            <div class="relative" x-data="{ currentTestimonial: 0, totalTestimonials: {{ $testimonials->count() ?: 1 }} }">
                <!-- Carousel Container -->
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out" 
                         :style="`transform: translateX(-${currentTestimonial * 100}%)`">
                        @forelse($testimonials as $index => $t)
                        @php $ci = $index % 4; @endphp
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center max-w-4xl mx-auto">
                                <div class="w-16 h-16 {{ $quoteBgClasses[$ci] }} rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-quote-left {{ $quoteIconClasses[$ci] }} text-2xl"></i>
                                </div>
                                <div class="flex justify-center items-center mb-6">
                                    <div class="flex text-yellow-400 mr-2">
                                        @for($i = 0; $i < 5; $i++)<i class="fas fa-star"></i>@endfor
                                    </div>
                                    <span class="text-gray-600 font-semibold">{{ number_format($t->rating ?? 5, 1) }}</span>
                                </div>
                                <blockquote class="text-xl text-gray-700 leading-relaxed mb-8 italic">"{{ $t->quote }}"</blockquote>
                                <div class="flex items-center justify-center">
                                    @if($t->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($t->image_path))
                                        <img src="{{ storage_asset($t->image_path) }}" alt="{{ $t->name }}" class="w-16 h-16 rounded-full object-cover mr-4">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4 text-gray-400"><i class="fas fa-user text-2xl"></i></div>
                                    @endif
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800">{{ $t->name }}</h4>
                                        @if($t->patient_since)<p class="{{ $subtitleClasses[$ci] }} font-semibold">{{ $t->patient_since }}</p>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg p-8 text-center max-w-4xl mx-auto text-gray-500">
                                <i class="fas fa-quote-left text-4xl mb-4"></i>
                                <p>No testimonials yet. Add them in Admin → Website front page → Testimonials.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($testimonials->count() > 1)
                <button @click="currentTestimonial = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <button @click="currentTestimonial = (currentTestimonial + 1) % totalTestimonials" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="i in totalTestimonials" :key="i">
                        <button @click="currentTestimonial = i - 1" 
                                :class="currentTestimonial === i - 1 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-3 h-3 rounded-full transition">
                        </button>
                    </template>
                </div>
                @endif
            </div>
            
            <!-- Stats Row -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-blue-600 mb-2">10,000+</div>
                    <div class="text-gray-600 font-semibold">Successful Outcomes</div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-green-600 mb-2">24x7</div>
                    <div class="text-gray-600 font-semibold">Home Care Available</div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-purple-600 mb-2">5 Cities</div>
                    <div class="text-gray-600 font-semibold">Service Locations</div>
                </div>
            </div>
            
            <!-- Bottom CTA -->
            <div class="text-center mt-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Join Our Happy Patients</h3>
                <p class="text-gray-600 mb-8">Experience the same exceptional care that our patients rave about.</p>
                <a href="{{ route('auth.register') }}?role=patient" class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    Start Your Journey
                </a>
            </div>
        </div>
    </section>

    <!-- CONTACT FORM SECTION -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Get In <span class="gradient-text">Touch</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Have questions about our services? Ready to start your healthcare journey? We're here to help you every step of the way.
                </p>
            </div>
            
            <!-- Contact Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                <!-- Contact Form -->
                <div class="bg-gray-50 rounded-2xl p-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h3>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firstName" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                                <input type="text" id="firstName" name="firstName" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label for="lastName" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                                <input type="text" id="lastName" name="lastName" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        
                        <div>
                            <label for="service" class="block text-sm font-semibold text-gray-700 mb-2">Service Interested In</label>
                            <select id="service" name="service" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">Select a service</option>
                                <option value="basic">Basic Plan</option>
                                <option value="standard">Standard Plan</option>
                                <option value="premium">Premium Plan</option>
                                <option value="family">Family Plan</option>
                                <option value="caregiver">Become a Caregiver</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <textarea id="message" name="message" rows="4" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                      placeholder="Tell us how we can help you..."></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-green-500 text-white py-3 px-6 rounded-lg font-semibold hover:shadow-lg transition">
                            Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Info & Social Media -->
                <div class="space-y-8">
                    <!-- Contact Information (from admin Site Settings) -->
                    @php
                        $contactAddress = \App\Models\SiteSetting::get('contact_address', "Udgam Incubation Centre, Rohit Nagar\nPhase 1 (Near Surya Children School)\nBhopal 462023, Madhya Pradesh");
                        $contactPhone = \App\Models\SiteSetting::get('contact_phone', '9113311256');
                        $contactWebsite = \App\Models\SiteSetting::get('contact_website', 'www.themmhc.com');
                        $contactEmail = \App\Models\SiteSetting::get('contact_email', 'Care@themmhc.com');
                        $serviceLocations = \App\Models\SiteSetting::get('service_locations', "Patna | Ranchi | Bhopal\nNoida | Gurgaon");
                    @endphp
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Contact Information</h3>
                        <div class="space-y-6">
                            @if($contactAddress)
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Corporate Office</h4>
                                    <p class="text-gray-600">{!! nl2br(e($contactAddress)) !!}</p>
                                </div>
                            </div>
                            @endif
                            @if($contactPhone || $contactWebsite)
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-phone text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Phone (24x7)</h4>
                                    <p class="text-gray-600">{{ $contactPhone }}{!! $contactWebsite ? '<br>' . e($contactWebsite) : '' !!}</p>
                                </div>
                            </div>
                            @endif
                            @if($contactEmail)
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-envelope text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Email</h4>
                                    <p class="text-gray-600">{{ $contactEmail }}</p>
                                </div>
                            </div>
                            @endif
                            @if($serviceLocations)
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-map-marked-alt text-orange-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Service Locations</h4>
                                    <p class="text-gray-600">{!! nl2br(e($serviceLocations)) !!}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center text-white hover:bg-blue-500 transition">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-pink-600 rounded-full flex items-center justify-center text-white hover:bg-pink-700 transition">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-blue-800 rounded-full flex items-center justify-center text-white hover:bg-blue-900 transition">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white hover:bg-red-700 transition">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Quick Links</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="#plans" class="text-blue-600 hover:text-blue-700 font-medium transition">Healthcare Plans</a>
                            <a href="#star-performers" class="text-blue-600 hover:text-blue-700 font-medium transition">Our Caregivers</a>
                            <a href="#about" class="text-blue-600 hover:text-blue-700 font-medium transition">About Us</a>
                            <a href="#testimonials" class="text-blue-600 hover:text-blue-700 font-medium transition">Testimonials</a>
                            <a href="{{ route('auth.register') }}?role=patient" class="text-blue-600 hover:text-blue-700 font-medium transition">Patient Registration</a>
                            <a href="{{ route('auth.register') }}?warrior=1" class="text-blue-600 hover:text-blue-700 font-medium transition">Nursing Warrior</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mobile app: quick sign-in / register -->
    <div class="mmhc-landing-mobile-bar md:hidden" role="navigation" aria-label="Quick actions">
        <a href="{{ route('pwa.install') }}" class="mmhc-landing-mobile-bar__btn mmhc-landing-mobile-bar__btn--outline mmhc-pwa-install-nav">Install</a>
        <a href="{{ route('auth.login') }}" class="mmhc-landing-mobile-bar__btn mmhc-landing-mobile-bar__btn--outline">Sign in</a>
        <a href="{{ route('auth.register') }}" class="mmhc-landing-mobile-bar__btn mmhc-landing-mobile-bar__btn--primary">Get started</a>
    </div>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-white py-8 mmhc-landing-footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Bottom Bar -->
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    © {{ date('Y') }} Med Miracle Health Care (MMHC). All rights reserved. | Founded by Mantu Kumar
                </div>
                <div class="flex space-x-6 text-sm">
                    <a href="{{ route('pwa.install') }}" class="text-gray-400 hover:text-white transition mmhc-pwa-install-nav">Install App</a>
                    <a href="{{ route('legal.privacy') }}" class="text-gray-400 hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/mobile-crm.js') }}" defer></script>
    <script src="{{ asset('js/capacitor-app.js') }}" defer></script>
    @include('partials.pwa-scripts')
    <script>
    (function () {
        var btn = document.getElementById('mmhcMobileMenuBtn');
        var panel = document.getElementById('mmhcMobileMenuPanel');
        var backdrop = document.getElementById('mmhcMobileMenuBackdrop');
        if (!btn || !panel) return;

        function setOpen(open) {
            panel.classList.toggle('hidden', !open);
            panel.classList.toggle('is-open', open);
            panel.setAttribute('aria-hidden', open ? 'false' : 'true');
            if (backdrop) {
                backdrop.classList.toggle('hidden', !open);
                backdrop.classList.toggle('is-open', open);
                backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
            }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.classList.toggle('is-open', open);
            document.body.classList.toggle('mmhc-landing-menu-open', open);
            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars', !open);
                icon.classList.toggle('fa-times', open);
            }
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });

        if (backdrop) {
            backdrop.addEventListener('click', function () { setOpen(false); });
        }

        panel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setOpen(false); });
        });
    })();
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MMHC - Your Health, Our Priority</title>
    
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
    </style>
</head>
<body class="bg-gray-50">

    <!-- NAVIGATION BAR -->
    <nav class="fixed w-full bg-white shadow-md z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="#" class="flex items-center space-x-2">
                        <div class="w-12 h-12 gradient-bg rounded-lg flex items-center justify-center">
                            <i class="fas fa-heartbeat text-white text-2xl"></i>
                        </div>
                        <span class="text-2xl font-bold gradient-text">MMHC</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition">Home</a>
                    <a href="#plans" class="text-gray-700 hover:text-blue-600 font-medium transition">Plans</a>
                    <a href="#about" class="text-gray-700 hover:text-blue-600 font-medium transition">About</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium transition">Contact</a>
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
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-t">
            <div class="px-4 py-4 space-y-3">
                <a href="#home" class="block text-gray-700 hover:text-blue-600 font-medium">Home</a>
                <a href="#plans" class="block text-gray-700 hover:text-blue-600 font-medium">Plans</a>
                <a href="#about" class="block text-gray-700 hover:text-blue-600 font-medium">About</a>
                <a href="#contact" class="block text-gray-700 hover:text-blue-600 font-medium">Contact</a>
                <hr class="my-3">
                <a href="{{ route('auth.login') }}" class="block w-full text-center px-5 py-2 text-blue-600 border border-blue-600 rounded-lg">Login</a>
                <a href="{{ route('auth.register') }}" class="block w-full text-center px-5 py-2 bg-gradient-to-r from-blue-600 to-green-500 text-white rounded-lg">Register</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="home" class="pt-32 pb-20 gradient-bg relative overflow-hidden">
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
                        <a href="{{ route('auth.register') }}?role=caregiver" class="px-8 py-4 bg-yellow-400 text-gray-900 rounded-lg font-semibold hover:shadow-xl transition transform hover:scale-105 text-center">
                            <i class="fas fa-user-nurse mr-2"></i>I'm a Caregiver
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="flex flex-wrap gap-8 text-white">
                        <div>
                            <div class="text-3xl font-bold">500+</div>
                            <div class="text-sm text-gray-200">Patients Served</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">50+</div>
                            <div class="text-sm text-gray-200">Expert Caregivers</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">24/7</div>
                            <div class="text-sm text-gray-200">Support Available</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Image/Illustration -->
                <div class="relative fade-in-up">
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

    <!-- PLANS SECTION -->
    <section id="plans" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Choose Your <span class="gradient-text">Healthcare Plan</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Flexible healthcare plans designed to meet your needs. From basic checkups to comprehensive family care.
                </p>
            </div>
            
            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @forelse($healthcarePlans as $plan)
                    <div class="bg-white {{ $plan->is_popular ? 'rounded-2xl shadow-xl border-2 border-' . $plan->color_theme . '-500' : 'rounded-2xl shadow-lg' }} hover-lift p-8 relative">
                        
                        @if($plan->is_popular)
                            <!-- Popular Badge -->
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                                <span class="bg-{{ $plan->color_theme }}-500 text-white px-4 py-1 rounded-full text-xs font-semibold shadow-lg">
                                    @if($plan->popular_label)
                                        {{ $plan->popular_label }}
                                    @else
                                        ⭐ Most Popular
                                    @endif
                                </span>
                            </div>
                        @endif
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-{{ $plan->color_theme }}-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas {{ $plan->icon_class }} text-{{ $plan->color_theme }}-600 text-2xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $plan->name }}</h3>
                            <p class="text-gray-600 mb-6">{{ $plan->description }}</p>
                            <div class="mb-6">
                                <span class="text-4xl font-bold text-gray-800">{{ $plan->formatted_price }}</span>
                                <span class="text-gray-600">{{ $plan->duration_text }}</span>
                            </div>
                            
                            <!-- Features -->
                            <ul class="text-left space-y-3 mb-8">
                                @foreach($plan->features as $feature)
                                    <li class="flex items-center">
                                        <i class="fas fa-check text-green-500 mr-3"></i>
                                        <span class="text-gray-700">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            
                            <a href="{{ $plan->button_link ?: route('auth.register') }}?role=patient&plan={{ strtolower(str_replace(' ', '_', $plan->name)) }}" 
                               class="w-full bg-{{ $plan->color_theme }}-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-{{ $plan->color_theme }}-700 transition">
                                {{ $plan->button_text }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-12">
                        <i class="fas fa-heartbeat text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600">No healthcare plans available at the moment.</p>
                        <p class="text-sm text-gray-500 mt-2">Please contact us for more information.</p>
                    </div>
                @endforelse
            </div>
            
            <!-- Bottom CTA -->
            <div class="text-center mt-12">
                <p class="text-gray-600 mb-4">Not sure which plan is right for you?</p>
                <a href="#contact" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Contact our team for personalized recommendations →
                </a>
            </div>
        </div>
    </section>

    <!-- STAR PERFORMERS SECTION -->
    <section id="star-performers" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Meet Our <span class="gradient-text">Star Performers</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Our expert caregivers and nursing staff are dedicated to providing exceptional healthcare services with compassion and expertise.
                </p>
            </div>
            
            <!-- Caregivers Carousel -->
            <div class="relative" x-data="{ currentSlide: 0, totalSlides: 4 }">
                <!-- Carousel Container -->
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out" 
                         :style="`transform: translateX(-${currentSlide * 100}%)`">
                        
                        <!-- Caregiver 1 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center">
                                <div class="relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&h=300&fit=crop&crop=face" 
                                         alt="Dr. Sarah Johnson" 
                                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Verified
                                        </div>
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Dr. Sarah Johnson</h3>
                                <p class="text-blue-600 font-semibold mb-3">Senior Caregiver & RN</p>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">4.9 (127 reviews)</span>
                                </div>
                                
                                <p class="text-gray-600 mb-6 leading-relaxed">
                                    "Specializing in elderly care with 8 years of experience. Passionate about providing compassionate healthcare and building meaningful relationships with patients."
                                </p>
                                
                                <div class="flex flex-wrap justify-center gap-2 mb-6">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Elderly Care</span>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Diabetes Management</span>
                                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">Medication Admin</span>
                                </div>
                                
                                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    View Profile
                                </button>
                            </div>
                        </div>
                        
                        <!-- Caregiver 2 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center">
                                <div class="relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&h=300&fit=crop&crop=face" 
                                         alt="Dr. Michael Chen" 
                                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Verified
                                        </div>
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Dr. Michael Chen</h3>
                                <p class="text-blue-600 font-semibold mb-3">Cardiac Care Specialist</p>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">4.8 (89 reviews)</span>
                                </div>
                                
                                <p class="text-gray-600 mb-6 leading-relaxed">
                                    "Expert in cardiac care and rehabilitation. 12 years of experience helping patients recover and maintain heart health through personalized care plans."
                                </p>
                                
                                <div class="flex flex-wrap justify-center gap-2 mb-6">
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">Cardiac Care</span>
                                    <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm">Rehabilitation</span>
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Health Coaching</span>
                                </div>
                                
                                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    View Profile
                                </button>
                            </div>
                        </div>
                        
                        <!-- Caregiver 3 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center">
                                <div class="relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1594824804732-9815c48a5b72?w=300&h=300&fit=crop&crop=face" 
                                         alt="Nurse Emily Rodriguez" 
                                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Verified
                                        </div>
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Nurse Emily Rodriguez</h3>
                                <p class="text-blue-600 font-semibold mb-3">Pediatric Care Specialist</p>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">5.0 (156 reviews)</span>
                                </div>
                                
                                <p class="text-gray-600 mb-6 leading-relaxed">
                                    "Specializing in pediatric care with 6 years of experience. Known for her gentle approach and ability to make children feel comfortable during medical care."
                                </p>
                                
                                <div class="flex flex-wrap justify-center gap-2 mb-6">
                                    <span class="bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm">Pediatric Care</span>
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">Child Development</span>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Vaccination</span>
                                </div>
                                
                                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    View Profile
                                </button>
                            </div>
                        </div>
                        
                        <!-- Caregiver 4 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center">
                                <div class="relative mb-6">
                                    <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=300&h=300&fit=crop&crop=face" 
                                         alt="Dr. James Wilson" 
                                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-white shadow-lg">
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2">
                                        <div class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Verified
                                        </div>
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Dr. James Wilson</h3>
                                <p class="text-blue-600 font-semibold mb-3">Emergency Care Specialist</p>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">4.9 (203 reviews)</span>
                                </div>
                                
                                <p class="text-gray-600 mb-6 leading-relaxed">
                                    "Emergency care expert with 15 years of experience. Available 24/7 for urgent medical situations and providing immediate, life-saving care."
                                </p>
                                
                                <div class="flex flex-wrap justify-center gap-2 mb-6">
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">Emergency Care</span>
                                    <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm">Trauma Care</span>
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">24/7 Available</span>
                                </div>
                                
                                <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                    View Profile
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <button @click="currentSlide = (currentSlide - 1 + totalSlides) % totalSlides" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                
                <button @click="currentSlide = (currentSlide + 1) % totalSlides" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
                
                <!-- Dots Indicator -->
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="i in totalSlides" :key="i">
                        <button @click="currentSlide = i - 1" 
                                :class="currentSlide === i - 1 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-3 h-3 rounded-full transition">
                        </button>
                    </template>
                </div>
            </div>
            
            <!-- Bottom CTA -->
            <div class="text-center mt-12">
                <p class="text-gray-600 mb-6">Want to see more of our amazing caregivers?</p>
                <a href="{{ route('auth.register') }}?role=caregiver" class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                    Join Our Team
                </a>
            </div>
        </div>
    </section>

    <!-- WHY CHOOSE MMHC SECTION -->
    <section id="why-choose" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Why Choose <span class="gradient-text">MMHC?</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    We're committed to providing exceptional healthcare services with cutting-edge technology, compassionate care, and unmatched expertise.
                </p>
            </div>
            
            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-clock text-blue-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">24/7 Availability</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Healthcare doesn't wait for business hours. Our team is available around the clock to provide immediate care and support whenever you need it.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-pills text-green-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Smart Medication Management</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Never miss a dose again. Our advanced medication tracking system sends reminders and helps manage complex medication schedules.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-mobile-alt text-purple-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Easy Appointment Booking</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Book appointments with just a few clicks. Our intuitive platform makes scheduling healthcare visits simple and convenient.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-yellow-200 transition-colors">
                        <i class="fas fa-graduation-cap text-yellow-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Certified Professionals</h3>
                    <p class="text-gray-600 leading-relaxed">
                        All our caregivers are licensed, certified, and continuously trained to provide the highest quality healthcare services.
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-red-200 transition-colors">
                        <i class="fas fa-rupee-sign text-red-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Affordable Plans</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Quality healthcare shouldn't break the bank. Our flexible pricing plans make professional healthcare accessible to everyone.
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center group">
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-orange-200 transition-colors">
                        <i class="fas fa-ambulance text-orange-600 text-3xl group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Emergency Support</h3>
                    <p class="text-gray-600 leading-relaxed">
                        When emergencies strike, we're here. Our rapid response team provides immediate medical assistance and coordination.
                    </p>
                </div>
                
            </div>
            
            <!-- Stats Section -->
            <div class="mt-20 bg-white rounded-2xl shadow-lg p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-blue-600 mb-2">500+</div>
                        <div class="text-gray-600 font-semibold">Patients Served</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">50+</div>
                        <div class="text-gray-600 font-semibold">Expert Caregivers</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-purple-600 mb-2">99%</div>
                        <div class="text-gray-600 font-semibold">Patient Satisfaction</div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-bold text-orange-600 mb-2">24/7</div>
                        <div class="text-gray-600 font-semibold">Support Available</div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom CTA -->
            <div class="text-center mt-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Ready to Experience Better Healthcare?</h3>
                <p class="text-gray-600 mb-8">Join thousands of satisfied patients who trust MMHC for their healthcare needs.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('auth.register') }}?role=patient" class="bg-gradient-to-r from-blue-600 to-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                        Get Started Today
                    </a>
                    <a href="#contact" class="border-2 border-blue-600 text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition">
                        Learn More
                    </a>
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
                    About <span class="gradient-text">MMHC</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Founded with a vision to revolutionize healthcare delivery, MMHC has been at the forefront of providing compassionate, accessible, and innovative healthcare services.
                </p>
            </div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-20">
                <!-- Left Side - Story -->
                <div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">Our Story</h3>
                    <div class="space-y-6 text-gray-600 leading-relaxed">
                        <p>
                            MMHC (Modern Medical Healthcare Center) was founded in 2018 with a simple yet powerful mission: to make quality healthcare accessible to everyone, everywhere. What started as a small team of dedicated healthcare professionals has grown into a comprehensive healthcare platform serving thousands of patients across the region.
                        </p>
                        <p>
                            Our journey began when our founders, experienced healthcare professionals, recognized the challenges patients face in accessing timely, affordable, and quality healthcare services. They envisioned a platform that would bridge the gap between patients and caregivers while ensuring the highest standards of medical care.
                        </p>
                        <p>
                            Today, MMHC stands as a testament to innovation in healthcare delivery, combining cutting-edge technology with human compassion to create a healthcare experience that puts patients at the center of everything we do.
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
                        To provide accessible, affordable, and high-quality healthcare services through innovative technology and compassionate care, ensuring that every patient receives the medical attention they deserve, when they need it most.
                    </p>
                </div>
                
                <!-- Vision -->
                <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                    <p class="text-gray-600 leading-relaxed">
                        To become the leading healthcare platform that transforms how people access and experience healthcare, creating a world where quality medical care is available to everyone, regardless of their location or circumstances.
                    </p>
                </div>
            </div>
            
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
            
            <!-- Timeline -->
            <div class="mb-20">
                <h3 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Journey</h3>
                <div class="relative">
                    <!-- Timeline Line -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 w-1 h-full bg-gradient-to-b from-blue-500 to-green-500"></div>
                    
                    <!-- Timeline Items -->
                    <div class="space-y-12">
                        <!-- 2018 -->
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8 text-right">
                                <div class="bg-white rounded-lg shadow-lg p-6">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">2018 - Foundation</h4>
                                    <p class="text-gray-600">MMHC was founded with a vision to revolutionize healthcare delivery through technology and compassion.</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 bg-blue-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-xs font-bold">18</span>
                            </div>
                            <div class="w-1/2 pl-8"></div>
                        </div>
                        
                        <!-- 2020 -->
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8"></div>
                            <div class="w-8 h-8 bg-green-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-xs font-bold">20</span>
                            </div>
                            <div class="w-1/2 pl-8">
                                <div class="bg-white rounded-lg shadow-lg p-6">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">2020 - Digital Platform</h4>
                                    <p class="text-gray-600">Launched our comprehensive digital healthcare platform, connecting patients with caregivers seamlessly.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 2022 -->
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8 text-right">
                                <div class="bg-white rounded-lg shadow-lg p-6">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">2022 - Expansion</h4>
                                    <p class="text-gray-600">Expanded our services to cover multiple cities and reached 1000+ satisfied patients.</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 bg-purple-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-xs font-bold">22</span>
                            </div>
                            <div class="w-1/2 pl-8"></div>
                        </div>
                        
                        <!-- 2024 -->
                        <div class="flex items-center">
                            <div class="w-1/2 pr-8"></div>
                            <div class="w-8 h-8 bg-orange-600 rounded-full border-4 border-white shadow-lg flex items-center justify-center relative z-10">
                                <span class="text-white text-xs font-bold">24</span>
                            </div>
                            <div class="w-1/2 pl-8">
                                <div class="bg-white rounded-lg shadow-lg p-6">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">2024 - Innovation</h4>
                                    <p class="text-gray-600">Introduced AI-powered health monitoring and 24/7 emergency response services.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Team Section -->
            <div class="text-center">
                <h3 class="text-3xl font-bold text-gray-800 mb-12">Meet Our Leadership Team</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=200&h=200&fit=crop&crop=face" 
                             alt="Dr. Rajesh Kumar" 
                             class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Dr. Rajesh Kumar</h4>
                        <p class="text-blue-600 font-semibold mb-2">Founder & CEO</p>
                        <p class="text-gray-600 text-sm">15+ years in healthcare management and innovation</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=200&h=200&fit=crop&crop=face" 
                             alt="Dr. Priya Sharma" 
                             class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Dr. Priya Sharma</h4>
                        <p class="text-blue-600 font-semibold mb-2">Chief Medical Officer</p>
                        <p class="text-gray-600 text-sm">Expert in patient care and medical technology</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop&crop=face" 
                             alt="Amit Singh" 
                             class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                        <h4 class="text-xl font-bold text-gray-800 mb-2">Amit Singh</h4>
                        <p class="text-blue-600 font-semibold mb-2">CTO</p>
                        <p class="text-gray-600 text-sm">Leading our technology and digital transformation</p>
                    </div>
                </div>
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
            
            <!-- Testimonials Carousel -->
            <div class="relative" x-data="{ currentTestimonial: 0, totalTestimonials: 4 }">
                <!-- Carousel Container -->
                <div class="overflow-hidden">
                    <div class="flex transition-transform duration-500 ease-in-out" 
                         :style="`transform: translateX(-${currentTestimonial * 100}%)`">
                        
                        <!-- Testimonial 1 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center max-w-4xl mx-auto">
                                <!-- Quote Icon -->
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-quote-left text-blue-600 text-2xl"></i>
                                </div>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-6">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">5.0</span>
                                </div>
                                
                                <!-- Testimonial Text -->
                                <blockquote class="text-xl text-gray-700 leading-relaxed mb-8 italic">
                                    "MMHC has completely transformed how I manage my health. The caregivers are incredibly professional and caring. The medication reminders have been a lifesaver, and I love how easy it is to book appointments. I recommend MMHC to everyone!"
                                </blockquote>
                                
                                <!-- Patient Info -->
                                <div class="flex items-center justify-center">
                                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=80&h=80&fit=crop&crop=face" 
                                         alt="Sarah Johnson" 
                                         class="w-16 h-16 rounded-full object-cover mr-4">
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800">Sarah Johnson</h4>
                                        <p class="text-blue-600 font-semibold">Patient since 2022</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial 2 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center max-w-4xl mx-auto">
                                <!-- Quote Icon -->
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-quote-left text-green-600 text-2xl"></i>
                                </div>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-6">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">5.0</span>
                                </div>
                                
                                <!-- Testimonial Text -->
                                <blockquote class="text-xl text-gray-700 leading-relaxed mb-8 italic">
                                    "As a senior citizen, I was worried about managing my medications and appointments. MMHC made everything so simple! Dr. Sarah is amazing - she's patient, knowledgeable, and always available when I need help. The 24/7 support gives me peace of mind."
                                </blockquote>
                                
                                <!-- Patient Info -->
                                <div class="flex items-center justify-center">
                                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face" 
                                         alt="Robert Chen" 
                                         class="w-16 h-16 rounded-full object-cover mr-4">
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800">Robert Chen</h4>
                                        <p class="text-green-600 font-semibold">Patient since 2021</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial 3 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center max-w-4xl mx-auto">
                                <!-- Quote Icon -->
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-quote-left text-purple-600 text-2xl"></i>
                                </div>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-6">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">5.0</span>
                                </div>
                                
                                <!-- Testimonial Text -->
                                <blockquote class="text-xl text-gray-700 leading-relaxed mb-8 italic">
                                    "I'm a busy working mother and finding time for healthcare was always a challenge. MMHC's telemedicine feature is incredible! I can consult with doctors from home, and the medication delivery service is so convenient. The family plan covers all of us perfectly."
                                </blockquote>
                                
                                <!-- Patient Info -->
                                <div class="flex items-center justify-center">
                                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=80&h=80&fit=crop&crop=face" 
                                         alt="Maria Rodriguez" 
                                         class="w-16 h-16 rounded-full object-cover mr-4">
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800">Maria Rodriguez</h4>
                                        <p class="text-purple-600 font-semibold">Patient since 2023</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonial 4 -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-white rounded-2xl shadow-lg hover-lift p-8 text-center max-w-4xl mx-auto">
                                <!-- Quote Icon -->
                                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-quote-left text-orange-600 text-2xl"></i>
                                </div>
                                
                                <!-- Rating -->
                                <div class="flex justify-center items-center mb-6">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 font-semibold">5.0</span>
                                </div>
                                
                                <!-- Testimonial Text -->
                                <blockquote class="text-xl text-gray-700 leading-relaxed mb-8 italic">
                                    "After my heart surgery, I needed continuous care and monitoring. MMHC's cardiac care program has been exceptional. Dr. Michael is not just a doctor, he's become like family. The recovery tracking and health coaching have helped me get back to my normal life faster than expected."
                                </blockquote>
                                
                                <!-- Patient Info -->
                                <div class="flex items-center justify-center">
                                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&h=80&fit=crop&crop=face" 
                                         alt="David Wilson" 
                                         class="w-16 h-16 rounded-full object-cover mr-4">
                                    <div class="text-left">
                                        <h4 class="text-lg font-bold text-gray-800">David Wilson</h4>
                                        <p class="text-orange-600 font-semibold">Patient since 2022</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <button @click="currentTestimonial = (currentTestimonial - 1 + totalTestimonials) % totalTestimonials" 
                        class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                
                <button @click="currentTestimonial = (currentTestimonial + 1) % totalTestimonials" 
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-3 hover:shadow-xl transition">
                    <i class="fas fa-chevron-right text-gray-600"></i>
                </button>
                
                <!-- Dots Indicator -->
                <div class="flex justify-center mt-8 space-x-2">
                    <template x-for="i in totalTestimonials" :key="i">
                        <button @click="currentTestimonial = i - 1" 
                                :class="currentTestimonial === i - 1 ? 'bg-blue-600' : 'bg-gray-300'"
                                class="w-3 h-3 rounded-full transition">
                        </button>
                    </template>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-blue-600 mb-2">4.9/5</div>
                    <div class="text-gray-600 font-semibold">Average Rating</div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-green-600 mb-2">98%</div>
                    <div class="text-gray-600 font-semibold">Patient Satisfaction</div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="text-3xl font-bold text-purple-600 mb-2">500+</div>
                    <div class="text-gray-600 font-semibold">Happy Patients</div>
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
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Contact Information</h3>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Address</h4>
                                    <p class="text-gray-600">123 Healthcare Street<br>Medical District, Mumbai 400001<br>Maharashtra, India</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-phone text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Phone</h4>
                                    <p class="text-gray-600">+91 98765 43210<br>+91 98765 43211 (Emergency)</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-envelope text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Email</h4>
                                    <p class="text-gray-600">info@themmhc.com<br>support@themmhc.com</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-clock text-orange-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-1">Business Hours</h4>
                                    <p class="text-gray-600">Monday - Friday: 8:00 AM - 8:00 PM<br>Saturday: 9:00 AM - 6:00 PM<br>Sunday: Emergency Services Only</p>
                                </div>
                            </div>
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
                            <a href="{{ route('auth.register') }}?role=caregiver" class="text-blue-600 hover:text-blue-700 font-medium transition">Caregiver Registration</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-green-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-heartbeat text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">MMHC</span>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        Modern Medical Healthcare Center - Your trusted partner in healthcare. Providing compassionate, accessible, and innovative healthcare services to communities across India.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-400 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-pink-600 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-800 transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Services -->
                <div>
                    <h3 class="text-xl font-bold mb-6">Our Services</h3>
                    <ul class="space-y-3">
                        <li><a href="#plans" class="text-gray-300 hover:text-white transition">Healthcare Plans</a></li>
                        <li><a href="#star-performers" class="text-gray-300 hover:text-white transition">Caregiver Services</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Emergency Care</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Telemedicine</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Medication Management</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Health Monitoring</a></li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li><a href="#about" class="text-gray-300 hover:text-white transition">About Us</a></li>
                        <li><a href="#testimonials" class="text-gray-300 hover:text-white transition">Testimonials</a></li>
                        <li><a href="{{ route('auth.login') }}" class="text-gray-300 hover:text-white transition">Patient Login</a></li>
                        <li><a href="{{ route('auth.register') }}?role=patient" class="text-gray-300 hover:text-white transition">Patient Registration</a></li>
                        <li><a href="{{ route('auth.register') }}?role=caregiver" class="text-gray-300 hover:text-white transition">Caregiver Registration</a></li>
                        <li><a href="#contact" class="text-gray-300 hover:text-white transition">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-bold mb-6">Contact Info</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <p class="text-gray-300">123 Healthcare Street</p>
                                <p class="text-gray-300">Medical District, Mumbai 400001</p>
                                <p class="text-gray-300">Maharashtra, India</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-green-500 mr-3"></i>
                            <p class="text-gray-300">+91 98765 43210</p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-purple-500 mr-3"></i>
                            <p class="text-gray-300">info@themmhc.com</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        © 2024 MMHC (Modern Medical Healthcare Center). All rights reserved.
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

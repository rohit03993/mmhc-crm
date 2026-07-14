@extends('auth::layout')

@section('title', 'Available Staff - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @include('services::partials.mobile-assets')
@endsection

@section('content')
<!-- Mobile App View for Staff Listing -->
<div class="mobile-app-container mmhc-page-staff hc-mobile-shell" data-mmhc-ptr>
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('dashboard') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Available Staff</div>
                    <div class="app-header-subtitle">
                        @if(!empty($hasPatientLocation))
                            Near your current location
                        @else
                            Turn on location to see nearby staff
                        @endif
                    </div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('services.my-requests') }}" class="app-header-icon" title="My requests">
                    <i class="fas fa-clipboard-list"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        @auth
            @if(auth()->user()->isPatient())
                @include('services::partials.booking-flow-steps', ['currentStep' => 'staff'])
            @endif
        @endauth

        @if(session('success'))
        <div class="staff-location-alert staff-location-alert--success" role="status">{{ session('success') }}</div>
        @endif

        @auth
            @if(auth()->user()->isPatient())
            <div class="staff-location-panel" data-auto-locate="{{ !empty($needsLocationSetup) ? '1' : '0' }}">
                @if(!empty($needsLocationSetup))
                <div class="staff-location-banner">
                    <i class="fas fa-location-crosshairs"></i>
                    <div>
                        <strong>Find staff near you</strong>
                        <p>We use your phoneâ€™s <strong>current GPS location</strong> (not pincode) to show the nearest nurses and caregivers.</p>
                    </div>
                </div>
                @elseif(!empty($hasPatientLocation))
                <div class="staff-location-banner staff-location-banner--active">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Showing staff near your current location</strong>
                        <p>
                            @if(!empty($locationFromGps))
                                Live GPS Â· within {{ $distance ?: '25' }} km Â· nearest first
                            @elseif($distance)
                                Within {{ $distance }} km Â· nearest first
                            @else
                                Sorted by distance (nearest first)
                            @endif
                        </p>
                    </div>
                </div>
                @endif

                <div class="staff-location-actions">
                    <button type="button"
                            id="btnUseMyLocation"
                            class="staff-location-btn"
                            data-resolve-url="{{ route('staff.resolve-location') }}">
                        <i class="fas fa-crosshairs"></i>
                        <span class="btn-label">{{ !empty($hasPatientLocation) ? 'Update current location' : 'Use current location' }}</span>
                    </button>
                </div>
                <div id="staffLocationStatus" class="staff-location-status" hidden></div>
            </div>
            @endif
        @endauth

        <!-- Search & Filter Section - Mobile App Style -->
        <div class="app-search-section">
            
            <!-- Search Bar -->
            <form method="GET" action="{{ route('staff.index') }}" id="searchFilterForm">
                @if(!empty($hasPatientLocation))
                    <input type="hidden" name="lat" value="{{ $patientLat }}">
                    <input type="hidden" name="lng" value="{{ $patientLng }}">
                    <input type="hidden" name="location" value="gps">
                @endif
                
                <div class="app-search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="app-search-input" 
                           placeholder="Search staff..." value="{{ $search }}">
                    @if($search)
                    <a href="{{ route('staff.index', !empty($hasPatientLocation) ? ['lat' => $patientLat, 'lng' => $patientLng, 'location' => 'gps', 'distance' => $distance] : []) }}" class="app-search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>

                <button type="button" class="mmhc-filter-toggle" id="mmhcFilterToggle" aria-expanded="false" aria-controls="mmhcFilterPanel">
                    <i class="fas fa-sliders-h"></i> Filters
                </button>
                
                <!-- Filters - collapsible on mobile -->
                <div class="app-filters mmhc-filter-panel" id="mmhcFilterPanel">
                    <div class="app-filter-row">
                        <select name="experience" class="app-filter-select">
                            <option value="">All Experience</option>
                            <option value="1-3" {{ $experience == '1-3' ? 'selected' : '' }}>1-3 years</option>
                            <option value="3-5" {{ $experience == '3-5' ? 'selected' : '' }}>3-5 years</option>
                            <option value="5-10" {{ $experience == '5-10' ? 'selected' : '' }}>5-10 years</option>
                            <option value="10+" {{ $experience == '10+' ? 'selected' : '' }}>10+ years</option>
                        </select>
                        
                        <select name="qualification" class="app-filter-select">
                            <option value="">All Qualifications</option>
                            <option value="B.Sc" {{ $qualification == 'B.Sc' ? 'selected' : '' }}>B.Sc Nursing</option>
                            <option value="M.Sc" {{ $qualification == 'M.Sc' ? 'selected' : '' }}>M.Sc Nursing</option>
                            <option value="GNM" {{ $qualification == 'GNM' ? 'selected' : '' }}>GNM</option>
                            <option value="General Care" {{ $qualification == 'General Care' ? 'selected' : '' }}>General Care</option>
                        </select>
                    </div>
                    
                    @if(!empty($hasPatientLocation))
                    <div class="app-filter-row">
                        <select name="distance" class="app-filter-select">
                            <option value="">All Distances</option>
                            <option value="5" {{ $distance == '5' ? 'selected' : '' }}>Within 5 km</option>
                            <option value="10" {{ $distance == '10' ? 'selected' : '' }}>Within 10 km</option>
                            <option value="25" {{ $distance == '25' ? 'selected' : '' }}>Within 25 km</option>
                            <option value="50" {{ $distance == '50' ? 'selected' : '' }}>Within 50 km</option>
                        </select>
                        
                        <select name="sort" class="app-filter-select">
                            <option value="distance" {{ $sort == 'distance' ? 'selected' : '' }}>Sort by Distance</option>
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Sort by Name</option>
                            <option value="experience" {{ $sort == 'experience' ? 'selected' : '' }}>Sort by Experience</option>
                        </select>
                    </div>
                    @else
                    <div class="app-filter-row">
                        <select name="sort" class="app-filter-select">
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Sort by Name</option>
                            <option value="experience" {{ $sort == 'experience' ? 'selected' : '' }}>Sort by Experience</option>
                        </select>
                    </div>
                    @endif
                    
                    <div class="app-filter-actions">
                        <button type="submit" class="app-btn-primary">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('staff.index', !empty($hasPatientLocation) ? ['lat' => $patientLat, 'lng' => $patientLng, 'location' => 'gps'] : []) }}" class="app-btn-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Marketplace Tabs -->
        <div class="app-marketplace-tabs">
            <button class="app-tab-btn active" onclick="showTab('nurses')" id="tab-nurses">
                <i class="fas fa-user-nurse"></i>
                <span>Nurses</span>
                <span class="app-tab-badge">{{ $nurses->total() }}</span>
            </button>
            <button class="app-tab-btn" onclick="showTab('caregivers')" id="tab-caregivers">
                <i class="fas fa-user-md"></i>
                <span>Caregivers</span>
                <span class="app-tab-badge">{{ $caregivers->total() }}</span>
            </button>
        </div>

        <!-- Nurses Tab Content -->
        <div class="app-tab-content active" id="content-nurses">
            <div class="app-section-header-compact">
                <div>
                    <h2 class="app-section-title">Licensed Nurses</h2>
                    <p class="app-section-subtitle">{{ $nurses->total() }} {{ $nurses->total() == 1 ? 'Nurse' : 'Nurses' }} available near you</p>
                </div>
            </div>
            
            @if($nurses->count() > 0)
            <div class="app-marketplace-grid">
                @foreach($nurses as $nurse)
                    @php
                        $profile = $nurse->profile ?? null;
                        $availability = $profile ? $profile->availability_status : 'available';
                        $distance = isset($nurse->distance_km) ? $nurse->distance_km : null;
                    @endphp
                <div class="app-marketplace-card">
                    <!-- Distance Badge -->
                    @if($distance !== null)
                    <div class="app-distance-badge distance-{{ $distance < 1 ? 'nearby' : ($distance < 5 ? 'close' : ($distance < 10 ? 'medium' : 'far')) }}">
                        <i class="fas fa-map-marker-alt"></i>
                        @if($distance < 0.1)
                            <span>Nearby</span>
                        @elseif($distance < 1)
                            <span>< 1 km</span>
                        @else
                            <span>{{ number_format($distance, 1) }} km</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Availability Badge -->
                    <div class="app-availability-badge availability-{{ $availability }}">
                        <i class="fas fa-circle"></i>
                        <span>{{ ucfirst($availability) }}</span>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="app-marketplace-card-header">
                        <div class="app-staff-avatar-marketplace nurse-avatar">
                            <i class="fas fa-user-nurse"></i>
                        </div>
                        <div class="app-staff-info-marketplace">
                            <h4 class="app-staff-name-marketplace">{{ $nurse->name }}</h4>
                            <span class="app-staff-id-marketplace">{{ $nurse->unique_id }}</span>
                        </div>
                    </div>
                    
                    <div class="app-marketplace-card-body">
                        <div class="app-staff-specs">
                            @if($nurse->qualification)
                            <div class="app-spec-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>{{ $nurse->qualification }}</span>
                            </div>
                            @endif
                            
                            @if($nurse->experience)
                            <div class="app-spec-item">
                                <i class="fas fa-briefcase"></i>
                                <span>{{ $nurse->experience }} exp.</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="app-marketplace-pricing">
                            <div class="app-price-label">Starting from</div>
                            <div class="app-price-amount">â‚¹{{ number_format($serviceTypes->first()?->nurse_payout ?? 2000) }}/day</div>
                        </div>
                    </div>
                    
                    <div class="app-marketplace-card-footer">
                        <a href="{{ route('book.staff', $nurse) }}" class="app-book-btn">
                            <i class="fas fa-calendar-check me-1"></i>Book Now
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($nurses->hasPages())
            <div class="app-pagination">
                {{ $nurses->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="app-empty-state">
                <div class="app-empty-icon">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <h3 class="app-empty-title">No Nurses Available</h3>
                <p class="app-empty-text">
                    @if(empty($hasPatientLocation))
                        Turn on current location to see nurses sorted by distance from you.
                    @elseif($distance)
                        No nurses within {{ $distance }} km. Try a larger distance or update your location.
                    @else
                        No nurses found nearby. Update your location or adjust filters.
                    @endif
                </p>
                <div class="d-flex flex-column gap-2 mt-2">
                    @if(empty($hasPatientLocation))
                    <button type="button" class="app-btn-primary" onclick="document.getElementById('btnUseMyLocation')?.click();">
                        <i class="fas fa-crosshairs me-1"></i>Use current location
                    </button>
                    @else
                    <button type="button" class="app-btn-primary" onclick="document.getElementById('btnUseMyLocation')?.click();">
                        <i class="fas fa-crosshairs me-1"></i>Update current location
                    </button>
                    <button type="button" class="app-btn-secondary" onclick="document.getElementById('mmhcFilterToggle')?.click();">
                        <i class="fas fa-sliders-h me-1"></i>Widen filters / distance
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Caregivers Tab Content -->
        <div class="app-tab-content" id="content-caregivers">
            <div class="app-section-header-compact">
                <div>
                    <h2 class="app-section-title">Caregivers</h2>
                    <p class="app-section-subtitle">{{ $caregivers->total() }} {{ $caregivers->total() == 1 ? 'Caregiver' : 'Caregivers' }} available near you</p>
                </div>
            </div>
            
            @if($caregivers->count() > 0)
            <div class="app-marketplace-grid">
                @foreach($caregivers as $caregiver)
                    @php
                        $profile = $caregiver->profile ?? null;
                        $availability = $profile ? $profile->availability_status : 'available';
                        $distance = isset($caregiver->distance_km) ? $caregiver->distance_km : null;
                    @endphp
                <div class="app-marketplace-card">
                    <!-- Distance Badge -->
                    @if($distance !== null)
                    <div class="app-distance-badge distance-{{ $distance < 1 ? 'nearby' : ($distance < 5 ? 'close' : ($distance < 10 ? 'medium' : 'far')) }}">
                        <i class="fas fa-map-marker-alt"></i>
                        @if($distance < 0.1)
                            <span>Nearby</span>
                        @elseif($distance < 1)
                            <span>< 1 km</span>
                        @else
                            <span>{{ number_format($distance, 1) }} km</span>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Availability Badge -->
                    <div class="app-availability-badge availability-{{ $availability }}">
                        <i class="fas fa-circle"></i>
                        <span>{{ ucfirst($availability) }}</span>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="app-marketplace-card-header">
                        <div class="app-staff-avatar-marketplace caregiver-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="app-staff-info-marketplace">
                            <h4 class="app-staff-name-marketplace">{{ $caregiver->name }}</h4>
                            <span class="app-staff-id-marketplace">{{ $caregiver->unique_id }}</span>
                        </div>
                    </div>
                    
                    <div class="app-marketplace-card-body">
                        <div class="app-staff-specs">
                            @if($caregiver->qualification)
                            <div class="app-spec-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>{{ $caregiver->qualification }}</span>
                            </div>
                            @endif
                            
                            @if($caregiver->experience)
                            <div class="app-spec-item">
                                <i class="fas fa-briefcase"></i>
                                <span>{{ $caregiver->experience }} exp.</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="app-marketplace-pricing">
                            <div class="app-price-label">Starting from</div>
                            <div class="app-price-amount">â‚¹{{ number_format($serviceTypes->first()?->caregiver_payout ?? 1500) }}/day</div>
                        </div>
                    </div>
                    
                    <div class="app-marketplace-card-footer">
                        <a href="{{ route('book.staff', $caregiver) }}" class="app-book-btn">
                            <i class="fas fa-calendar-check me-1"></i>Book Now
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($caregivers->hasPages())
            <div class="app-pagination">
                {{ $caregivers->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="app-empty-state">
                <div class="app-empty-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="app-empty-title">No Caregivers Available</h3>
                <p class="app-empty-text">
                    @if(empty($hasPatientLocation))
                        Turn on current location to see caregivers sorted by distance from you.
                    @elseif($distance)
                        No caregivers within {{ $distance }} km. Try a larger distance or update your location.
                    @else
                        No caregivers found nearby. Update your location or adjust filters.
                    @endif
                </p>
                <div class="d-flex flex-column gap-2 mt-2">
                    @if(empty($hasPatientLocation))
                    <button type="button" class="app-btn-primary" onclick="document.getElementById('btnUseMyLocation')?.click();">
                        <i class="fas fa-crosshairs me-1"></i>Use current location
                    </button>
                    @else
                    <button type="button" class="app-btn-primary" onclick="document.getElementById('btnUseMyLocation')?.click();">
                        <i class="fas fa-crosshairs me-1"></i>Update current location
                    </button>
                    <button type="button" class="app-btn-secondary" onclick="document.getElementById('mmhcFilterToggle')?.click();">
                        <i class="fas fa-sliders-h me-1"></i>Widen filters / distance
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
<!-- How It Works Section - App Style Grid -->
        <div class="app-section">
            <div class="app-section-header">
                <h2 class="app-section-title">How to Request Services</h2>
                <p class="app-section-subtitle">Simple steps to get professional healthcare</p>
            </div>
            
            <div class="app-steps-grid">
                <div class="app-step-card">
                    <div class="app-step-number">1</div>
                    <div class="app-step-icon step-search">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4 class="app-step-title">Browse Staff</h4>
                    <p class="app-step-desc">View available nurses and caregivers</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">2</div>
                    <div class="app-step-icon step-calendar">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4 class="app-step-title">Select Service</h4>
                    <p class="app-step-desc">Choose service type & duration</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">3</div>
                    <div class="app-step-icon step-assign">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h4 class="app-step-title">Get Assigned</h4>
                    <p class="app-step-desc">Admin assigns best staff</p>
                </div>
                
                <div class="app-step-card">
                    <div class="app-step-number">4</div>
                    <div class="app-step-icon step-care">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="app-step-title">Receive Care</h4>
                    <p class="app-step-desc">Professional healthcare at home</p>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.app-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.app-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.add('active');
    
    // Add active class to selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>
@auth
    @if(auth()->user()->isPatient())
    <script src="{{ asset('js/staff-location.js') }}" defer></script>
    @endif
@endauth
@endsection

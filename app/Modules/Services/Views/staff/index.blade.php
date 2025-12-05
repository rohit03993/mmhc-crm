@extends('auth::layout')

@section('title', 'Available Staff - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <style>
        :root {
            --nurse-gradient: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            --caregiver-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
@endsection

@section('content')
<!-- Mobile App View for Staff Listing -->
<div class="mobile-app-container">
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
                        @if($patientPincode)
                            Near {{ $patientPincode }}
                        @else
                            Nurses & Caregivers
                        @endif
                    </div>
                </div>
            </div>
            <div class="app-header-right">
                <a href="{{ route('services.create') }}" class="app-header-icon">
                    <i class="fas fa-plus"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Search & Filter Section - Mobile App Style -->
        <div class="app-search-section">
            @auth
                @if(auth()->user()->isPatient())
                <form method="GET" action="{{ route('staff.index') }}" class="app-pincode-form">
                    <div class="app-input-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="pincode" value="{{ $patientPincode }}" 
                               placeholder="Enter pincode" maxlength="6" pattern="[0-9]{6}" 
                               class="app-input">
                        <button type="submit" class="app-btn-icon">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="experience" value="{{ $experience }}">
                    <input type="hidden" name="qualification" value="{{ $qualification }}">
                    <input type="hidden" name="distance" value="{{ $distance }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </form>
                @endif
            @endauth
            
            <!-- Search Bar -->
            <form method="GET" action="{{ route('staff.index') }}" id="searchFilterForm">
                @if($patientPincode)
                    <input type="hidden" name="pincode" value="{{ $patientPincode }}">
                @endif
                
                <div class="app-search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="app-search-input" 
                           placeholder="Search staff..." value="{{ $search }}">
                    @if($search)
                    <a href="{{ route('staff.index') }}{{ $patientPincode ? '?pincode=' . $patientPincode : '' }}" class="app-search-clear">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
                
                <!-- Filters - Mobile Collapsible -->
                <div class="app-filters">
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
                    
                    @if($patientPincode)
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
                        <a href="{{ route('staff.index') }}{{ $patientPincode ? '?pincode=' . $patientPincode : '' }}" class="app-btn-secondary">
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
                            <div class="app-price-amount">₹{{ number_format($serviceTypes->first()->nurse_payout ?? 2000) }}/day</div>
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
                {{ $nurses->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
            @endif
            @else
            <div class="app-empty-state">
                <div class="app-empty-icon">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <h3 class="app-empty-title">No Nurses Available</h3>
                <p class="app-empty-text">Try adjusting your filters or check back later</p>
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
                            <div class="app-price-amount">₹{{ number_format($serviceTypes->first()->caregiver_payout ?? 1500) }}/day</div>
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
                {{ $caregivers->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
            @endif
            @else
            <div class="app-empty-state">
                <div class="app-empty-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="app-empty-title">No Caregivers Available</h3>
                <p class="app-empty-text">Try adjusting your filters or check back later</p>
            </div>
            @endif
        </div>
        
        <!-- Empty State Styles -->
        <style>
        .app-empty-state {
            text-align: center;
            padding: 48px 24px;
            background: white;
            border-radius: 16px;
            margin-top: 16px;
        }
        
        .app-empty-icon {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 16px;
        }
        
        .app-empty-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .app-empty-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
        }
        
        .app-pagination {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }
        </style>

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

    <!-- Bottom Navigation Bar (Mobile Only) -->
    <nav class="app-bottom-nav d-md-none">
        <a href="{{ route('dashboard') }}" class="app-nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('services.my-requests') }}" class="app-nav-item">
            <i class="fas fa-list"></i>
            <span>Requests</span>
        </a>
        <a href="{{ route('staff.index') }}" class="app-nav-item active">
            <i class="fas fa-users"></i>
            <span>Staff</span>
        </a>
        <a href="{{ route('profile.index') }}" class="app-nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
</div>

<!-- Mobile App Styling -->
<style>
/* Mobile App Container */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 80px !important;
    margin-top: 0;
}

/* Hide desktop navbar on mobile */
@media (max-width: 767px) {
    .top-navbar {
        display: none !important;
    }
    
    .container-fluid {
        padding-left: 0;
        padding-right: 0;
    }
    
    .row {
        margin-left: 0;
        margin-right: 0;
    }
}

/* App Header Styles (from dashboard) */
.app-header-mobile {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 12px 16px;
    padding-top: max(12px, env(safe-area-inset-top));
}

.app-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-back-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
}

.app-header-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.2;
}

.app-header-subtitle {
    font-size: 0.8rem;
    opacity: 0.9;
}

.app-header-right {
    display: flex;
    gap: 12px;
}

.app-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
}

/* App Content */
.app-content {
    padding: 16px;
    padding-bottom: 90px !important;
    margin-top: 0;
}

@media (max-width: 767px) {
    .app-content {
        padding-top: 8px;
    }
}

/* Search Section */
.app-search-section {
    margin-bottom: 20px;
}

.app-pincode-form {
    margin-bottom: 12px;
}

.app-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-input-group i {
    color: #667eea;
    font-size: 1rem;
}

.app-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 0.95rem;
    color: #2c3e50;
}

.app-input::placeholder {
    color: #9ca3af;
}

.app-btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.app-search-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 12px;
}

.app-search-bar i {
    color: #9ca3af;
    font-size: 1rem;
}

.app-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 0.95rem;
    color: #2c3e50;
}

.app-search-clear {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.75rem;
}

.app-filters {
    background: white;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 8px;
}

.app-filter-select {
    padding: 10px 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.85rem;
    background: white;
    color: #2c3e50;
    outline: none;
}

.app-filter-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}

.app-btn-primary {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.app-btn-secondary {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e9ecef;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

/* App Section */
.app-section {
    margin-bottom: 24px;
}

.app-section-header {
    margin-bottom: 16px;
    padding: 0 4px;
}

.app-section-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-section-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    flex-shrink: 0;
}

.app-section-icon.nurse-icon {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-section-icon.caregiver-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-section-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

/* Staff Grid - App Style */
.app-staff-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

.app-staff-card-full {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.app-staff-card-full:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.app-staff-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.app-staff-card-header {
    padding: 16px;
    background: linear-gradient(to bottom, #f8f9fa 0%, white 100%);
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-staff-avatar-full {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.nurse-avatar-full {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.caregiver-avatar-full {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-staff-info-header {
    flex: 1;
    min-width: 0;
}

.app-staff-name-full {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-staff-id-full {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-staff-card-body-full {
    padding: 16px;
}

.app-staff-details-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 12px;
}

.app-staff-detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.85rem;
    color: #6c757d;
}

.app-staff-detail-item i {
    width: 20px;
    color: #667eea;
    text-align: center;
    font-size: 0.9rem;
}

.app-staff-detail-item.distance {
    color: #10b981;
    font-weight: 600;
}

.app-staff-detail-item.distance i {
    color: #10b981;
}

.app-staff-pricing-mini {
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.app-staff-pricing-mini small {
    font-size: 0.75rem;
    color: #6c757d;
    display: block;
    margin-bottom: 4px;
}

.app-staff-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #28a745;
}

/* How It Works - App Style Grid */
.app-steps-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.app-step-card {
    background: white;
    border-radius: 16px;
    padding: 20px 16px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    transition: transform 0.2s ease;
}

.app-step-card:active {
    transform: scale(0.97);
}

.app-step-number {
    position: absolute;
    top: -12px;
    right: 12px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.app-step-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.app-step-icon.step-search {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-step-icon.step-calendar {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-step-icon.step-assign {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-step-icon.step-care {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-step-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
}

.app-step-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
}

/* Bottom Navigation (from dashboard) */
.app-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-around;
    align-items: center;
    padding: 8px 0;
    padding-bottom: max(8px, env(safe-area-inset-bottom));
    z-index: 1000;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    max-width: 100vw;
    overflow-x: hidden;
}

.app-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    color: #6c757d;
    padding: 8px 16px;
    transition: color 0.2s ease;
    flex: 1;
}

.app-nav-item i {
    font-size: 1.2rem;
}

.app-nav-item span {
    font-size: 0.7rem;
    font-weight: 600;
}

.app-nav-item.active {
    color: #667eea;
}

.app-nav-item.active i {
    color: #667eea;
}

/* Desktop View */
@media (min-width: 768px) {
    .mobile-app-container {
        padding-bottom: 0;
    }
    
    .app-content {
        padding: 24px;
        padding-bottom: 24px;
    }
    
    .app-staff-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .app-steps-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    
    .app-filter-row {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Mobile Optimizations */
@media (max-width: 576px) {
    .app-staff-avatar-full {
        width: 50px;
        height: 50px;
        font-size: 1.3rem;
    }
    
    .app-staff-name-full {
        font-size: 0.9rem;
    }
    
    .app-step-icon {
        width: 48px;
        height: 48px;
        font-size: 1.3rem;
    }
    
    .app-step-title {
        font-size: 0.85rem;
    }
    
    .app-step-desc {
        font-size: 0.75rem;
    }
}

/* Marketplace Tabs */
.app-marketplace-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    background: white;
    padding: 8px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.app-tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border: none;
    border-radius: 12px;
    background: transparent;
    color: #6c757d;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.app-tab-btn i {
    font-size: 1.1rem;
}

.app-tab-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.app-tab-badge {
    background: rgba(255,255,255,0.3);
    color: inherit;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
}

.app-tab-btn.active .app-tab-badge {
    background: rgba(255,255,255,0.25);
}

/* Tab Content */
.app-tab-content {
    display: none;
}

.app-tab-content.active {
    display: block;
}

.app-section-header-compact {
    margin-bottom: 16px;
}

.app-section-header-compact .app-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.app-section-header-compact .app-section-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

/* Marketplace Grid */
.app-marketplace-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

/* Marketplace Card */
.app-marketplace-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.app-marketplace-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-color: #e0e0e0;
}

/* Distance Badge */
.app-distance-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.app-distance-badge i {
    font-size: 0.7rem;
}

.app-distance-badge.distance-nearby {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.app-distance-badge.distance-close {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.app-distance-badge.distance-medium {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.app-distance-badge.distance-far {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

/* Availability Badge */
.app-availability-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.app-availability-badge i {
    font-size: 0.5rem;
}

.app-availability-badge.availability-available {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.app-availability-badge.availability-busy {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.app-availability-badge.availability-unavailable {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

/* Marketplace Card Header */
.app-marketplace-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    padding-bottom: 16px;
}

.app-staff-avatar-marketplace {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.app-staff-avatar-marketplace.nurse-avatar {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-staff-avatar-marketplace.caregiver-avatar {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-staff-info-marketplace {
    flex: 1;
    min-width: 0;
}

.app-staff-name-marketplace {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.app-staff-id-marketplace {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Marketplace Card Body */
.app-marketplace-card-body {
    padding: 0 20px 16px;
}

.app-staff-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
}

.app-spec-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 500;
}

.app-spec-item i {
    color: #667eea;
    font-size: 0.9rem;
}

.app-marketplace-pricing {
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
}

.app-price-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.app-price-amount {
    font-size: 1.2rem;
    font-weight: 700;
    color: #10b981;
}

/* Marketplace Card Footer */
.app-marketplace-card-footer {
    padding: 16px 20px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.app-book-btn {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.app-book-btn:hover,
.app-book-btn:active {
    transform: scale(0.98);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Desktop View */
@media (min-width: 768px) {
    .app-marketplace-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .app-marketplace-tabs {
        max-width: 400px;
        margin: 0 auto 24px;
    }
}

@media (min-width: 1024px) {
    .app-marketplace-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>

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
@endsection

@extends('auth::layout')

@section('title', 'Book Staff - MMHC CRM')

@section('head')
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
@endsection

@section('content')
<!-- Mobile App View for Direct Booking -->
<div class="mobile-app-container">
    <!-- App Header (Mobile Only) -->
    <div class="app-header-mobile d-md-none">
        <div class="app-header-content">
            <div class="app-header-left">
                <a href="{{ route('staff.index') }}" class="app-back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <div class="app-header-title">Book Service</div>
                    <div class="app-header-subtitle">Complete your booking</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="app-content">
        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="text-primary">Book Healthcare Service</h2>
            <p class="text-muted">Complete your booking with {{ $staff->name }}</p>
        </div>

        <!-- Selected Staff Card (Prominent) -->
        <div class="app-selected-staff-card">
            <div class="app-selected-staff-header">
                <div class="app-selected-staff-avatar {{ $staff->isNurse() ? 'nurse' : 'caregiver' }}">
                    <i class="fas fa-{{ $staff->isNurse() ? 'user-nurse' : 'user-md' }}"></i>
                </div>
                <div class="app-selected-staff-info">
                    <h3 class="app-selected-staff-name">{{ $staff->name }}</h3>
                    <div class="app-selected-staff-badges">
                        <span class="app-staff-badge">{{ ucfirst($staff->role) }}</span>
                        <span class="app-staff-badge">{{ $staff->unique_id }}</span>
                        @if($staff->profile && $staff->profile->availability_status)
                        <span class="app-staff-badge availability-{{ $staff->profile->availability_status }}">
                            {{ ucfirst($staff->profile->availability_status) }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="app-selected-staff-change">
                    <a href="{{ route('staff.index') }}" class="app-change-btn">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Change</span>
                    </a>
                </div>
            </div>
            @if($staff->qualification || $staff->experience)
            <div class="app-selected-staff-details">
                @if($staff->qualification)
                <div class="app-staff-detail">
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ $staff->qualification }}</span>
                </div>
                @endif
                @if($staff->experience)
                <div class="app-staff-detail">
                    <i class="fas fa-briefcase"></i>
                    <span>{{ $staff->experience }} years experience</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        @if($errors->any() || session('error'))
        <div class="app-alert app-alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>
                @if(session('error'))
                    <strong>{{ session('error') }}</strong>
                @endif
                @if($errors->any())
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @endif

        <!-- Alternative Staff Suggestions (if staff unavailable) -->
        @if(session('alternatives') && count(session('alternatives')) > 0)
        <div class="app-alternatives-card">
            <div class="app-alternatives-header">
                <i class="fas fa-users"></i>
                <h4>{{ session('alternative_message', 'Alternative Staff Available') }}</h4>
            </div>
            <div class="app-alternatives-list">
                @foreach(session('alternatives') as $alternative)
                <a href="{{ route('book.staff', $alternative) }}" class="app-alternative-item">
                    <div class="app-alternative-avatar {{ $alternative->isNurse() ? 'nurse' : 'caregiver' }}">
                        <i class="fas fa-{{ $alternative->isNurse() ? 'user-nurse' : 'user-md' }}"></i>
                    </div>
                    <div class="app-alternative-info">
                        <div class="app-alternative-name">{{ $alternative->name }}</div>
                        <div class="app-alternative-details">
                            @if($alternative->qualification)
                            <span>{{ $alternative->qualification }}</span>
                            @endif
                            @if(isset($alternative->distance_km) && $alternative->distance_km !== null)
                            <span>• {{ number_format($alternative->distance_km, 1) }} km away</span>
                            @endif
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Booking Form -->
        <div class="app-form-container">
            <form method="POST" action="{{ route('book.store', $staff) }}" id="bookingForm">
                @csrf
                
                <!-- Service Type Selection -->
                <div class="app-form-section">
                    <div class="app-section-header">
                        <div class="app-section-icon-header">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <h3 class="app-section-title">Select Service Type</h3>
                    </div>
                    
                    <div class="app-service-type-grid">
                        @foreach($serviceTypes as $serviceType)
                        <div class="app-service-type-card">
                            <input type="radio" 
                                   name="service_type_id" 
                                   id="service_type_{{ $serviceType->id }}" 
                                   value="{{ $serviceType->id }}"
                                   class="app-service-type-radio"
                                   required
                                   data-price="{{ $serviceType->patient_charge }}"
                                   data-duration="{{ $serviceType->duration_hours }}">
                            <label for="service_type_{{ $serviceType->id }}" class="app-service-type-label">
                                <div class="app-service-type-header service-{{ $serviceType->duration_hours }}">
                                    <div class="app-service-type-icon">
                                        @if($serviceType->duration_hours == 24)
                                            <i class="fas fa-clock"></i>
                                        @elseif($serviceType->duration_hours == 12)
                                            <i class="fas fa-sun"></i>
                                        @elseif($serviceType->duration_hours == 8)
                                            <i class="fas fa-briefcase"></i>
                                        @else
                                            <i class="fas fa-user-md"></i>
                                        @endif
                                    </div>
                                    <div class="app-service-type-badge">
                                        @if($serviceType->duration_hours == 24)
                                            Full Day
                                        @elseif($serviceType->duration_hours == 12)
                                            Half Day
                                        @elseif($serviceType->duration_hours == 8)
                                            Standard
                                        @else
                                            Quick
                                        @endif
                                    </div>
                                </div>
                                <div class="app-service-type-body">
                                    <h5 class="app-service-type-title">{{ $serviceType->name }}</h5>
                                    <p class="app-service-type-desc">{{ $serviceType->description }}</p>
                                    
                                    <div class="app-service-type-info">
                                        <div class="app-info-item">
                                            <i class="fas fa-clock"></i>
                                            <span>
                                                @if($serviceType->duration_hours == 1)
                                                    1 Hour
                                                @else
                                                    {{ $serviceType->duration_hours }}h/Day
                                                @endif
                                            </span>
                                        </div>
                                        <div class="app-info-item price">
                                            <i class="fas fa-rupee-sign"></i>
                                            <span>₹{{ number_format($serviceType->patient_charge) }}/day</span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Booking Options -->
                <div class="app-quick-booking-section">
                    <div class="app-quick-booking-header">
                        <i class="fas fa-bolt"></i>
                        <h4>Quick Book</h4>
                    </div>
                    <div class="app-quick-presets">
                        <button type="button" class="app-quick-preset" data-days="1" data-date="{{ date('Y-m-d') }}">
                            <i class="fas fa-calendar-day"></i>
                            <span>Today</span>
                            <small>1 day</small>
                        </button>
                        <button type="button" class="app-quick-preset" data-days="1" data-date="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Tomorrow</span>
                            <small>1 day</small>
                        </button>
                        <button type="button" class="app-quick-preset" data-days="3" data-date="{{ date('Y-m-d') }}">
                            <i class="fas fa-calendar-week"></i>
                            <span>3 Days</span>
                            <small>Starting today</small>
                        </button>
                        <button type="button" class="app-quick-preset" data-days="7" data-date="{{ date('Y-m-d') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <span>1 Week</span>
                            <small>Starting today</small>
                        </button>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="app-form-section">
                    <div class="app-section-header">
                        <div class="app-section-icon-header">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 class="app-section-title">Booking Details</h3>
                    </div>

                    <!-- Date Selection with Quick Options -->
                    <div class="app-form-group">
                        <label for="start_date" class="app-form-label">
                            <i class="fas fa-calendar me-2"></i>Start Date
                        </label>
                        <div class="app-date-input-wrapper">
                            <input type="date" 
                                   class="app-form-input @error('start_date') app-input-error @enderror" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            <div class="app-quick-date-buttons">
                                <button type="button" class="app-quick-date-btn" data-date="{{ date('Y-m-d') }}" title="Today">
                                    <i class="fas fa-calendar-day"></i> Today
                                </button>
                                <button type="button" class="app-quick-date-btn" data-date="{{ date('Y-m-d', strtotime('+1 day')) }}" title="Tomorrow">
                                    <i class="fas fa-calendar-plus"></i> Tomorrow
                                </button>
                            </div>
                        </div>
                        @error('start_date')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Duration with Quick Presets -->
                    <div class="app-form-group">
                        <label for="duration_days" class="app-form-label">
                            <i class="fas fa-calendar-check me-2"></i>Duration (Days)
                        </label>
                        <div class="app-duration-wrapper">
                            <input type="number" 
                                   class="app-form-input @error('duration_days') app-input-error @enderror" 
                                   id="duration_days" 
                                   name="duration_days" 
                                   value="{{ old('duration_days', 1) }}"
                                   min="1"
                                   required>
                            <div class="app-duration-presets">
                                <button type="button" class="app-duration-preset" data-days="1">1 Day</button>
                                <button type="button" class="app-duration-preset" data-days="3">3 Days</button>
                                <button type="button" class="app-duration-preset" data-days="7">7 Days</button>
                                <button type="button" class="app-duration-preset" data-days="15">15 Days</button>
                                <button type="button" class="app-duration-preset" data-days="30">30 Days</button>
                            </div>
                        </div>
                        <div class="app-form-help">Minimum 1 day. Single visit services are 1 day only.</div>
                        @error('duration_days')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location with Quick Fill -->
                    <div class="app-form-group">
                        <label for="location" class="app-form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Service Location
                        </label>
                        <div class="app-location-wrapper">
                            <textarea class="app-form-input @error('location') app-input-error @enderror" 
                                      id="location" 
                                      name="location" 
                                      rows="3"
                                      placeholder="Enter complete address where service is needed"
                                      required>{{ old('location', $user->address) }}</textarea>
                            @if($user->address)
                            <button type="button" class="app-quick-fill-btn" data-target="location" data-value="{{ $user->address }}" title="Use saved address">
                                <i class="fas fa-magic"></i> Use Saved
                            </button>
                            @endif
                        </div>
                        @error('location')
                            <div class="app-form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Contact Info (Collapsible for Quick Booking) -->
                    <div class="app-form-group">
                        <div class="app-collapsible-header" onclick="toggleCollapsible('contactInfo')">
                            <label class="app-form-label mb-0">
                                <i class="fas fa-user me-2"></i>Contact Information
                            </label>
                            <i class="fas fa-chevron-down" id="contactInfoIcon"></i>
                        </div>
                        <div class="app-collapsible-content" id="contactInfo">
                            <div class="app-form-group mt-3">
                                <label for="contact_person" class="app-form-label">
                                    <i class="fas fa-user me-2"></i>Contact Person Name
                                </label>
                                <div class="app-input-with-quick-fill">
                                    <input type="text" 
                                           class="app-form-input @error('contact_person') app-input-error @enderror" 
                                           id="contact_person" 
                                           name="contact_person" 
                                           value="{{ old('contact_person', $user->name) }}"
                                           placeholder="Person to contact during service"
                                           required>
                                    <button type="button" class="app-quick-fill-btn-small" data-target="contact_person" data-value="{{ $user->name }}" title="Use my name">
                                        <i class="fas fa-user"></i>
                                    </button>
                                </div>
                                @error('contact_person')
                                    <div class="app-form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="app-form-group">
                                <label for="contact_phone" class="app-form-label">
                                    <i class="fas fa-phone me-2"></i>Contact Phone
                                </label>
                                <div class="app-input-with-quick-fill">
                                    <input type="tel" 
                                           class="app-form-input @error('contact_phone') app-input-error @enderror" 
                                           id="contact_phone" 
                                           name="contact_phone" 
                                           value="{{ old('contact_phone', $user->phone) }}"
                                           placeholder="10-digit phone number"
                                           pattern="[0-9]{10}"
                                           maxlength="10"
                                           required>
                                    <button type="button" class="app-quick-fill-btn-small" data-target="contact_phone" data-value="{{ $user->phone }}" title="Use my phone">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                </div>
                                @error('contact_phone')
                                    <div class="app-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Optional Fields (Collapsible) -->
                    <div class="app-form-group">
                        <div class="app-collapsible-header" onclick="toggleCollapsible('optionalFields')">
                            <label class="app-form-label mb-0">
                                <i class="fas fa-ellipsis-h me-2"></i>Additional Information (Optional)
                            </label>
                            <i class="fas fa-chevron-down" id="optionalFieldsIcon"></i>
                        </div>
                        <div class="app-collapsible-content" id="optionalFields" style="display: none;">
                            <div class="app-form-group mt-3">
                                <label for="special_requirements" class="app-form-label">
                                    <i class="fas fa-clipboard-list me-2"></i>Special Requirements
                                </label>
                                <textarea class="app-form-input @error('special_requirements') app-input-error @enderror" 
                                          id="special_requirements" 
                                          name="special_requirements" 
                                          rows="2"
                                          placeholder="Any special requirements or instructions">{{ old('special_requirements') }}</textarea>
                                @error('special_requirements')
                                    <div class="app-form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="app-form-group">
                                <label for="notes" class="app-form-label">
                                    <i class="fas fa-sticky-note me-2"></i>Additional Notes
                                </label>
                                <textarea class="app-form-input @error('notes') app-input-error @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"
                                          placeholder="Any additional information">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="app-form-error">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Summary -->
                <div class="app-cost-summary">
                    <div class="app-cost-header">
                        <h4>Cost Summary</h4>
                    </div>
                    <div class="app-cost-details">
                        <div class="app-cost-row">
                            <span>Service Rate:</span>
                            <span id="serviceRate">₹0/day</span>
                        </div>
                        <div class="app-cost-row">
                            <span>Duration:</span>
                            <span id="durationDisplay">0 days</span>
                        </div>
                        <div class="app-cost-divider"></div>
                        <div class="app-cost-row total">
                            <span>Total Amount:</span>
                            <span id="totalAmount">₹0</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="app-form-actions">
                    <a href="{{ route('staff.index') }}" class="app-btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="app-btn-submit">
                        <i class="fas fa-check me-2"></i>Confirm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Navigation -->
    @include('auth::components.bottom-nav')
</div>

<style>
/* Mobile App Container - Ensure proper padding for bottom nav */
.mobile-app-container {
    position: relative;
    min-height: 100vh;
    background: #f5f7fa;
    padding-bottom: 140px !important;
    margin-top: 0;
}

@media (max-width: 767px) {
    .mobile-app-container {
        padding-bottom: 160px !important;
    }
}

/* App Content - Proper spacing */
.app-content {
    position: relative;
    padding: 16px;
    padding-bottom: 20px;
}

@media (max-width: 767px) {
    .app-content {
        padding-bottom: 40px;
    }
}

/* Reuse existing styles from create.blade.php and add new ones */
.app-selected-staff-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #667eea;
}

.app-selected-staff-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.app-selected-staff-avatar {
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

.app-selected-staff-avatar.nurse {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-selected-staff-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-selected-staff-info {
    flex: 1;
    min-width: 0;
}

.app-selected-staff-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 8px 0;
}

.app-selected-staff-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.app-staff-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #f0f4ff;
    color: #667eea;
}

.app-staff-badge.availability-available {
    background: #d1fae5;
    color: #059669;
}

.app-staff-badge.availability-busy {
    background: #fef3c7;
    color: #d97706;
}

.app-selected-staff-change {
    flex-shrink: 0;
}

.app-change-btn {
    padding: 8px 16px;
    border-radius: 12px;
    background: #f8f9fa;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.app-change-btn:hover,
.app-change-btn:active {
    background: #e9ecef;
    color: #2c3e50;
}

.app-selected-staff-details {
    display: flex;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.app-staff-detail {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #6c757d;
}

.app-staff-detail i {
    color: #667eea;
}

/* Alternatives Card */
.app-alternatives-card {
    background: #fff7ed;
    border: 2px solid #fb923c;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.app-alternatives-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.app-alternatives-header i {
    font-size: 1.5rem;
    color: #f59e0b;
}

.app-alternatives-header h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #92400e;
    margin: 0;
}

.app-alternatives-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.app-alternative-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.app-alternative-item:hover,
.app-alternative-item:active {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: #fb923c;
}

.app-alternative-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    flex-shrink: 0;
}

.app-alternative-avatar.nurse {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.app-alternative-avatar.caregiver {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.app-alternative-info {
    flex: 1;
    min-width: 0;
}

.app-alternative-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.app-alternative-details {
    font-size: 0.8rem;
    color: #6c757d;
}

.app-alternative-item i.fa-chevron-right {
    color: #9ca3af;
    font-size: 0.9rem;
}

/* Cost Summary */
.app-cost-summary {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.app-cost-header h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 16px 0;
}

.app-cost-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.app-cost-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #6c757d;
}

.app-cost-row.total {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    padding-top: 12px;
}

.app-cost-row.total span:last-child {
    color: #10b981;
    font-size: 1.3rem;
}

.app-cost-divider {
    height: 1px;
    background: #e9ecef;
    margin: 4px 0;
}

/* Service Type Grid - Reuse from create.blade.php but enhance */
.app-service-type-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.app-service-type-card {
    position: relative;
}

.app-service-type-radio {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.app-service-type-label {
    display: block;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 16px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.app-service-type-radio:checked + .app-service-type-label {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.app-service-type-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding: 12px;
    border-radius: 12px;
    color: white;
}

.app-service-type-header.service-24 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.app-service-type-header.service-12 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.app-service-type-header.service-8 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.app-service-type-header.service-1 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.app-service-type-icon {
    font-size: 1.5rem;
}

.app-service-type-badge {
    font-size: 0.75rem;
    font-weight: 700;
    background: rgba(255,255,255,0.25);
    padding: 4px 10px;
    border-radius: 12px;
}

.app-service-type-body {
    text-align: center;
}

.app-service-type-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px 0;
}

.app-service-type-desc {
    font-size: 0.8rem;
    color: #6c757d;
    margin: 0 0 12px 0;
    line-height: 1.4;
}

.app-service-type-info {
    display: flex;
    justify-content: space-around;
    gap: 8px;
    font-size: 0.8rem;
}

.app-info-item {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #6c757d;
}

.app-info-item.price {
    color: #10b981;
    font-weight: 700;
}

/* Form Elements - Reuse from edit.blade.php */
.app-form-container {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 16px;
    padding-bottom: 40px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    z-index: 1;
}

.app-form-section {
    margin-bottom: 24px;
}

.app-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.app-section-icon-header {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.app-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.app-form-group {
    margin-bottom: 20px;
}

.app-form-label {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.app-form-label i {
    color: #667eea;
    width: 20px;
}

.app-form-input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 0.95rem;
    background: white;
    color: #2c3e50;
    transition: all 0.2s ease;
    font-family: inherit;
}

.app-form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: #fafbff;
}

.app-input-error {
    border-color: #dc2626 !important;
    background: #fef2f2 !important;
}

.app-form-help {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.app-form-error {
    font-size: 0.8rem;
    color: #dc2626;
    margin-top: 6px;
}

.app-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    position: relative;
    z-index: 100;
}

.app-btn-secondary {
    flex: 1;
    padding: 14px;
    border-radius: 12px;
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #e9ecef;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-btn-submit {
    flex: 1;
    padding: 14px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease;
    position: relative;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.app-btn-submit:hover {
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    transform: translateY(-2px);
}

.app-btn-submit:active {
    transform: scale(0.98);
}

.app-alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 16px;
    display: flex;
    align-items: start;
    gap: 8px;
}

.app-alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

@media (min-width: 768px) {
    .app-service-type-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 576px) {
    .app-service-type-grid {
        grid-template-columns: 1fr;
    }
}

/* Quick Booking Enhancements */
.app-quick-booking-section {
    background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
    border: 2px solid #e0e7ff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
}

.app-quick-booking-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.app-quick-booking-header i {
    font-size: 1.3rem;
    color: #667eea;
}

.app-quick-booking-header h4 {
    font-size: 1rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.app-quick-presets {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.app-quick-preset {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.app-quick-preset:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.app-quick-preset.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
}

.app-quick-preset i {
    font-size: 1.2rem;
    color: #667eea;
}

.app-quick-preset.active i {
    color: white;
}

.app-quick-preset span {
    font-size: 0.85rem;
    font-weight: 600;
}

.app-quick-preset small {
    font-size: 0.7rem;
    opacity: 0.8;
}

.app-date-input-wrapper {
    position: relative;
}

.app-quick-date-buttons {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}

.app-quick-date-btn {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.app-quick-date-btn:hover {
    background: #e9ecef;
    border-color: #667eea;
    color: #667eea;
}

.app-duration-wrapper {
    position: relative;
}

.app-duration-presets {
    display: flex;
    gap: 6px;
    margin-top: 8px;
    flex-wrap: wrap;
}

.app-duration-preset {
    padding: 6px 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.app-duration-preset:hover {
    background: #e9ecef;
    border-color: #667eea;
    color: #667eea;
}

.app-duration-preset.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
}

.app-location-wrapper {
    position: relative;
}

.app-quick-fill-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 6px 12px;
    border: 1px solid #667eea;
    border-radius: 8px;
    background: white;
    color: #667eea;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.app-quick-fill-btn:hover {
    background: #667eea;
    color: white;
}

.app-input-with-quick-fill {
    position: relative;
    display: flex;
    align-items: center;
}

.app-input-with-quick-fill .app-form-input {
    padding-right: 45px;
}

.app-quick-fill-btn-small {
    position: absolute;
    right: 8px;
    width: 32px;
    height: 32px;
    border: 1px solid #667eea;
    border-radius: 8px;
    background: white;
    color: #667eea;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.app-quick-fill-btn-small:hover {
    background: #667eea;
    color: white;
}

.app-collapsible-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.app-collapsible-header:hover {
    background: #e9ecef;
}

.app-collapsible-content {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.app-booking-progress {
    margin-top: 24px;
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 16px;
    position: relative;
    z-index: 1;
}

.app-progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.app-progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.app-progress-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.app-progress-step.active .step-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    transform: scale(1.1);
}

.step-label {
    font-size: 0.75rem;
    color: #9ca3af;
    font-weight: 600;
    text-align: center;
}

.app-progress-step.active .step-label {
    color: #667eea;
}

.btn-loading {
    display: none;
}

@media (max-width: 768px) {
    .app-quick-presets {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .app-progress-steps {
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .app-progress-steps::before {
        display: none;
    }
    
    .app-progress-step {
        flex: 0 0 calc(50% - 6px);
    }
}
</style>

<script>
// Enhanced Quick Booking with Real-time Updates
document.addEventListener('DOMContentLoaded', function() {
    const serviceTypeRadios = document.querySelectorAll('.app-service-type-radio');
    const durationInput = document.getElementById('duration_days');
    const startDateInput = document.getElementById('start_date');
    const serviceRateSpan = document.getElementById('serviceRate');
    const durationDisplaySpan = document.getElementById('durationDisplay');
    const totalAmountSpan = document.getElementById('totalAmount');
    const submitBtn = document.getElementById('submitBtn');
    const bookingForm = document.getElementById('bookingForm');

    // Calculate cost
    function calculateCost() {
        const selectedService = document.querySelector('.app-service-type-radio:checked');
        const duration = parseInt(durationInput.value) || 0;
        
        if (selectedService && duration > 0) {
            const pricePerDay = parseFloat(selectedService.dataset.price) || 0;
            const total = pricePerDay * duration;
            
            serviceRateSpan.textContent = `₹${pricePerDay.toLocaleString('en-IN')}/day`;
            durationDisplaySpan.textContent = `${duration} ${duration === 1 ? 'day' : 'days'}`;
            totalAmountSpan.textContent = `₹${total.toLocaleString('en-IN')}`;
            updateProgress(2);
        } else {
            serviceRateSpan.textContent = '₹0/day';
            durationDisplaySpan.textContent = '0 days';
            totalAmountSpan.textContent = '₹0';
        }
    }

    // Update progress indicator
    function updateProgress(step) {
        const steps = document.querySelectorAll('.app-progress-step');
        steps.forEach((s, index) => {
            if (index + 1 <= step) {
                s.classList.add('active');
            }
        });
    }

    // Quick preset buttons
    document.querySelectorAll('.app-quick-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            const date = this.dataset.date;
            durationInput.value = days;
            startDateInput.value = date;
            document.querySelectorAll('.app-quick-preset').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            calculateCost();
            updateProgress(3);
            document.querySelector('.app-form-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Quick date buttons
    document.querySelectorAll('.app-quick-date-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            startDateInput.value = this.dataset.date;
            updateProgress(3);
        });
    });

    // Duration presets
    document.querySelectorAll('.app-duration-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            durationInput.value = days;
            document.querySelectorAll('.app-duration-preset').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            calculateCost();
            updateProgress(3);
        });
    });

    // Quick fill buttons
    document.querySelectorAll('.app-quick-fill-btn, .app-quick-fill-btn-small').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const value = this.dataset.value;
            const input = document.getElementById(target);
            if (input) {
                input.value = value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 200);
            }
        });
    });

    // Service type selection
    serviceTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            calculateCost();
            updateProgress(2);
        });
    });

    // Form inputs
    durationInput?.addEventListener('input', function() {
        calculateCost();
        updateProgress(3);
    });

    startDateInput?.addEventListener('change', function() {
        updateProgress(3);
    });

    // Form submission
    bookingForm?.addEventListener('submit', function(e) {
        const selectedService = document.querySelector('.app-service-type-radio:checked');
        if (!selectedService) {
            e.preventDefault();
            alert('Please select a service type');
            document.querySelector('.app-service-type-grid')?.scrollIntoView({ behavior: 'smooth' });
            return false;
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            const btnContent = submitBtn.querySelector('.btn-content');
            const btnLoading = submitBtn.querySelector('.btn-loading');
            if (btnContent) btnContent.style.display = 'none';
            if (btnLoading) btnLoading.style.display = 'inline-flex';
            updateProgress(4);
        }
    });

    // Auto-update progress
    ['contact_person', 'contact_phone', 'location'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        field?.addEventListener('input', function() {
            if (this.value.trim()) updateProgress(3);
        });
    });

    // Initial setup
    calculateCost();
    updateProgress(1);
});

// Toggle collapsible sections
function toggleCollapsible(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById(id + 'Icon');
    if (content) {
        if (content.style.display === 'none') {
            content.style.display = 'block';
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        } else {
            content.style.display = 'none';
            if (icon) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn && !submitBtn.disabled) submitBtn.click();
    }
    if (e.key >= '1' && e.key <= '4' && !e.ctrlKey && !e.metaKey) {
        const presets = document.querySelectorAll('.app-quick-preset');
        if (presets[e.key - 1]) presets[e.key - 1].click();
    }
});
</script>
@endsection


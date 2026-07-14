@extends('auth::layout')

@section('title', 'Book Staff - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @include('services::partials.mobile-assets')
@endsection

@section('content')
<div class="mobile-app-container mmhc-page-book hc-mobile-shell" data-mmhc-ptr>
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
        @include('services::partials.booking-flow-steps', ['currentStep' => 'book'])

        <!-- Desktop Header -->
        <div class="d-none d-md-block mb-4">
            <h2 class="text-primary">Book Healthcare Service</h2>
            <p class="text-muted">Complete your booking with {{ $staff->name }}</p>
        </div>

        <!-- Subscription Status Alert -->
        @if(isset($hasActiveSubscription) && $hasActiveSubscription && $activeSubscription)
        <div class="app-subscription-alert">
            <div class="subscription-alert-content">
                <i class="fas fa-gift"></i>
                <div>
                    <strong>You have an active subscription!</strong>
                    <p>All services will be FREE. Your subscription: <strong>{{ $activeSubscription->plan->name }}</strong></p>
                </div>
            </div>
        </div>
        @endif

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
                            <span>â€¢ {{ number_format($alternative->distance_km, 1) }} km away</span>
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
                                            <span class="service-price">
                                                @if(isset($hasActiveSubscription) && $hasActiveSubscription)
                                                    <span class="text-success fw-bold">FREE</span>
                                                    <small class="text-muted text-decoration-line-through ms-1">â‚¹{{ number_format($serviceType->patient_charge) }}/day</small>
                                                @else
                                                    â‚¹{{ number_format($serviceType->patient_charge) }}/day
                                                @endif
                                            </span>
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

                <!-- Cost + confirm dock (mobile: single footer; desktop: normal flow) -->
                <div class="app-book-dock">
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
                        @if(!($hasActiveSubscription ?? false))
                        <p class="app-payment-note small text-muted mb-0 mt-2 px-2">
                            <i class="fas fa-info-circle me-1"></i>
                            After booking you’ll pay the visit fee online via Razorpay (UPI, card, wallet). Office / cash collection by MMHC is still available as a fallback.
                        </p>
                        @endif
                    </div>

                    <div class="app-form-actions">
                        <a href="{{ route('staff.index') }}" class="app-btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="app-btn-submit">
                            <i class="fas fa-check me-2"></i>Confirm Booking
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Navigation -->
</div>
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
            const hasSubscription = {{ isset($hasActiveSubscription) && $hasActiveSubscription ? 'true' : 'false' }};
            const displayPrice = hasSubscription ? 0 : pricePerDay;
            const total = displayPrice * duration;

            if (hasSubscription) {
                serviceRateSpan.innerHTML = '<span class="text-success fw-bold">FREE</span> <small class="text-muted text-decoration-line-through">â‚¹' + pricePerDay.toLocaleString('en-IN') + '/day</small>';
            } else {
                serviceRateSpan.textContent = `â‚¹${pricePerDay.toLocaleString('en-IN')}/day`;
            }
            durationDisplaySpan.textContent = `${duration} ${duration === 1 ? 'day' : 'days'}`;
            if (hasSubscription) {
                totalAmountSpan.innerHTML = '<span class="text-success fw-bold">FREE</span>';
            } else {
                totalAmountSpan.textContent = `â‚¹${total.toLocaleString('en-IN')}`;
            }
            updateProgress(2);
        } else {
            serviceRateSpan.textContent = 'â‚¹0/day';
            durationDisplaySpan.textContent = '0 days';
            totalAmountSpan.textContent = 'â‚¹0';
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


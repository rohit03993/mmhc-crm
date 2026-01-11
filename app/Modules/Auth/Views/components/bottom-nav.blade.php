<!-- Bottom Navigation Bar (Mobile Only) -->
<nav class="app-bottom-nav d-md-none">
    <a href="{{ route('dashboard') }}" class="app-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>Home</span>
    </a>
    @if(auth()->user()->isPatient())
        <a href="{{ route('services.my-requests') }}" class="app-nav-item {{ request()->routeIs('services.my-requests') || request()->routeIs('services.show') ? 'active' : '' }}">
            <i class="fas fa-list"></i>
            <span>Requests</span>
        </a>
        <a href="{{ route('staff.index') }}" class="app-nav-item {{ request()->routeIs('staff.index') || request()->routeIs('book.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Staff</span>
        </a>
    @elseif(auth()->user()->isStaff())
        <a href="{{ route('staff.dashboard') }}" class="app-nav-item {{ request()->routeIs('staff.dashboard') || request()->routeIs('staff.service-details') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Assignments</span>
        </a>
        <a href="{{ route('rewards.index') }}" class="app-nav-item {{ request()->routeIs('rewards.*') ? 'active' : '' }}">
            <i class="fas fa-gift"></i>
            <span>Rewards</span>
        </a>
    @endif
    <button class="app-nav-item app-menu-trigger" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
        <span>More</span>
    </button>
</nav>

<!-- Mobile Menu Overlay -->
<div class="app-mobile-menu-overlay d-md-none" id="mobileMenuOverlay" onclick="toggleMobileMenu()">
    <div class="app-mobile-menu" onclick="event.stopPropagation()">
        <div class="app-mobile-menu-header">
            <h3>Menu</h3>
            <button class="app-menu-close" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="app-mobile-menu-content">
            <a href="{{ route('profile.index') }}" class="app-menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a>
            
            <a href="{{ route('documents.index') }}" class="app-menu-item {{ request()->routeIs('documents.*') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
            
            @if(auth()->user()->isPatient())
            <a href="{{ route('staff.index') }}" class="app-menu-item {{ request()->routeIs('staff.index') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-users"></i>
                <span>Available Staff</span>
            </a>
            
            <a href="{{ route('plans.index') }}" class="app-menu-item {{ request()->routeIs('plans.*') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-clipboard-list"></i>
                <span>Healthcare Plans</span>
            </a>
            
            <a href="{{ route('subscriptions.index') }}" class="app-menu-item {{ request()->routeIs('subscriptions.*') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-credit-card"></i>
                <span>My Subscriptions</span>
            </a>
            @endif
            
            @if(auth()->user()->isStaff())
            <a href="{{ route('staff.dashboard') }}" class="app-menu-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-tasks"></i>
                <span>My Assignments</span>
            </a>
            
            <a href="{{ route('rewards.index') }}" class="app-menu-item {{ request()->routeIs('rewards.*') ? 'active' : '' }}" onclick="toggleMobileMenu()">
                <i class="fas fa-gift"></i>
                <span>Rewards & Points</span>
            </a>
            @endif
            
            <div class="app-menu-divider"></div>
            
            <form method="POST" action="{{ route('auth.logout') }}" class="app-menu-item-form">
                @csrf
                <button type="submit" class="app-menu-item app-menu-item-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Bottom Navigation - Mobile Only */
.app-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    background: white;
    border-top: 1px solid #e9ecef;
    justify-content: space-around;
    align-items: center;
    padding: 10px 0;
    padding-bottom: max(10px, env(safe-area-inset-bottom));
    z-index: 9999;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    max-width: 100vw;
    overflow-x: hidden;
    margin: 0;
}

/* Hide on desktop (md and up) */
@media (min-width: 768px) {
    .app-bottom-nav {
        display: none !important;
    }
}

.app-nav-item {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    color: #6c757d;
    padding: 8px 12px;
    transition: color 0.2s ease;
    flex: 1;
    min-width: 0;
}

.app-nav-item i {
    font-size: 1.2rem;
    display: block;
}

.app-nav-item span {
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.app-nav-item.active {
    color: #667eea !important;
}

.app-nav-item.active i {
    color: #667eea !important;
}

.app-menu-trigger {
    background: none;
    border: none;
    cursor: pointer;
}

.app-menu-trigger:active {
    opacity: 0.7;
}

/* Mobile Menu Overlay */
.app-mobile-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: none;
    align-items: flex-end;
    animation: fadeIn 0.2s ease;
}

.app-mobile-menu-overlay.show {
    display: flex;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.app-mobile-menu {
    background: white;
    width: 100%;
    max-height: 80vh;
    border-radius: 20px 20px 0 0;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}

.app-mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
}

.app-mobile-menu-header h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.app-menu-close {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.app-menu-close:active {
    background: #e9ecef;
    transform: scale(0.95);
}

.app-mobile-menu-content {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
    padding-bottom: max(8px, env(safe-area-inset-bottom));
}

.app-menu-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.app-menu-item i {
    width: 24px;
    text-align: center;
    color: #667eea;
    font-size: 1.1rem;
}

.app-menu-item:hover,
.app-menu-item:active {
    background: #f8f9fa;
    color: #667eea;
}

.app-menu-item.active {
    background: #f0f4ff;
    color: #667eea;
    border-left: 4px solid #667eea;
}

.app-menu-item-logout {
    color: #dc2626 !important;
}

.app-menu-item-logout i {
    color: #dc2626 !important;
}

.app-menu-item-logout:hover,
.app-menu-item-logout:active {
    background: #fee2e2 !important;
    color: #dc2626 !important;
}

.app-menu-item-form {
    margin: 0;
}

.app-menu-divider {
    height: 1px;
    background: #e9ecef;
    margin: 8px 20px;
}

/* Show on mobile only */
@media (max-width: 767px) {
    body {
        padding-bottom: 0 !important;
    }
    
    /* Ensure main content has space for bottom nav */
    .main-content {
        padding-bottom: 80px !important;
        margin-bottom: 0;
    }
    
    /* Add padding to container on mobile if needed */
    .container-fluid,
    .container {
        padding-bottom: 0;
    }
    
    .app-bottom-nav {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    body.menu-open {
        overflow: hidden;
    }
}

/* Explicitly hide on desktop (md and above) */
@media (min-width: 768px) {
    .app-bottom-nav {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
}
</style>

<script>
function toggleMobileMenu() {
    const overlay = document.getElementById('mobileMenuOverlay');
    const body = document.body;
    
    if (overlay.classList.contains('show')) {
        overlay.classList.remove('show');
        body.classList.remove('menu-open');
    } else {
        overlay.classList.add('show');
        body.classList.add('menu-open');
    }
}

// Close menu on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const overlay = document.getElementById('mobileMenuOverlay');
        if (overlay && overlay.classList.contains('show')) {
            toggleMobileMenu();
        }
    }
});
</script>


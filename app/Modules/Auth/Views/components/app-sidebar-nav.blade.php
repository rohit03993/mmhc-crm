{{-- Shared sidebar link list (desktop column + mobile offcanvas). --}}
<ul class="nav flex-column">
    @if(auth()->user()->hasAcademicRole())
    <li class="nav-item mt-2">
        <span class="nav-link text-white-50 text-uppercase small px-3 py-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Academics</span>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.dashboard') ? 'active' : '' }}" href="{{ route('academics.dashboard') }}">
            <i class="fas fa-graduation-cap me-2"></i>
            Academics overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.open-classrooms.*') ? 'active' : '' }}" href="{{ route('academics.open-classrooms.index') }}">
            <i class="fas fa-door-open me-2"></i>
            Open classrooms
        </a>
    </li>
    @if(auth()->user()->role === 'institution_admin')
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.enrollments.*') ? 'active' : '' }}" href="{{ route('academics.enrollments.index') }}">
            <i class="fas fa-user-clock me-2"></i>
            Pending enrollments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.students.*') ? 'active' : '' }}" href="{{ route('academics.students.index') }}">
            <i class="fas fa-user-graduate me-2"></i>
            Students
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.batches.*') ? 'active' : '' }}" href="{{ route('academics.batches.index') }}">
            <i class="fas fa-layer-group me-2"></i>
            Batches
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.subjects.*') ? 'active' : '' }}" href="{{ route('academics.subjects.index') }}">
            <i class="fas fa-book me-2"></i>
            Subjects
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.topics.*') ? 'active' : '' }}" href="{{ route('academics.topics.index') }}">
            <i class="fas fa-list-ul me-2"></i>
            Topics
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.assignments.*') ? 'active' : '' }}" href="{{ route('academics.assignments.index') }}">
            <i class="fas fa-tasks me-2"></i>
            Assignments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.faculty.*') ? 'active' : '' }}" href="{{ route('academics.faculty.index') }}">
            <i class="fas fa-chalkboard-teacher me-2"></i>
            Faculty
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.reports.*') ? 'active' : '' }}" href="{{ route('academics.reports.index') }}">
            <i class="fas fa-chart-bar me-2"></i>
            Reports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.exams.*') ? 'active' : '' }}" href="{{ route('academics.exams.index') }}">
            <i class="fas fa-question-circle me-2"></i>
            Quizzes &amp; exams
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.attendance.index') || request()->routeIs('academics.attendance.mark') ? 'active' : '' }}" href="{{ route('academics.attendance.index') }}">
            <i class="fas fa-calendar-check me-2"></i>
            Mark attendance
        </a>
    </li>
    @endif
    @if(auth()->user()->role === 'faculty')
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.mentorship.*') ? 'active' : '' }}" href="{{ route('academics.mentorship.index') }}">
            <i class="fas fa-hands-helping me-2"></i>
            Mentorship
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.topics.*') ? 'active' : '' }}" href="{{ route('academics.topics.index') }}">
            <i class="fas fa-list-ul me-2"></i>
            Topics
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.assignments.*') ? 'active' : '' }}" href="{{ route('academics.assignments.index') }}">
            <i class="fas fa-tasks me-2"></i>
            Assignments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.reports.*') ? 'active' : '' }}" href="{{ route('academics.reports.index') }}">
            <i class="fas fa-chart-bar me-2"></i>
            Reports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.exams.*') ? 'active' : '' }}" href="{{ route('academics.exams.index') }}">
            <i class="fas fa-question-circle me-2"></i>
            Quizzes &amp; exams
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.attendance.index') || request()->routeIs('academics.attendance.mark') ? 'active' : '' }}" href="{{ route('academics.attendance.index') }}">
            <i class="fas fa-calendar-check me-2"></i>
            Mark attendance
        </a>
    </li>
    @endif
    @if(auth()->user()->role === 'student')
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.mentorship.*') ? 'active' : '' }}" href="{{ route('academics.mentorship.index') }}">
            <i class="fas fa-hands-helping me-2"></i>
            Mentorship
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.my-assignments') || request()->routeIs('academics.submit.*') ? 'active' : '' }}" href="{{ route('academics.my-assignments') }}">
            <i class="fas fa-tasks me-2"></i>
            My Assignments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.learning-resources') || request()->routeIs('academics.topics.student-library') ? 'active' : '' }}" href="{{ route('academics.learning-resources') }}">
            <i class="fas fa-photo-video me-2"></i>
            Learning resources
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.attendance.my') ? 'active' : '' }}" href="{{ route('academics.attendance.my') }}">
            <i class="fas fa-calendar-check me-2"></i>
            My attendance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.exams.*') ? 'active' : '' }}" href="{{ route('academics.exams.index') }}">
            <i class="fas fa-question-circle me-2"></i>
            Quizzes &amp; exams
        </a>
    </li>
    @endif
    <li class="nav-item mt-3">
        <a class="nav-link text-white {{ request()->routeIs('community.*') ? 'active' : '' }}" href="{{ route('community.index') }}">
            <i class="fas fa-users me-2"></i>
            Community
        </a>
    </li>
    @else
    <li class="nav-item">
        <a class="nav-link text-white {{ (request()->routeIs('community.*') || request()->routeIs('dashboard')) ? 'active' : '' }}" href="{{ route('community.index') }}">
            <i class="fas fa-home me-2"></i>
            Community Hub
        </a>
    </li>
    @endif

    @if(!auth()->user()->isAdmin() && !auth()->user()->hasAcademicRole())
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
            <i class="fas fa-user me-2"></i>
            My Profile
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
            <i class="fas fa-file-alt me-2"></i>
            Documents
        </a>
    </li>
    @endif

    @if(auth()->user()->isPatient())
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('staff.index') }}">
            <i class="fas fa-users me-2"></i>
            Available Staff
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('staff.index') }}">
            <i class="fas fa-users me-2"></i>
            Find Staff
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('services.my-requests') }}">
            <i class="fas fa-list me-2"></i>
            My Requests
        </a>
    </li>
    @endif

    @if(auth()->user()->isStaff())
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard
        </a>
    </li>

    @if(in_array(auth()->user()->role, ['nurse', 'caregiver'], true))
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.mentorship.*') ? 'active' : '' }}" href="{{ route('academics.mentorship.index') }}">
            <i class="fas fa-hands-helping me-2"></i>
            Mentorship
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('academics.open-classrooms.*') ? 'active' : '' }}" href="{{ route('academics.open-classrooms.index') }}">
            <i class="fas fa-door-open me-2"></i>
            Open classrooms
        </a>
    </li>
    @endif

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.assignments*') ? 'active' : '' }}" href="{{ route('staff.dashboard') }}#assignments">
            <i class="fas fa-tasks me-2"></i>
            My Assignments
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.rewards.*') ? 'active' : '' }}" href="{{ route('staff.rewards.index') }}">
            <i class="fas fa-gift me-2"></i>
            Patient Rewards
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.staff-referrals.*') ? 'active' : '' }}" href="{{ route('staff.staff-referrals.index') }}">
            <i class="fas fa-user-friends me-2"></i>
            Staff Referrals
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.subscription-referrals.*') ? 'active' : '' }}" href="{{ route('staff.subscription-referrals.index') }}">
            <i class="fas fa-credit-card me-2"></i>
            Subscription Referrals
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.payments.*') ? 'active' : '' }}" href="{{ route('staff.payments.settings') }}">
            <i class="fas fa-wallet me-2"></i>
            Payment Settings
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white {{ request()->routeIs('staff.payments.history') ? 'active' : '' }}" href="{{ route('staff.payments.history') }}">
            <i class="fas fa-history me-2"></i>
            Payment History
        </a>
    </li>

    <li class="nav-item d-none">
        <a class="nav-link text-white {{ request()->routeIs('rewards.*') ? 'active' : '' }}" href="{{ route('rewards.index') }}">
            <i class="fas fa-gift me-2"></i>
            Rewards & Points
        </a>
    </li>
    @endif

    @if(auth()->user()->isPatient())
    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('plans.index') }}">
            <i class="fas fa-clipboard-list me-2"></i>
            Healthcare Plans
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('subscriptions.index') }}">
            <i class="fas fa-credit-card me-2"></i>
            My Subscriptions
        </a>
    </li>
    @endif

    @if(auth()->user()->isAdmin())
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.users') || request()->routeIs('admin.profiles*') || request()->routeIs('admin.staff.id-card') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                <i class="fas fa-users me-2"></i>
                Manage Users
            </a>
        </li>

        <li class="nav-item mt-2">
            <span class="nav-link text-white-50 text-uppercase small px-3 py-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Academics</span>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('academics.dashboard') ? 'active' : '' }}" href="{{ route('academics.dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                Academics overview
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('academics.institutions.*') ? 'active' : '' }}" href="{{ route('academics.institutions.index') }}">
                <i class="fas fa-university me-2"></i>
                Institutes &amp; codes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.users') && request('segment') === 'institute_admins' ? 'active' : '' }}" href="{{ route('admin.users', ['segment' => 'institute_admins']) }}">
                <i class="fas fa-user-shield me-2"></i>
                Institute admins
            </a>
        </li>
        <li class="nav-item mt-2">
            <span class="nav-link text-white-50 text-uppercase small px-3 py-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Healthcare &amp; CRM</span>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.service-requests*') ? 'active' : '' }}" href="{{ route('admin.service-requests') }}">
                <i class="fas fa-tasks me-2"></i>
                Service Requests
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.referrals.*') ? 'active' : '' }}" href="{{ route('admin.referrals.index') }}">
                <i class="fas fa-share-alt me-2"></i>
                Referral Management
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.rewards.*') ? 'active' : '' }}" href="{{ route('admin.rewards.index') }}">
                <i class="fas fa-star me-2"></i>
                Reward Submissions (Patient Details)
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}" href="{{ route('admin.subscriptions') }}">
                <i class="fas fa-list-alt me-2"></i>
                Manage Subscriptions
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.plans*') ? 'active' : '' }}" href="{{ route('admin.plans') }}">
                <i class="fas fa-tags me-2"></i>
                Manage Plans
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.subscription-settings*') ? 'active' : '' }}" href="{{ route('admin.subscription-settings') }}">
                <i class="fas fa-cog me-2"></i>
                Subscription Settings
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.subscription-coupons*') ? 'active' : '' }}" href="{{ route('admin.subscription-coupons.index') }}">
                <i class="fas fa-ticket-alt me-2"></i>
                Coupons
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.plan-payments*') ? 'active' : '' }}" href="{{ route('admin.plan-payments') }}">
                <i class="fas fa-file-invoice-dollar me-2"></i>
                Customer Payments
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}">
                <i class="fas fa-money-bill-wave me-2"></i>
                Staff Payments
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.visit-refunds*') ? 'active' : '' }}" href="{{ route('admin.visit-refunds') }}">
                <i class="fas fa-undo-alt me-2"></i>
                Visit Refunds
            </a>
        </li>

        <li class="nav-item mt-3">
            <span class="nav-link text-white-50 text-uppercase small px-3 py-2" style="font-size: 0.7rem; letter-spacing: 0.05em;">Website front page</span>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.site-settings*') ? 'active' : '' }}" href="{{ route('admin.site-settings.index') }}">
                <i class="fas fa-cog me-2"></i>
                Site Settings (Founder & logo)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.featured-team*') ? 'active' : '' }}" href="{{ route('admin.featured-team.index') }}">
                <i class="fas fa-users me-2"></i>
                Expert Nursing Team
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.testimonials*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}">
                <i class="fas fa-quote-left me-2"></i>
                What Our Patients Say
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white {{ request()->routeIs('admin.achievement-media*') ? 'active' : '' }}" href="{{ route('admin.achievement-media.index') }}">
                <i class="fas fa-trophy me-2"></i>
                Achievements & Media
            </a>
        </li>

        <li class="nav-item mt-4 pt-3 border-top border-secondary">
            <a class="nav-link text-white {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}">
                <i class="fas fa-database me-2"></i>
                Site backups
            </a>
        </li>

        <li class="nav-item mt-4 pt-3 border-top border-danger">
            <a class="nav-link text-danger {{ request()->routeIs('admin.system.reset') ? 'active' : '' }}" href="{{ route('admin.system.reset') }}">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Reset System Data
            </a>
            <small class="text-danger-50 ms-4 d-block" style="font-size: 0.7rem;">⚠️ Danger Zone</small>
        </li>
    @endif

    <li class="nav-item mt-3 mmhc-pwa-install-nav">
        <a class="nav-link text-white" href="{{ url('/install') }}" data-mmhc-pwa-install title="Install MeD Miracle on your phone">
            <i class="fas fa-mobile-alt me-2"></i>
            Install App
        </a>
    </li>

    <li class="nav-item mt-3">
        <form method="POST" action="{{ route('auth.logout') }}">
            @csrf
            <button type="submit" class="nav-link text-white btn btn-link">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
            </button>
        </form>
    </li>
</ul>

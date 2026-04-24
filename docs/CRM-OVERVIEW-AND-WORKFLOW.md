# MMHC CRM — Complete Overview & Workflow

**For:** Developers joining the project, stakeholders, and anyone who needs to understand what this system does and how it works.

**In simple words:** This is a **Healthcare CRM** (Customer Relationship Management) for **MeD Miracle Health Care**. It lets **patients** book nurses/caregivers at home, **staff** (nurses & caregivers) manage their bookings and earnings, and **admins** run the business (plans, payments, content, users).

---

## Table of Contents

1. [What This CRM Does (Simple Explanation)](#1-what-this-crm-does-simple-explanation)
2. [Technology Used (For Developers)](#2-technology-used-for-developers)
3. [User Roles & Who Can Do What](#3-user-roles--who-can-do-what)
4. [Main Workflows (Step by Step)](#4-main-workflows-step-by-step)
5. [Features & Functions (By Module)](#5-features--functions-by-module)
6. [Important Concepts (Data & Business Logic)](#6-important-concepts-data--business-logic)
7. [Quick Reference for Developers](#7-quick-reference-for-developers)
8. [Academics Module (LMS)](#8-academics-module-lms)

---

## 1. What This CRM Does (Simple Explanation)

### In One Sentence

**Patients** can sign up, buy a **subscription plan** (optional), **choose a nurse or caregiver**, and **book home care**. **Nurses and caregivers** get assigned to these bookings, do the service, and earn money. **Admins** manage users, plans, payments, and the **public website** (landing page, testimonials, team).

### For a Layman

- **Website (public):** Anyone can visit the home page. They see healthcare plans, team, testimonials, and calls to “Register” or “Book care.”
- **Patient:** Registers, logs in, can subscribe to a plan (monthly/yearly) for benefits. They see a list of **staff** (nurses and caregivers), pick one, and **book** a service (e.g. nursing care for X days). They can track “My requests” and subscriptions.
- **Nurse / Caregiver:** Logs in to a **staff dashboard**. Sees **bookings** assigned to them, can **accept or reject** new ones, **start** and **complete** services. They also see **earnings** (from services, rewards, referrals), set **payment details** (UPI, etc.), and have **referral links** to bring in more patients or subscribers.
- **Admin:** Logs in to **admin panel**. Manages **users** (add, edit, activate/deactivate, reset password), **service requests** (assign staff, approve payments), **subscriptions** (approve, verify payment, reject), **plans**, **payments to staff**, **referrals**, **rewards**, and **website content** (hero, plans, team, testimonials, achievements, site logo).

### Core Flows (Plain English)

| Flow | What happens |
|------|----------------|
| **Booking care** | Patient picks staff → selects service type & dates → submits booking → staff can accept/reject → admin can assign if needed → staff starts & completes service → admin can approve payment for staff. |
| **Subscription** | Patient picks a plan → pays (screenshot/transaction ID) → admin verifies → subscription becomes active → patient may get free or discounted services. |
| **Referral** | Staff or patient shares a link/code → new user registers with that code → referrer gets points/commission when the referred user subscribes or completes actions. |
| **Rewards** | Staff (nurse/caregiver) submits patient details (e.g. for a referral program) → gets reward points → admin can process payout. |

---

## 2. Technology Used (For Developers)

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.2+, **Laravel 12** |
| **Frontend** | **Blade** templates, **Vite 7**, **Tailwind CSS 4**, Alpine.js (on welcome page), Axios |
| **Database** | **MySQL** — the CRM is run on MySQL. Migrations live in `database/migrations`. (SQLite is not used; any SQLite-specific handling was intentionally skipped.) |
| **Auth** | Laravel session-based auth; role-based access via custom middleware `role:admin`, `role:patient`, `role:nurse`, `role:caregiver` |
| **Structure** | Modular: `app/Modules/{Auth, Services, Plans, Profiles, Payments, Referrals, Rewards}` + `app/Http/Controllers/Admin` for global admin (testimonials, featured team, page content, site settings) |
| **Assets** | Images/files in `storage/app/public`; optional route `/storage?path=...` to serve when symlink not used |
| **Queue** | Database queue driver (optional; used for background jobs if configured) |
| **Config** | `.env` for keys/DB; `config/subscription.php` for GST, commission, UPI (editable by admin) |

### Key Directories

- **`app/Modules/`** — Auth, Services (booking, staff, service types), Plans (plans + subscriptions), Profiles (profile + documents), Payments (staff + admin), Referrals, Rewards.
- **`app/Http/Controllers/Admin/`** — Site settings, testimonials, featured team, achievement media, page content, healthcare plans (landing).
- **`app/Models/`** — Core `User`, `PageContent`, `Testimonial`, `FeaturedTeam`, `AchievementMedia`, `Pincode`, etc.
- **`routes/web.php`** — Main web routes; Auth module also registers routes from `app/Modules/Auth/Routes/web.php`.
- **`resources/views/`** — Global views (e.g. welcome); module views live under `app/Modules/*/Views/`.

---

## 3. User Roles & Who Can Do What

| Role | Who | Main actions |
|------|-----|--------------|
| **Patient** | End customer needing home care | Register, login, view dashboard, browse **staff**, **book** nurse/caregiver, view **my requests**, manage **profile** & **documents**, view/buy **plans** & **subscriptions**, submit payment proof, use referral links. |
| **Nurse** | Licensed nurse | Login → **staff dashboard**, see **assigned bookings**, **accept/reject** bookings, **start/complete** services, view **earnings** (services + rewards + staff referrals + subscription referrals), set **payment settings** (UPI, QR), use **referral links**, submit **rewards** (patient details). |
| **Caregiver** | Caregiver (non-nurse) | Same as Nurse from system’s perspective (same staff dashboard, bookings, payments, referrals, rewards). |
| **Admin** | Back-office | **Users:** list, add, edit, view, toggle active, reset password, delete non-admins. **Service requests:** list, **assign** staff, approve payment. **Subscriptions:** list, view, **approve/reject**, **verify/reject payment**. **Plans:** CRUD. **Subscription settings:** GST, commission, UPI. **Payments:** list staff, **process payment** to staff (form + process). **Referrals:** list all, view by staff. **Rewards:** list. **Content:** page content, healthcare plans (landing), **achievement media**, **featured team**, **testimonials**, **site settings**. **Academics:** institutions, faculty, batches, subjects, topics, assignments, attendance, reports. **System:** pending payments page, **system reset** (danger zone). |
| **Academic Roles** | Institution-side users (faculty/students/academic admins) | Access Academics dashboard, curriculum (subjects/topics), assignments/submissions, attendance, and reports depending on role. |

---

## 4. Main Workflows (Step by Step)

### 4.1 Patient: Book Home Care (One-Way Booking)

1. Patient logs in → Dashboard (or goes to **Staff** listing).
2. **Staff listing** (`/staff`) — Sees nurses and caregivers (optional: filter by pincode, search, sort). Clicks **Book** on one staff.
3. **Book staff** (`/book/{staff}`) — Chooses **service type** (e.g. nursing, duration), dates, location, contact. If they have an **active subscription**, service can be free/discounted.
4. **Submit** → A **service request** is created with status e.g. `pending_approval` (for that staff) or `pending` (if no pre-selected staff).
5. **Staff** sees the booking in dashboard → can **Accept** or **Reject**. If accepted, status becomes `assigned`.
6. **Admin** can also **assign** staff from **Service requests** if the flow was “request first, assign later.”
7. On the day, **staff** **Starts** service (status → `in_progress`), then **Completes** (status → `completed`).
8. **Admin** can **Approve payment** for the service so staff payout is marked approved; admin can then **process payment** to staff from **Payments** section.

### 4.2 Patient: Subscribe to a Plan

1. Patient goes to **Plans** (`/plans`) → selects a **plan** → **Subscribe**.
2. Chooses payment frequency (e.g. monthly) → sees amount (base + GST). Can have a **referral code** (from staff or another user).
3. **Submit** → Subscription created (status `pending`). Patient uploads **payment screenshot** and/or **transaction ID**.
4. **Admin** → **Subscriptions** (or Pending payments) → opens subscription → **Verify payment** (or Reject). On verify, subscription becomes **active**, referrer (if any) gets commission tracked.
5. Patient sees **My subscriptions**; with active subscription they may get **free/discounted** service requests.

### 4.3 Staff: Accept Booking, Start, Complete, Get Paid

1. Staff logs in → **Staff dashboard** (`/staff/dashboard`). Sees list of **assigned** or **pending_approval** bookings.
2. For a new booking: **Accept** or **Reject**. Accept → status `assigned`.
3. On start date: **Start service** → status `in_progress`.
4. On end date: **Complete service** → status `completed`. Staff payout is calculated; admin can **approve** it.
5. **Payments** → Staff sets **UPI / payment details** in **Settings**. Admin goes to **Payments** → selects staff → **Process payment** (amount, transaction ID) → staff sees it in **Payment history**.

### 4.4 Staff: Referrals & Rewards

- **Staff referral:** Staff shares **referral link**. New user registers with that link → when they subscribe (or complete criteria), staff gets **referral commission** (tracked in Referrals). Admin can process referral payouts.
- **Subscription referral:** Same idea for subscription sign-ups; staff gets commission on plan sales.
- **Rewards:** Staff submits a form with **patient details** (name, phone, etc.) → gets **reward points**. Admin sees in **Rewards**; points can be converted to payout (logic in Rewards module).

### 4.5 Admin: Manage Website (Landing Page)

- **Page content** — Edit sections (e.g. hero title, subtitle) for the main landing.
- **Healthcare plans** — Plans shown on landing (name, price, features, etc.).
- **Achievement media** — Carousel/slider items (e.g. media coverage).
- **Featured team** — “Meet our experts” (name, image, role).
- **Testimonials** — “What our patients say.”
- **Site settings** — Logo, company name, tagline, founder image.

These are used on the **welcome** page (`/` or `/landing`).

---

## 5. Features & Functions (By Module)

### Auth Module

- **Login / Logout** — Email + password; session-based.
- **Registration** — Tabbed: **Patient**, **Nurse**, **Caregiver**. Optional **referral code** in URL (`?ref=...`). Optional **warrior** flow (nurse/caregiver only). After nurse/caregiver register → redirect to **Welcome Nursing Warrior** page.
- **Dashboard redirect** — After login: **Patient** → patient dashboard, **Staff** → staff dashboard, **Admin** → admin dashboard.
- **Admin: User management** — List users, add user, view/edit user (name, email, phone, role, pincode, address, status, password reset), toggle active, delete all non-admins.

### Services Module

- **Service types** — Admin-defined types (e.g. nursing 1 hr, 24 hr) with **patient charge**, **nurse payout**, **caregiver payout**. Shown to patient when booking.
- **Service request (booking)** — Created by patient: service type, preferred staff (or “any”), dates, duration, location, contact. Status flow: `pending` → `pending_approval` (when staff pre-selected) → `assigned` → `in_progress` → `completed` (optional: admin approval for payout).
- **Staff listing** — For patients: list nurses/caregivers, filter by pincode/search/experience/qualification, sort by distance/name/experience. **Book** → direct booking with that staff.
- **Direct booking** — Patient selects staff first, then service type and dates; request is tied to that staff (they accept/reject).
- **Staff dashboard** — Assigned & pending-approval bookings; start/complete; accept/reject; stats; earnings summary (services, rewards, staff referrals, subscription referrals); links to rewards, staff-referrals, subscription-referrals, payment settings & history.
- **Admin: Service requests** — List, filter by status, **assign** staff to a request, **approve payment** for completed services.

### Plans & Subscriptions Module

- **Plans** — Name, description, price, payment options (e.g. monthly/yearly), features, active/popular/sort. Shown on landing and in **Plans** page for logged-in users.
- **Subscribe** — User selects plan + payment frequency → subscription created (pending). User uploads payment screenshot and/or transaction ID.
- **My subscriptions** — List, view detail, payment confirmation, submit payment, cancel, renew.
- **Admin: Subscriptions** — List, view, **approve** subscription, **reject**, **verify payment**, **reject payment**. View payment screenshot.
- **Admin: Plans** — CRUD for plans.
- **Admin: Subscription settings** — GST rate, referral commission rate, UPI ID, merchant name (stored in `config/subscription.php`).

### Profiles Module

- **Profile** — View and edit profile (name, phone, address, DOB, bio, experience, specialization, availability). **Upload avatar** (image).
- **Documents** — Upload documents (type: medical report, certificate, ID proof, etc. depending on role). List, view, download, delete. Patient vs staff have different allowed types.

### Payments Module

- **Staff: Payment settings** — Set UPI ID, QR code for receiving payments from admin.
- **Staff: Payment history** — List of payments received from admin (transaction ID, amount, date).
- **Admin: Payments** — List staff, open **payment form** for a staff, **process payment** (amount, transaction ID) — records in `staff_payments` and updates service/referral payout flags as needed.

### Referrals Module

- **Referral link/code** — Generated for staff (and possibly patient). New user registers with `?ref=CODE`. When they subscribe (or complete action), **referral** record links referrer and referred user; commission is calculated.
- **Staff views** — Staff sees “Staff referrals” and “Subscription referrals” (people they referred, status, commission).
- **Admin: Referrals** — List all referrals, view by staff, see status and commission.

### Rewards Module

- **Staff: Rewards** — Submit a form with patient details (name, phone, age, address, hospital, treatment, etc.) → **CaregiverReward** created; staff gets **reward points**.
- **Admin: Rewards** — List reward submissions; can process payment for rewards (payment status, etc.).

### Academics Module

- **Academics dashboard** — Separate dashboard flow for academic roles (`hasAcademicRole()`).
- **Institutions** — Create/edit/list institution entities used for organizing academic data.
- **Faculty management** — Create and map faculty users for teaching and assignment workflows.
- **Batches** — Batch creation and mapping for student grouping.
- **Subjects & Topics** — Curriculum hierarchy (subject -> topic) with completion tracking support.
- **Assignments & Submissions** — Assignment CRUD, student submissions, and faculty review views.
- **Attendance** — Attendance marking and personal attendance views.
- **Reports** — Student and aggregate academic reports for progress/performance.

### Admin-Only (Non-Module) Features

- **Achievement media** — CRUD, reorder (move up/down). Shown on landing carousel.
- **Featured team** — CRUD, reorder. “Meet our experts.”
- **Testimonials** — CRUD, reorder. “What our patients say.”
- **Page content** — Edit sections (hero, etc.) and **healthcare plans** for landing.
- **Site settings** — Logo, company name, tagline, founder image.
- **Pending payments** — Single page listing pending subscription payments and pending service payments.
- **System reset** — Danger zone: delete all data except admin (users, service requests, subscriptions, referrals, rewards, etc.).

---

## 6. Important Concepts (Data & Business Logic)

- **Service request** — One booking: one patient, one service type, optional preferred staff, dates, amount. After assignment: one **assigned_staff** (nurse or caregiver). Status drives workflow (pending → assigned → in_progress → completed).
- **Subscription** — One user + one plan, payment frequency, start/end date, status (pending, active, expired, cancelled, rejected). Payment proof (screenshot/transaction_id) verified by admin. Active subscription can make service requests free or discounted.
- **Referral** — Links **referrer** (staff/patient) to **referred** user; tracks status and **referral_commission_amount** when referred user subscribes (or similar). Commission rate from subscription settings.
- **Reward (CaregiverReward)** — Staff submits patient details; gets **reward_points** (and later **reward_amount**). Admin can mark payment_processed.
- **Staff payout** — From **service requests** (total_staff_payout, admin_approved_at), **referrals**, and **rewards**. Admin **processes payment** via Payments → records **StaffPayment** (transaction_id, amount) and can mark related records as paid.
- **Pincode / location** — Users (and patients) can have pincode; **LocationService** uses pincode DB to get lat/long and compute **distance** for “nearby staff” (MySQL spatial used in production).
- **Unique ID** — Each user has a **unique_id** (e.g. MMHC-P-001, MMHC-N-001) for display and referral codes.

---

## 7. Quick Reference for Developers

| I want to… | Where to look |
|------------|----------------|
| Change login/register | `App\Modules\Auth\Controllers\AuthController`, `Auth\Views\login`, `Auth\Views\register-tabbed` |
| Change staff listing or booking | `Modules\Services\Controllers\StaffController`, `ServiceController` (bookStaff, storeDirectBooking), Views under `Services\Views\staff`, `Services\Views\services` |
| Change service request flow | `ServiceRequest` model (status, scopes), `StaffDashboardController` (accept, start, complete), `ServiceController` (assign) |
| Change plans or subscriptions | `Modules\Plans\Controllers\PlanController`, `SubscriptionController`, `SubscriptionService`, `Plan`, `Subscription` models |
| Change subscription payment verification | `SubscriptionController` (verifyPayment, rejectPayment), admin subscription views |
| Change profile or documents | `Modules\Profiles\Controllers\ProfileController`, `DocumentController`, `ProfileService`, `DocumentService` |
| Change staff payment flow | `Modules\Payments\Controllers\StaffPaymentController`, `AdminPaymentController`, `StaffPayment` model |
| Change referrals | `ReferralService`, `Referral` model, `AdminReferralController`, staff referral views |
| Change rewards | `RewardController`, `RewardService`, `CaregiverReward` model |
| Change academics (institutions/faculty/batches/subjects/topics/assignments/attendance/reports) | `app/Modules/Academics/Controllers/*`, `app/Modules/Academics/Models/*`, `app/Modules/Academics/Views/*`, `app/Modules/Academics/Routes/web.php` |
| Change landing page content | `resources/views/welcome.blade.php`, `PageContent`, `AchievementMedia`, `FeaturedTeam`, `Testimonial`, admin controllers in `Http/Controllers/Admin` |
| Change site logo/name | `SiteSettingsController`, `site_settings` table, views using site settings |
| Add a new role or permission | `App\Core\Middleware\CheckRole`, route middleware `role:...`, User model `isAdmin`, `isNurse`, etc. |

---

## 8. Academics Module (LMS)

This codebase also includes an Academics/LMS capability under `app/Modules/Academics`.

### Scope

- Institution and faculty setup
- Batch and student grouping
- Subject/topic curriculum structure
- Assignment publishing and submission lifecycle
- Attendance tracking
- Student and batch reporting

### Entry Points

- Controllers: `app/Modules/Academics/Controllers`
- Models: `app/Modules/Academics/Models`
- Views: `app/Modules/Academics/Views`
- Routes: `app/Modules/Academics/Routes/web.php`
- Provider: `app/Modules/Academics/Providers/AcademicsServiceProvider.php`

### Notes for New Developers

- Auth redirect already checks academic roles in dashboard routing.
- Treat Academics as a parallel domain to CRM workflows (home-care + subscriptions), not a replacement.
- Keep role and route checks strict when modifying mixed CRM + Academics navigation.

**End of document.** Use this as the single place to understand what the CRM does, for both technical and non-technical readers.

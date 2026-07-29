# MMHC CRM — Developer Meeting Brief

**Purpose:** Handout for client-side developer meetings. Covers how the app is built, platforms, testing, security, and common technical questions.

**Product:** MeD Miracle Health Care CRM  
**Live URL:** https://themmhc.com  
**Repository:** https://github.com/rohit03993/mmhc-crm  
**Last updated:** July 2026

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [What the System Does](#2-what-the-system-does)
3. [Platform & Architecture](#3-platform--architecture)
4. [Technology Stack](#4-technology-stack)
5. [How the Mobile App Works](#5-how-the-mobile-app-works)
6. [Project Structure](#6-project-structure)
7. [User Roles & Access Control](#7-user-roles--access-control)
8. [Major Features by Module](#8-major-features-by-module)
9. [Third-Party Integrations](#9-third-party-integrations)
10. [Database Overview](#10-database-overview)
11. [Security](#11-security)
12. [Testing & Quality Assurance](#12-testing--quality-assurance)
13. [Deployment & Infrastructure](#13-deployment--infrastructure)
14. [Recent Work (2025–2026)](#14-recent-work-20252026)
15. [Developer Q&A Cheat Sheet](#15-developer-qa-cheat-sheet)
16. [Known Gaps & Future Work](#16-known-gaps--future-work)

---

## 1. Executive Summary

MMHC CRM is a **single Laravel web application** that combines:

- **Home healthcare CRM** — patients book nurses/caregivers, subscriptions, referrals, staff payouts
- **Academics / LMS** — institutions, curriculum, exams, mentorship, open classrooms
- **Community** — internal social feed for staff and users
- **Admin CMS** — landing page, plans, testimonials, site settings

**There is no separate React/Angular SPA and no REST API for mobile.** The UI is **server-rendered Blade templates** with responsive CSS. The Android app is a **Capacitor WebView shell** that loads the live website (`https://themmhc.com`). UI updates deploy via `git pull` on the server — **no APK rebuild required** for most changes.

**Architecture type:** Modular monolith (one codebase, one database, one deployment).

---

## 2. What the System Does

### In one sentence

Patients book home care from nurses/caregivers; staff manage jobs and earnings; admins run the business; nursing colleges use the academics module for teaching, exams, and mentorship.

### Core business flows

| Flow | Summary |
|------|---------|
| **Booking** | Patient picks staff → books service → staff accept/reject → start/complete → admin approves payout |
| **Subscription** | Patient/student buys plan via Razorpay or manual UPI → admin verifies → active benefits |
| **Referrals** | Staff/patients share links → commission on successful signup/subscription |
| **Rewards** | Staff submit patient referrals → WhatsApp OTP → reward points → admin payout |
| **Academics** | Institution → batches → subjects → topics → assignments → exams → reports |
| **Open classrooms** | Independent faculty create public learning spaces; students/nurses join without college approval |
| **Mentorship** | Cross-institute mentor/mentee matching with submission reviews |

---

## 3. Platform & Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  Browser (desktop/mobile)  │  Android APK (Capacitor)      │
│  Responsive Blade UI       │  WebView → themmhc.com          │
└────────────────────────────┬────────────────────────────────┘
                             │ HTTPS
┌────────────────────────────▼────────────────────────────────┐
│  Nginx → PHP 8.2-FPM → Laravel 12 (modular monolith)        │
│  ├── ModuleServiceProvider (auto-loads app/Modules/*)         │
│  ├── routes/web.php + per-module Routes/web.php             │
│  └── Session-based auth (no token API)                      │
└────────────────────────────┬────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│  MySQL 8  │  Cache/Sessions/Queue (database or Redis)       │
│  Razorpay │  Pal Digital WhatsApp OTP                       │
└─────────────────────────────────────────────────────────────┘
```

### Key architectural decisions

| Decision | Choice | Why |
|----------|--------|-----|
| Frontend pattern | Server-rendered Blade | Faster delivery, SEO for landing page, one codebase |
| Mobile strategy | Responsive web + Capacitor WebView | No duplicate native UI; deploy once, all clients update |
| API layer | None (web routes only) | CRM is form-based; no mobile native API needed today |
| Module structure | `app/Modules/*` | Domain separation without microservices complexity |
| Auth | Laravel session cookies | Standard, secure for same-origin web app |
| Payments | Razorpay + manual UPI fallback | India market; admin verification for offline payments |

---

## 4. Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Language | PHP | 8.2+ |
| Framework | Laravel | 12.x |
| Database | MySQL | 8.0+ |
| Templates | Blade | Laravel built-in |
| CSS | Tailwind CSS | 4.x |
| Build tool | Vite | 7.x |
| HTTP client | Axios | Minimal (bootstrap.js) |
| Payments | Razorpay PHP SDK | 2.9.x |
| Cache (optional) | Redis via Predis | Production OTP scaling |
| Mobile wrapper | Capacitor | 7.x (Android) |
| Android SDK | compile/target 35, min 23 | `android/variables.gradle` |
| Testing | PHPUnit | 11.x |
| Code style | Laravel Pint | Dev dependency |

### What we do NOT use

- No React, Vue, or Angular SPA
- No Laravel Sanctum / Passport / REST API
- No Docker in production (VPS + Nginx)
- No GitHub Actions CI (tests run locally)
- No Laravel Dusk / Playwright (no browser automation)
- No traditional SMS gateway (WhatsApp OTP only)

---

## 5. How the Mobile App Works

### Android app (Capacitor)

| Item | Value |
|------|-------|
| App ID | `com.themmhc.crm` |
| App name | MeD Miracle |
| Config file | `capacitor.config.ts` |
| Mode | **Live-server WebView** |
| Loads | `https://themmhc.com` |

The APK does **not** bundle the Laravel app. It opens a full-screen browser pointed at production. When we deploy CSS, Blade, or backend changes to the server, users see updates on next app open — **no Play Store release needed** for most UI work.

### Mobile UI approach

- **Responsive CSS** in `public/css/` — `mobile-crm.css`, `healthcare-mobile.css`, `academics-mobile.css`, `crm-desktop.css`
- **App-like chrome** — bottom navigation, sidebar offcanvas, mobile headers (~95 module views)
- **Role-specific nav** — patient, staff, admin, and academic users see different bottom menus
- **Capacitor tweaks** — safe-area padding, WebView-specific JS in `public/js/capacitor-app.js`

### When APK rebuild IS required

- Android permissions, manifest, or icons change
- `capacitor.config.ts` changes (e.g. different URL)
- Native plugin additions
- Play Store release with new version code

---

## 6. Project Structure

```
mmhc-crm/
├── app/
│   ├── Modules/           # Domain modules (Auth, Services, Plans, Academics, etc.)
│   ├── Core/              # Shared middleware (CheckRole)
│   ├── Http/Controllers/  # Admin CMS, backups, site settings
│   ├── Models/            # Core models (User, PageContent, etc.)
│   └── Services/          # Cross-cutting (backups, storage auth, account deletion)
├── bootstrap/app.php      # Middleware, CSRF exceptions, route loading
├── config/                # subscription, payments, academics, backup
├── database/
│   ├── migrations/        # ~114 migrations
│   └── seeders/           # Demo data for testing
├── public/                # CSS, JS, images, Google verification files
├── resources/             # Vite entry (app.css, app.js), global views
├── routes/web.php         # Landing, webhooks, some manual module routes
├── android/               # Capacitor Android project
├── tests/                 # PHPUnit feature + unit tests
└── docs/                  # Internal documentation
```

### Active modules (`app/Modules/`)

| Module | Responsibility |
|--------|----------------|
| **Auth** | Login, OTP, registration, dashboards, admin financial views |
| **Profiles** | User profiles, documents, staff ID cards |
| **Plans** | Healthcare plans, subscriptions, Razorpay, coupons, student membership |
| **Services** | Service requests, staff booking, staff dashboard |
| **Payments** | Staff payout settings and admin payment processing |
| **Referrals** | Patient and staff referral flows |
| **Rewards** | Caregiver reward submissions |
| **Incentives** | Incentive rules, ledger, growth slabs |
| **Community** | Posts, comments, reactions, events |
| **Academics** | Institutions, curriculum, exams, mentorship, open classrooms |

Modules are auto-discovered by `ModuleServiceProvider` — each can have its own controllers, models, views, migrations, and routes.

---

## 7. User Roles & Access Control

### Roles (stored on `users.role`)

| Role | Primary use |
|------|-------------|
| `admin` | Full platform control |
| `patient` | Book care, subscriptions, referrals |
| `nurse` | Staff dashboard, bookings, earnings |
| `caregiver` | Same as nurse |
| `institution_admin` | College admin — batches, enrollments, reports |
| `faculty` | Teaching, exams, mentorship, open classrooms |
| `student` | Learning, assignments, exams (after enrollment + membership) |

### Authorization model

- **Role-based only** — custom `role` middleware (`CheckRole.php`)
- **No permission tables** — no Spatie permissions, no Laravel Policies
- **Layered gates** on web middleware:
  1. `auth` — must be logged in
  2. `role:admin|nurse|...` — role check
  3. `phone.verified` — WhatsApp OTP verified
  4. `student.enrollment.approved` — college approved student
  5. `student.membership` — student paid ₹1,200 journey plan

### Post-login redirects

| Role | Destination |
|------|-------------|
| admin | `/admin/dashboard` |
| patient | `/dashboard` |
| nurse / caregiver | `/staff/dashboard` |
| institution_admin / faculty / student | `/academics` |

---

## 8. Major Features by Module

### Healthcare (Services + Payments + Profiles)

- One-way direct booking (patient picks staff)
- Service types with patient charge and staff payout rates
- Nearby staff by pincode (MySQL spatial distance)
- Service lifecycle: pending → assigned → in_progress → completed
- **Completion OTP** — WhatsApp OTP to patient before staff marks complete
- Staff UPI/QR payment settings
- Staff ID cards with public QR verification (`/verify/staff/{id}`)
- Document uploads per role

### Plans & Subscriptions

- Healthcare subscription plans (monthly/yearly)
- Razorpay online checkout + webhook
- Manual UPI with screenshot upload (fallback)
- Subscription coupons (student / patient / all audiences)
- Student Journey Membership (₹1,200 gate for students)
- GST, referral commission, invoices

### Referrals & Rewards

- Staff referral chain with WhatsApp OTP on referred mobile
- Subscription referral links and commission tracking
- Patient reward submissions by staff → OTP → points → payout

### Incentives

- Rule sets, growth DTA slabs, service/subscription rates
- Incentive ledger and staff drill-down dashboard

### Academics (LMS)

- Institutions → batches → subjects → topics → assignments
- Enrollment workflow (pending → institution admin approves)
- Attendance, MCQ exams with timed attempts and auto-scoring
- Topic resources (video, files)
- Academic reports (SPI, FPI, ICR style)
- **Mentorship** — cross-institute mentor/mentee
- **Open classrooms** — faculty-owned public learning spaces (June 2026)

### Community

- Feed with posts, comments, reactions
- Staff can create text/image posts; admin creates events
- Notifications for interactions

### Admin / CMS

- Landing page content (hero, plans, team, testimonials, achievements)
- Site settings (logo, tagline)
- User management (CRUD, activate/deactivate, password reset)
- Financial dashboards and pending payments
- Full site backup (database + uploads ZIP)
- System reset (blocked in production)

---

## 9. Third-Party Integrations

| Service | Purpose | Config |
|---------|---------|--------|
| **Pal Digital WhatsApp** | All OTP (login, profile, rewards, referrals, service completion) | `PAL_DIGITAL_*` in `.env` |
| **Razorpay** | Patient/student subscription payments | `RAZORPAY_*` in `.env` |
| **RazorpayX** | Optional automatic staff UPI payouts (disabled by default) | `RAZORPAYX_*`, opt-in per staff |
| **MySQL spatial** | Pincode-based staff distance | `pincodes` table |
| **Google Search Console** | Site verification | `public/googlec665424ea2997da3.html` |

### OTP delivery

All OTPs go through **WhatsApp** (Pal Digital campaign API), not SMS. Services involved:

- `SmsOtpService` — login OTP
- `PhoneBindOtpService` — profile phone binding
- `ScopedSmsOtpRedisService` — referrals, rewards, service completion

OTP storage: HMAC digest in cache (or Redis in production), 6-digit codes, `hash_equals` verification, rate-limited resend.

---

## 10. Database Overview

- **Engine:** MySQL 8.0+
- **Migrations:** ~114 files (central + per-module)
- **Approach:** Additive migrations; no destructive resets in production

### Major table groups

| Group | Key tables |
|-------|------------|
| Users & auth | `users`, `profiles`, `documents`, `sessions` |
| Healthcare | `service_types`, `service_requests`, `daily_services`, `pincodes` |
| Commerce | `plans`, `subscriptions`, `payments`, `subscription_coupons` |
| Staff payouts | `staff_payments` |
| Referrals/rewards | `referrals`, `caregiver_rewards` |
| Incentives | `incentive_rule_sets`, `incentive_ledger`, rate tables |
| Academics | `academic_institutions`, `academic_batches`, `academic_subjects`, `academic_topics`, `academic_assignments`, `academic_submissions`, `academic_exams`, `academic_mentorships` |
| Open classrooms | `academic_open_classrooms`, members, resources, assignments, submissions |
| Community | `community_posts`, `community_comments`, `community_reactions` |
| CMS | `page_contents`, `site_settings`, `testimonials`, `featured_team`, `healthcare_plans` |

### Demo data

Seeders available for local/staging demos: `HealthcareCrmDemoSeeder`, `AcademicDemoSeeder`, `PincodeSeeder`, etc. Production uses real data — seeders are not run on live deploy.

---

## 11. Security

### Authentication

| Method | Details |
|--------|---------|
| **WhatsApp OTP** | Primary for new/phone-only users; Pal Digital API |
| **Email + password** | Legacy; bcrypt hashing; blocked for `login_via_phone_only` accounts |
| **Sessions** | Database driver, 120 min lifetime, HTTP-only cookies |
| **Password reset** | Laravel broker; disabled for phone-only accounts |

### Password & encryption

- Passwords hashed with **bcrypt** (12 rounds)
- Plaintext password column **removed** (migration 2026)
- `APP_KEY` for Laravel encryption; optional `OTP_PEPPER` for OTP HMAC
- Secrets in `.env` only — never committed to git

### Authorization

- Role middleware on routes
- Controller-level checks for ownership (e.g. subscription belongs to user)
- `DeletionPolicy` — cannot delete last admin or self-delete admin
- System reset blocked when `APP_ENV=production`

### CSRF & XSS

- Laravel CSRF on all web forms (`@csrf`)
- **Exception:** Razorpay webhook (uses HMAC signature instead)
- Blade `{{ }}` auto-escaping; user HTML not rendered raw in feeds

### Rate limiting

| Area | Limit |
|------|-------|
| Login / forgot password | 5/min |
| OTP verify | 10/min |
| Register | 10/min |
| Community comments | 20/min |
| OTP resend (app-level) | 1 min per phone |

### File upload security

- MIME and size validation on most uploads (PDF, images)
- UUID filenames for profile documents
- **Storage gateway** (`/media-file`) — path traversal protection, per-user ACLs
- Admin bypass for backup/media management
- Payment screenshots denied for non-admin paths

### Payment security

- Razorpay order signature verification on client callback
- Webhook verified with `X-Razorpay-Signature` HMAC
- Subscription ownership checked before payment confirm

### Production hardening (`.env`)

```
APP_DEBUG=false
APP_ENV=production
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
APP_URL=https://themmhc.com
```

HTTPS expected via Nginx + Let's Encrypt on VPS.

### Security strengths (talking points)

1. Phone-first auth with hashed OTP storage, not plaintext
2. Session regeneration on login; full invalidation on logout
3. Layered access gates (auth → role → phone → enrollment → membership)
4. Throttling on sensitive endpoints
5. Razorpay signature verification
6. Secure file serving with path sanitization
7. No secrets in repository

### Honest security gaps (if asked)

1. No application-level security headers (CSP, HSTS) — relies on web server
2. No token-based API — if native offline API needed later, must be built
3. Role-only auth — no fine-grained permissions package
4. Email login does not check `is_active` flag (phone OTP does)
5. Academic file uploads lack strict MIME whitelist
6. No automated security scanning in CI

---

## 12. Testing & Quality Assurance

### Automated tests (PHPUnit 11)

| Test file | What it covers |
|-----------|----------------|
| `tests/Feature/SmokeTest.php` | Login and register pages return 200 |
| `tests/Feature/ExampleTest.php` | Login route smoke |
| `tests/Feature/DashboardRedirectTest.php` | Admin/nurse/patient/faculty dashboard routing |
| `tests/Feature/ExamAccessServiceTest.php` | Academics exam access rules (cohort, publish, timing) |
| `tests/Unit/ExampleTest.php` | Placeholder |

**Total:** 12 automated test methods. Run with:

```bash
php artisan test
# or
composer test
```

**Requirements:** MySQL running, `mmhc_crm_test` database (auto-created by `tests/bootstrap.php`).

### What automated tests do NOT cover

- Payment flows (Razorpay)
- OTP delivery (WhatsApp)
- Full booking lifecycle end-to-end
- Mobile/Capacitor behavior
- Admin CMS workflows
- Browser/UI regression

### Manual testing

Extensive checklists exist in the repo:

| Document | Purpose |
|----------|---------|
| `docs/MASTER-TEST-CHECKLIST.md` | End-to-end manual checklist by role |
| `docs/TESTING.md` | Automated vs manual testing guide |
| `docs/FUNCTIONALITY-ANALYSIS-BY-ROLE.md` | Role-based flow verification |
| `MOBILE_COVERAGE_AUDIT.md` | Mobile layout coverage (~95 views) |
| `docs/academics-audit-and-server-checklist.md` | Academics deploy checklist |
| `LOGIN_CREDENTIALS.md` | Demo accounts for manual testing |

### Demo test credentials (staging/local)

| Role | Email | Password |
|------|-------|----------|
| Admin | `mantu@themmhc.com` | `password123` |
| Nurse | `nurse@demo.com` | `password123` |
| Patient | `patient@demo.com` | `password123` |
| College admin | `college.admin@medmiracle.com` | `password123` |

### CI/CD status

- **No GitHub Actions** or automated pipeline
- Deploy is manual: `git pull` on VPS + artisan cache commands
- Tests run locally before release

### Testing talking points for the meeting

1. Core routing and academics exam logic have automated coverage
2. Full CRM tested manually with role-based checklists before each release
3. Production deploy is additive migrations only — existing data preserved
4. CI automation is a reasonable future improvement

---

## 13. Deployment & Infrastructure

### Production environment

| Item | Detail |
|------|--------|
| Hosting | VPS (Ubuntu/Debian) |
| Web server | Nginx → PHP 8.2-FPM |
| Document root | `public/` |
| Domain | `themmhc.com` |
| SSL | Let's Encrypt (Certbot) |
| Process manager | Cron for `php artisan schedule:run` |

### Standard deploy commands

```bash
cd /home/themmhc/htdocs/themmhc.com
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force          # only when new migrations exist
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache
php artisan config:clear && php artisan config:cache
```

### Environment variables (critical)

- `APP_KEY`, `APP_URL`, `APP_DEBUG=false`
- `DB_*` — MySQL connection
- `PAL_DIGITAL_*` — WhatsApp OTP
- `RAZORPAY_*` — payments
- `OTP_PEPPER` — optional OTP hardening

### Backup

- Admin can trigger full site backup (DB + uploads) from admin panel
- Rate-limited to 6/min

### Google Search Console

Verification file: `public/googlec665424ea2997da3.html`  
URL: https://themmhc.com/googlec665424ea2997da3.html

---

## 14. Recent Work (2025–2026)

| Feature | Status |
|---------|--------|
| WhatsApp-only OTP (Pal Digital) | Live |
| Student enrollment approval workflow | Live |
| Student Journey Membership (₹1,200) | Live |
| MCQ exams with timed attempts | Live |
| Cross-institute mentorship | Live |
| Subscription coupons | Live |
| Community module | Live |
| CRM-wide desktop UI polish | Live |
| Open classrooms (independent teachers) | Live (June 2026) |
| Nurse/caregiver access to open classrooms | Live |
| Plain password column removed | Live |
| Google Search Console verification | Added |

---

## 15. Developer Q&A Cheat Sheet

### “What platform is this built on?”

**Laravel 12 (PHP 8.2) + MySQL 8 + Blade + Tailwind.** Android uses Capacitor 7 WebView. Not React Native, not Flutter, not a separate mobile backend.

### “Is there an API?”

**No REST API today.** All features are server-rendered web routes with session auth. Razorpay webhook is the only public POST endpoint (signature-verified).

### “How does mobile work?”

**Responsive web inside a Capacitor Android shell.** The APK loads `https://themmhc.com`. Same codebase serves browser and app.

### “How do you deploy updates?”

**Git pull on VPS + Laravel cache clear.** UI/CSS changes appear immediately. APK rebuild only for native/Android config changes.

### “How is auth handled?”

**Laravel sessions + WhatsApp OTP (Pal Digital).** Phone verification required before earnings/payouts. Students also need enrollment approval and membership payment.

### “How are payments secured?”

**Razorpay** with order signature verification and webhook HMAC. Manual UPI requires admin verification. Staff payouts are manual UPI or opt-in RazorpayX.

### “What testing do you have?”

**PHPUnit** for smoke tests, dashboard routing, and academics exam access. **Manual checklists** for full CRM flows by role. No browser automation or CI yet.

### “How is data protected?”

**bcrypt passwords, CSRF on forms, rate limiting, secure file gateway, HTTPS in production, secrets in .env.** Role-based route protection. OTP stored as HMAC digests, not plaintext.

### “Can we scale?”

**Vertical scaling on VPS first.** Redis optional for cache/OTP. Database queue for background jobs. Modular monolith can be split later if needed, but not required now.

### “What about iOS?”

**Capacitor supports iOS**, but only Android project exists in repo today. Same WebView approach would apply.

### “Where is the code organized?”

**`app/Modules/{Name}/`** — each domain has Controllers, Models, Views, Routes, Migrations. Shared code in `app/Services/`, `app/Core/`.

### “How do migrations work?”

**Laravel migrations** — additive, run with `php artisan migrate --force` on deploy. New tables/columns only; existing production data preserved.

### “What happens if Razorpay is down?”

**Manual UPI fallback** — user uploads screenshot + transaction ID; admin verifies manually.

### “Who can delete users?”

**Admin only**, with `DeletionPolicy` preventing last admin deletion and self-deletion.

---

## 16. Known Gaps & Future Work

| Area | Current state | Possible next step |
|------|---------------|-------------------|
| CI/CD | Manual deploy | GitHub Actions running PHPUnit on push |
| API layer | None | Laravel Sanctum if native offline features needed |
| Browser tests | None | Laravel Dusk or Playwright for critical flows |
| iOS app | Not built | `npx cap add ios` + App Store process |
| Permissions | Role-only | Spatie permissions if granular access needed |
| Security headers | Server-level | CSP/HSTS middleware or Nginx config |
| Email login + inactive users | Gap | Add `is_active` check to email login |
| Test coverage | ~12 tests | Expand payment, booking, OTP flows |

---

## Related Documentation

| File | Content |
|------|---------|
| `docs/CRM-OVERVIEW-AND-WORKFLOW.md` | Detailed workflows |
| `docs/FUNCTIONALITY-ANALYSIS-BY-ROLE.md` | Role-by-role features |
| `docs/MASTER-TEST-CHECKLIST.md` | Manual test checklist |
| `DEPLOYMENT.md` | VPS setup guide |
| `MOBILE_COVERAGE_AUDIT.md` | Mobile UI coverage |
| `LOGIN_CREDENTIALS.md` | Demo accounts |
| `capacitor.config.ts` | Mobile app config |

---

*This document is intended for internal and client developer meetings. Update after major releases.*

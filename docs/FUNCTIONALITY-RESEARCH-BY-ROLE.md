# MMHC CRM — Functionality Research by Role

**Purpose:** Identify what is incomplete, what can crash, and what needs to be fixed immediately — from the point of view of **Normal User (Guest)**, **Patient**, **Nurse**, **Caregiver**, and **Admin**.

**How to read this document:**
- **CRASH** = Unhandled exception or error that can break the page or show a white/500 screen.
- **INCOMPLETE** = Feature is missing, stubbed, or does not behave as expected.
- **FIX IMMEDIATELY** = High impact (crash or wrong access) and should be fixed first.
- **FIX SOON** = Important for correctness or UX but not an instant crash.

---

## 1. Normal User (Guest — Not Logged In)

### What They Can Do
- Visit **home page** (`/`, `/landing`) — see welcome content, healthcare plans, achievements, featured team, testimonials.
- Click **Login** → `/login` or `/auth/login`.
- Click **Register** → `/register` or `/auth/register`.

### What Can Crash
| Where | When | Why |
|-------|------|-----|
| **Home / Landing** | If `PageContent::getAllSections()` or any model throws (e.g. DB error, missing table) | No try/catch; exception propagates. |
| **Home** | If a section key is missing in `$pageContent` and a view uses it without `isset()` | Possible in custom sections; main hero is guarded with `isset($pageContent['hero'])`. |

### What Is Incomplete
- **No rate limiting on login** — Brute-force possible (security gap, not a “crash”).
- **Referral from guest:** If a guest opens a referral link (e.g. `.../register?ref=CODE`), registration works and referral is attached. No issue. If they open `.../plans?ref=USER_ID` and then subscribe, referrer is set. **Complete.**

### What Needs Fix
- **FIX SOON:** Add throttle to login route to prevent brute force.

---

## 2. Patient

### What They Can Do
- Log in → redirected to **patient dashboard**.
- **Dashboard:** See nearby staff (if pincode set), subscription status, quick links to Staff, My Requests, Plans, Subscriptions.
- **Staff listing** (`/staff`) — Browse nurses/caregivers, filter by pincode/search/experience/qualification, sort; click **Book** on a staff.
- **Book staff** (`/book/{staff}`) — Select service type, dates, location, contact; submit. With active subscription, amount can be zero.
- **My Requests** (`/services/my-requests`) — List and view own service requests.
- **Profile** — View/edit profile, upload avatar.
- **Documents** — Upload/list/view/download/delete own documents (patient types: medical report, Aadhaar, prescription, etc.).
- **Plans** — View plans; **Subscribe** with payment frequency, upload screenshot/transaction ID.
- **Subscriptions** — List, view, payment confirmation, submit payment, cancel, renew.

### What Can Crash
| Where | When | Why |
|-------|------|-----|
| **Staff listing** (`/staff`) | When there are **no active service types** in DB | View uses `$serviceTypes->first()->nurse_payout` and `$serviceTypes->first()->caregiver_payout` without null check. `first()` is null → "Trying to get property on null". |
| **Profile index** (`/profile`) | When **profile load fails** (DB error, missing profile table, etc.) | Controller catch sets `$profile = null` and still returns same view. View uses `$profile->avatar_path`, `$profile->getCompletionPercentage()` → crash. |
| **Profile edit** (`/profile/edit`) | Same as above | Same pattern; view uses `$profile->...` in many places. |
| **Subscriptions index** (`/subscriptions`) | Any uncaught exception in controller | Catch block returns 500 with **full exception message and file path** in response body (info leak + ugly error). |

### What Is Incomplete
- **Services “request” flow:** `/services/request` (create) **redirects** to `/staff` or to `/book/{staff}` if staff_id is in query. So “Request service” from services index effectively becomes “choose staff first”. No broken link, but the **legacy** “request without pre-selecting staff” path is effectively deprecated; only direct booking is the real flow. **Document or remove old copy** to avoid confusion.
- **Patient cannot see “subscription referral” link** — Only staff see subscription referral link. Patients can still use ref in URL when registering or subscribing. **No bug.**

### What Needs Fix
| Priority | Item | Action |
|----------|------|--------|
| **FIX IMMEDIATELY** | Staff listing when no service types | Guard `$serviceTypes->first()`: use `$serviceTypes->first()?->nurse_payout ?? 2000` (PHP 8+) or `@if($serviceTypes->isNotEmpty())` with default. |
| **FIX IMMEDIATELY** | Profile index/edit when profile fails | Do not pass `$profile = null` to same view. Redirect to dashboard with error message, or use a dedicated “profile unavailable” view. |
| **FIX IMMEDIATELY** | Subscriptions index 500 response | Log exception; return generic error message. Do not send `getMessage()`, `getFile()`, `getLine()` in response. |

---

## 3. Nurse

### What They Can Do
- Log in → redirected to **staff dashboard** (`/staff/dashboard`).
- **Staff dashboard:** Assigned/pending-approval bookings, start/complete service, accept/reject booking; earnings (service, rewards, staff referrals, subscription referrals); referral links; quick links to rewards, staff-referrals, subscription-referrals, payment settings/history.
- **Service details** (`/staff/service/{id}`) — View assigned service; start/complete (with validation).
- **Rewards** (`/rewards`) — Submit patient details for reward points.
- **Staff referrals** (`/staff/staff-referrals`) — List staff referral history (people they referred to join as staff).
- **Subscription referrals** (`/staff/subscription-referrals`) — List subscription referrals (patients who subscribed via their link).
- **Payment settings** (`/staff/payments/settings`) — Set UPI, QR code.
- **Payment history** (`/staff/payments/history`) — View payments received from admin.
- **Profile & Documents** — Same as any user; document types for staff (certificate, ID proof, medical license, insurance).

### What Can Crash
| Where | When | Why |
|-------|------|-----|
| **Staff dashboard** | When **subscription referral stats query returns no rows** | Code uses `$subscriptionReferralStats->total_referrals ?? 0` etc. after `->first()`. If no subscriptions at all, `first()` is **null**, then `null->total_referrals` throws. |
| **Staff dashboard** | Same as Patient: **no service types** | Not used on staff dashboard for pricing in the same way; staff dashboard gets service types from assigned services. So **no crash** here for nurse. |
| **Service details** | If **service type or assigned staff is missing** (data inconsistency) | Controller has null checks and aborts 404; safe. |

### What Is Incomplete
- **ReferralController (Referrals module):** Has `getReferralLink`, `getReferralStats`, `getReferralHistory` but **no Routes file** in Referrals module — so these **API-style endpoints are never registered**. The staff dashboard does **not** use them; it gets referral link and history from `ReferralService` in `StaffDashboardController`. So **no functional gap** for nurse/caregiver; only dead code in ReferralController. Optional cleanup: remove or register routes if you want a separate API later.
- **Subscription referral link:** Built as `route('plans.index', ['ref' => $user->id])`. Subscription controller accepts `ref` as referrer user id and validates referrer is staff. **Works.**

### What Needs Fix
| Priority | Item | Action |
|----------|------|--------|
| **FIX IMMEDIATELY** | Staff dashboard when staff has zero subscription referrals | `$subscriptionReferralStats = Subscription::...->first();` can be null. Use `optional($subscriptionReferralStats)->total_referrals ?? 0` (and same for other fields) so null is safe. |

---

## 4. Caregiver

**Same as Nurse** from the app’s point of view (same routes, same staff dashboard, same rewards/referrals/payments). All findings for **Nurse** apply to **Caregiver**.

- Same **crash** risk on staff dashboard if `$subscriptionReferralStats` is null (no subscription referrals).
- Same **incomplete** items (ReferralController routes unused; subscription referral link works).

---

## 5. Admin

### What They Can Do
- Log in → **admin dashboard** (`/admin/dashboard`).
- **Users** — List, add, view, edit, toggle status, reset password, delete non-admins.
- **Pending payments** — List pending subscription and service payments.
- **Service requests** — List, filter, assign staff, approve payment.
- **Subscriptions** — List, view, approve/reject, verify/reject payment, view screenshot.
- **Plans** — CRUD.
- **Subscription settings** — GST, commission, UPI, merchant name (writes to `config/subscription.php`).
- **Payments (staff)** — List staff, open payment form, process payment (transaction ID, amount).
- **Referrals** — List all, view by staff.
- **Rewards** — List reward submissions (admin index).
- **Profiles** — List users, view user profile.
- **Achievement media, Featured team, Testimonials** — CRUD, reorder.
- **Page content** — Edit sections; **Healthcare plans** (landing) — CRUD.
- **Site settings** — Logo, company name, tagline, founder image.
- **System reset** — Danger zone: delete all data except admin.

### What Can Crash
| Where | When | Why |
|-------|------|-----|
| **Admin dashboard** | When there are **no service requests** matching the “pending service payments” aggregate | `getFinancialStats()` uses `->first()->pending ?? 0`. If query returns no rows, `first()` is null → `null->pending` throws. |
| **Subscription settings update** | When **config file** `config/subscription.php` is missing | `File::get(config_path('subscription.php'))` throws. |
| **Profile view (admin)** | When **getProfile($user)** throws | `ProfileController::adminView()` calls `getProfile($user)` and passes `$profile` to view. If it throws, 500. View itself is null-safe (`$profile && ...`). |
| **System reset** | When DB is **SQLite** | `SystemResetService` uses `SET FOREIGN_KEY_CHECKS=0/1` (MySQL only). SQLite will throw. |
| **Delete all data (artisan)** | When DB is **SQLite** | Same MySQL-only statements. |

### What Is Incomplete
- **Admin dashboard “Placeholder for more activities”** — UI placeholder; no bug.
- **Profiles admin index** — Shows “Incomplete” badge for incomplete profiles; works.
- **Plans module admin routes vs web.php:** Plans module registers `admin.payments`, `admin.plans`, `admin.subscriptions` (Plans PaymentController). Main `web.php` registers **admin subscriptions and plans** again and uses **AdminPaymentController** for **staff** payments (different feature). So:
  - **Admin “Payments”** in sidebar (from web.php) = staff payments (process payment to nurse/caregiver). **Works.**
  - Plans module `PaymentController` (subscription payment gateway, success/failure, invoice) may be **duplicate or legacy** — confirm if any link in admin points to `admin.payments` from Plans; if not, admin only uses staff payments. **Clarify or remove duplicate routes.**
- **Admin Rewards:** Route `admin.rewards.index` exists (Rewards module). Admin dashboard has quick link to `admin.referrals.index`; no direct “Rewards” quick link on dashboard — admin must go via menu/sidebar if available. **Check sidebar/layout for “Rewards” link** for admin.

### What Needs Fix
| Priority | Item | Action |
|----------|------|--------|
| **FIX IMMEDIATELY** | Admin dashboard financial stats | Use `optional($query->first())->pending ?? 0` (or equivalent) so zero rows does not throw. |
| **FIX IMMEDIATELY** | Subscription settings when config missing | Check `File::exists(config_path('subscription.php'))` before `File::get`; return friendly error or create default. |
| **FIX SOON** | System reset / DeleteAllData on SQLite | Run MySQL-only statements only when `DB::getDriverName() === 'mysql'`; document or implement SQLite-safe path. |
| **FIX SOON** | Admin profile view when getProfile fails | Wrap in try/catch; on failure redirect or show error instead of 500. |

---

## 6. Cross-Role & Global Issues

### Crashes
- **SubscriptionController::viewPaymentScreenshot** — Catch block returns `'Error loading screenshot: ' . $e->getMessage()`. **FIX SOON:** Log and return generic message.
- **Storage disk:** ProfileService and DocumentService use `Storage::exists()` / `Storage::delete()` without `->disk('public')`. If default disk is not `public`, behavior may be wrong. **FIX SOON:** Use `Storage::disk('public')` for these paths.

### Incomplete / Confusing
- **Duplicate route registration:** Subscriptions and plans are registered both in `web.php` and in Plans module `Routes/web.php`. Comment in web.php says “manually register to ensure they work”. Risk of double registration or order-dependent behavior. **Recommendation:** Single source of truth; fix module loading order if needed.
- **Login throttle:** Not applied to login POST. **FIX SOON.**

---

## 7. Summary: Fix Immediately vs Fix Soon

### Fix Immediately (Crash or High Impact)
1. **DashboardController** — `getFinancialStats()`: `->first()->pending` when no rows (admin dashboard crash).
2. **StaffDashboardController** — Subscription referral stats: `$subscriptionReferralStats->...` when `first()` is null (nurse/caregiver dashboard crash).
3. **Staff index view** — `$serviceTypes->first()->nurse_payout` / `caregiver_payout` when no service types (patient staff listing crash).
4. **ProfileController** — On profile load failure, do not pass `$profile = null` to profile index/edit view (patient profile crash).
5. **SubscriptionController::index()** — Do not return exception message and path in 500 response (info leak + bad UX).
6. **SubscriptionSettingsController** — Check config file exists before `File::get` (admin settings crash).

### Fix Soon (Correctness, Security, or Stability)
7. **SubscriptionController::viewPaymentScreenshot** — Generic error message in catch.
8. **ProfileService / DocumentService** — Use `Storage::disk('public')` for avatar and document paths.
9. **SystemResetService & DeleteAllData** — SQLite-safe (guard MySQL statements).
10. **Login route** — Add throttle (e.g. `throttle:5,1`).
11. **Admin profile view** — Try/catch around getProfile; redirect or error view on failure.

---

## 8. Role-by-Role Checklist (Quick Reference)

| Role | Crashes | Incomplete | Fix Immediately |
|------|---------|------------|------------------|
| **Guest** | Home if DB/view error | Login throttle missing | Add login throttle |
| **Patient** | Staff listing (no service types); Profile (null); Subscriptions index (500 body) | Legacy “request” flow is redirect-only | Staff view null-safe; Profile error handling; Subscriptions catch message |
| **Nurse** | Staff dashboard (no subscription referrals) | ReferralController routes unused | Subscription referral stats null-safe |
| **Caregiver** | Same as nurse | Same | Same |
| **Admin** | Dashboard (no pending service payments); Subscription settings (missing config); System reset on SQLite | Duplicate plans/subscription routes; Rewards link in nav | Financial stats null-safe; Config file check; SQLite guard for reset |

This document should be updated whenever new features are added or flows change. Re-verify after applying fixes.

# MMHC CRM — Full Functionality Analysis by Role

**Purpose:** End-to-end check of all features from **Nurse**, **Caregiver**, **Patient**, and **Admin** perspectives so you can fix anything that is not working properly. Includes admin–staff payment flow.  
**Principle:** No hiccups in live use; fix issues permanently with zero data loss.

**How to read:**
- **OK** = Implemented and behaving correctly; no known bug.
- **CHECK** = Should be manually tested; possible edge case or UX improvement.
- **FIX** = Known bug or gap; should be fixed.
- **N/A** = Not applicable for that role.

---

## Already Fixed (Previous Sessions)

The following were identified earlier and have been fixed in code. They are listed so the doc stays accurate; no action needed unless you regress.

- Admin dashboard: `getFinancialStats()` null when no service requests → **fixed** (optional).
- Staff dashboard: subscription referral stats when no rows → **fixed** (optional).
- Patient staff listing: no service types → **fixed** (null-safe first()).
- Profile index/edit: profile load failure → **fixed** (redirect + message).
- Subscriptions index: 500 with exception text → **fixed** (generic message).
- Subscription settings: missing config file → **fixed** (exists check).
- Plan null in subscription/plan views → **fixed** (plan?->name etc.).
- Payment screenshot error message → **fixed** (generic).
- Subscription create/submit/verify/reject: user-facing exception text → **fixed** (generic + log).
- Direct booking catch: user message → **fixed** (generic).
- ProfileService/DocumentService storage → **fixed** (disk('public')).
- Login + registration throttle → **fixed**.
- Admin profile view on getProfile failure → **fixed** (try/catch, redirect).
- System reset / DeleteAllData on non-MySQL → **fixed** (driver check).
- System reset in production → **fixed** (blocked).
- SystemController + SubscriptionSettingsController error messages → **fixed** (generic).

---

## 1. Nurse & Caregiver — Full Flow

Nurse and caregiver use the **same** routes and dashboard; only role labels differ.

### 1.1 Login & Redirect

| Step | Route / Action | Status | Notes |
|------|----------------|--------|--------|
| GET `/login` or `/auth/login` | Show login form | OK | |
| POST login | `auth.login.post` | OK | Throttle 5/min. Redirects to `route('dashboard')`. |
| DashboardController `index()` | Redirect staff to `staff.dashboard` | OK | `$user->isStaff()` → `redirect()->route('staff.dashboard')`. |

**Verdict:** Login and post-login redirect for staff are correct.

---

### 1.2 Staff Dashboard (`/staff/dashboard`)

| Element | Status | Notes |
|---------|--------|--------|
| Assigned services list | OK | `pending_approval`, `assigned`, `in_progress`, `completed`; paginated; with patient, serviceType, assignedStaff. |
| Missing payout calculation | OK | Only when `assignedStaff` and `serviceType` exist; null-safe. |
| Stats (assignments, active, completed, pending) | OK | From same base query. |
| Service request earnings | OK | Total approved, pending approval, upcoming, this month. |
| Patient reward earnings | OK | RewardService + CaregiverReward. |
| Staff referral earnings | OK | ReferralService; points-only. |
| Subscription referral earnings | OK | optional($subscriptionReferralStats)->... (fixed). |
| Referral links (staff + subscription) | OK | ReferralService + `route('plans.index', ['ref' => $user->id])`. |
| Quick links (Rewards, Staff Referrals, Subscription Referrals, Payment Settings/History) | OK | Routes exist and are correct. |
| Nav: Dashboard, My Assignments, Patient Rewards, Staff Referrals, Subscription Referrals, Payment Settings, Payment History | OK | layout.blade.php; all routes valid. |

**Verdict:** Staff dashboard is in good shape.

---

### 1.3 Service Details (`/staff/service/{id}`)

| Element | Status | Notes |
|---------|--------|--------|
| Access control | OK | Must be `assigned_staff_id`; else 403. |
| Assigned staff / service type null | OK | Abort 404 with message. |
| Payout calculation when missing | OK | Same null checks. |
| Start Service (button → POST JSON) | OK | fetch to `/staff/service/{id}/start`; status transition validated; JSON response; view uses JS. |
| Complete Service (button → POST JSON) | OK | Same pattern; end_date validated. |
| Earnings card (approved / pending approval / projected) | OK | Uses `isApprovedByAdmin()`, `approvedBy`, `admin_approved_at`. |
| Service type / patient / daily services | OK | Loaded in controller. |

**Verdict:** Service details and Start/Complete flows are correct.

---

### 1.4 Accept / Reject Booking (from dashboard)

| Action | Route | Status | Notes |
|--------|--------|--------|-------|
| Accept | POST `staff.booking.accept` | OK | Status check; transition; redirect + success/error. |
| Reject | POST `staff.booking.reject` | OK | Same; rejection reason supported. |

**Verdict:** Accept/Reject booking work; user sees generic error on exception.

---

### 1.5 Rewards (Patient Details for Points)

| Step | Route / Action | Status | Notes |
|------|----------------|--------|--------|
| List | `staff.rewards.index` → StaffDashboardController::rewards | OK | Shows staff’s CaregiverReward list + stats. |
| Create form | Link to `rewards.create` (Rewards module) | OK | role:caregiver,nurse. |
| Submit | POST `rewards.store` | OK | Validation (phone 10 digits, pincode 6, etc.); RewardService::createReward; credits user reward_points. |
| Duplicate phone | OK | Validator rejects "This mobile number has already been submitted." |

**Verdict:** Rewards flow is correct.

---

### 1.6 Staff Referrals & Subscription Referrals

| Page | Route | Status | Notes |
|------|--------|--------|--------|
| Staff referrals | `staff.staff-referrals.index` | OK | ReferralService::getReferralHistory; link for inviting staff. |
| Subscription referrals | `staff.subscription-referrals.index` | OK | Subscriptions where referrer_id = user; subscription referral link. |

**Verdict:** Both referral sections work.

---

### 1.7 Payment Settings & Payment History

| Page | Route | Status | Notes |
|------|--------|--------|--------|
| Settings | `staff.payments.settings` | OK | UPI ID + QR upload. |
| Update settings | POST `staff.payments.settings.update` | OK | Validation; Storage::disk('public') for QR. |
| History | `staff.payments.history` | OK | StaffPayment for current user; paginated. |

**Verdict:** Staff payment settings and history are correct.

---

### 1.8 Profile & Documents (shared with other roles)

| Feature | Status | Notes |
|---------|--------|--------|
| Profile view/edit | OK | ProfileController; on load failure redirect (fixed). |
| Avatar upload | OK | ProfileService disk('public'). |
| Documents list/upload/view/download/delete | OK | DocumentService disk('public'). |

**Verdict:** Profile and documents are fine for staff.

---

### 1.9 Nurse/Caregiver — Summary

| Area | Status |
|------|--------|
| Login → Dashboard | OK |
| Dashboard data & links | OK |
| Service details, Start/Complete | OK |
| Accept/Reject booking | OK |
| Rewards (list, create, store) | OK |
| Staff & Subscription referrals | OK |
| Payment settings & history | OK |
| Profile & documents | OK |

**No FIX items left for nurse/caregiver from this analysis.** Optional CHECK: run through one full cycle (accept → start → complete) and confirm admin approval and payment flow (see Admin section).

---

## 2. Patient — Full Flow

### 2.1 Login & Redirect

| Step | Status | Notes |
|------|--------|--------|
| Login | OK | Same auth; redirect to `dashboard`. |
| DashboardController | OK | Patient sees patient dashboard (not staff.dashboard). |

**Verdict:** OK.

---

### 2.2 Patient Dashboard

| Element | Status | Notes |
|---------|--------|--------|
| Service requests (paginated) | OK | With serviceType, assignedStaff. |
| Nearby staff (if pincode) | OK | LocationService::getNearbyStaff (MySQL spatial; you use MySQL). |
| Service types for pricing | OK | GetActiveServiceTypes. |
| Active subscription / hasActiveSubscription | OK | SubscriptionService. |
| Quick links (Staff, My Requests, Plans, Subscriptions) | OK | |

**Verdict:** OK.

---

### 2.3 Staff Listing & Booking

| Step | Route / Action | Status | Notes |
|------|----------------|--------|--------|
| Staff list | `staff.index` (role:patient) | OK | serviceTypes first() null-safe (fixed). |
| Book staff | GET `book.staff` | OK | |
| Store booking | POST `book.store` (storeDirectBooking) | OK | Validation; generic error on exception (fixed). |

**Verdict:** OK.

---

### 2.4 My Requests, Profile, Documents, Plans, Subscriptions

| Feature | Status | Notes |
|---------|--------|--------|
| My Requests | `services.my-requests` | OK | |
| Profile / Documents | OK | Same as staff; failures handled. |
| Plans list & show | OK | Plan null-safe in views (fixed). |
| Subscriptions list/show | OK | Generic error on index failure (fixed). |
| Subscribe / submit payment / verify (admin) / cancel / renew | OK | Generic messages in catch blocks (fixed). |

**Verdict:** Patient flows are in good shape.

---

### 2.5 Patient — Summary

| Area | Status |
|------|--------|
| Login → Dashboard | OK |
| Staff listing & direct book | OK |
| My Requests, Profile, Documents | OK |
| Plans & Subscriptions | OK |

**No FIX items left for patient from this analysis.**

---

## 3. Admin — Full Flow (Including Staff Payments)

### 3.1 Login & Dashboard

| Step | Status | Notes |
|------|--------|--------|
| Login → admin dashboard | OK | DashboardController redirects admin to `admin.dashboard`. |
| Admin dashboard | OK | getFinancialStats null-safe (fixed). |
| Pending payments link | OK | `admin.pending-payments`. |

**Verdict:** OK.

---

### 3.2 Users & Profiles

| Feature | Route / Action | Status | Notes |
|---------|----------------|--------|--------|
| List users | `admin.users` | OK | |
| Add/Edit/View user | OK | |
| Toggle status, reset password, delete non-admin | OK | |
| View profile | `admin.profiles.view` | OK | getProfile failure → redirect (fixed). |

**Verdict:** OK.

---

### 3.3 Service Requests (Assign Staff & Approve Payment)

| Action | Status | Notes |
|--------|--------|-------|
| List/filter | `admin.service-requests` | OK | |
| Assign staff form | `admin.service-requests.assign` | OK | |
| POST assign | OK | Validation; conflict check; createDailyServiceRecords. |
| Approve payment | POST `admin.service-requests.approve-payment` | OK | Only when status = completed; lock; sets admin_approved_at + approved_by; generic error on exception. |

**Verdict:** Service request and payment approval flow are correct. Staff see “Approved by admin” and amount in dashboard/service details after approval.

---

### 3.4 Staff Payments (Admin → Nurse/Caregiver)

This is the direct link between admin and staff earnings.

| Step | Route / Action | Status | Notes |
|------|----------------|--------|--------|
| List pending | `admin.payments.index` | OK | Only staff with total pending > 0; breakdown by service_request, patient_reward, subscription_referral. |
| Open form | `admin.payments.form` with `type` = service_request \| patient_reward \| subscription_referral | OK | type=all or invalid → redirect. Index links pass correct type and only when amount > 0. |
| Process payment | POST `admin.payments.process` | OK | Validates type, amount ≥ 0.01, transaction_id, payment_screenshot; creates StaffPayment; markAsPaid() marks related records (service_request / patient_reward / subscription_referral). |
| Staff sees in Payment History | `staff.payments.history` | OK | StaffPayment where staff_id = user; staff sees payments after admin processes. |
| UPI/QR on form | OK | Handles missing UPI/QR with “No UPI ID provided” / “No QR code uploaded”. |

**Verdict:** Admin staff payment flow is correct and connected: admin processes payment → staff sees it in Payment History; service/reward/subscription referral records are marked paid so they drop out of pending.

---

### 3.5 Subscriptions, Plans, Subscription Settings

| Feature | Status | Notes |
|---------|--------|--------|
| List/view subscriptions | OK | |
| Approve / Reject / Verify payment / Reject payment | OK | Generic error messages (fixed). |
| View payment screenshot | OK | Generic error (fixed). |
| Plans CRUD | OK | |
| Subscription settings (GST, commission, UPI, merchant) | OK | Config exists check + generic error (fixed). |

**Verdict:** OK.

---

### 3.6 Referrals, Rewards, System Reset

| Feature | Status | Notes |
|---------|--------|--------|
| Referrals list / by staff | OK | |
| Rewards (patient details) list | `admin.rewards.index` | OK | Sidebar link present (Reward Submissions). |
| System reset | OK | Blocked in production; generic error (fixed). |

**Verdict:** OK.

---

### 3.7 Admin — Summary

| Area | Status |
|------|--------|
| Dashboard, users, profiles | OK |
| Service requests: assign & approve payment | OK |
| Staff payments: list → form (by type) → process → staff history | OK |
| Subscriptions, plans, subscription settings | OK |
| Referrals, rewards, system reset | OK |

**No FIX items left for admin from this analysis.**

---

## 4. Cross-Role Flows (Sanity Check)

| Flow | What to verify |
|------|----------------|
| Patient books staff → Staff sees “pending_approval” → Staff accepts → Status “assigned” → Staff starts → “in_progress” → Staff completes → “completed” | OK in code; CHECK manually once. |
| Admin approves payment for completed service → Staff sees “Approved by Admin” and amount | OK; approved_by and admin_approved_at set. |
| Admin processes staff payment (e.g. service_request) → StaffPayment created → Staff sees in Payment History; service_request.staff_payment_processed = true | OK; markAsPaid implements this. |
| Staff submits reward → Admin sees in Rewards list; Admin pays via “Pay Patient Rewards” → reward payment_processed | OK; same markAsPaid pattern. |
| Staff subscription referral link → Patient subscribes with ref → Admin verifies → Subscription referrer_id set; admin can “Pay Subscription Referrals” | OK; referral_commission and referral_payment_processed. |

---

## 5. Recommended Manual Checks (CHECK)

These are not bugs but worth one-time verification on your environment:

1. **Nurse/Caregiver:** Login → Dashboard → open one assignment → Start → Complete; confirm status and earnings text update.
2. **Admin:** Service request completed → Approve payment → Staff dashboard/service details show “Approved by Admin”.
3. **Admin:** Staff payments → open form for one type (e.g. Service Requests) → fill amount, transaction ID, screenshot → submit; confirm staff Payment History shows the payment and pending list decreases.
4. **Patient:** Staff list with no service types in DB (if possible); page should load with default payouts (already fixed in code).
5. **All roles:** Profile and Documents (view, upload, delete) to confirm storage and disk('public') behave as expected.

---

## 6. Summary Table

| Role | Login & redirect | Dashboard | Core actions | Payments / earnings | Verdict |
|------|------------------|-----------|--------------|---------------------|--------|
| Nurse / Caregiver | OK | OK | OK (assignments, start/complete, accept/reject, rewards, referrals) | OK (settings, history; admin process → history) | No FIX |
| Patient | OK | OK | OK (staff, book, my requests, profile, documents, plans, subscriptions) | N/A | No FIX |
| Admin | OK | OK | OK (users, service requests, assign, approve payment, staff payments, subscriptions, plans, referrals, rewards) | OK (staff payment flow correct) | No FIX |

---

## 7. What to Fix (FIX) — None Remaining

All previously identified crash and error-message issues have been addressed. This analysis did not surface new FIX items.

If you later see a specific bug (e.g. a particular page, role, or action), add it under a new **FIX** row in the relevant section and then implement the fix with the same rules: no data loss, no change to success-path behaviour.

---

**Document version:** 1.0  
**Last updated:** After full role-by-role code and route review.  
**Next:** Run the manual CHECK list above once in your environment; then treat the CRM as functionally complete for these flows unless a new issue appears.

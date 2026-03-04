# MMHC CRM — Crash Risk Analysis

This document lists circumstances under which the application can crash or return fatal errors, based on a codebase review. It is intended to harden the system and avoid regressions.

---

## 1. Summary: How Likely Is a Full CRM Crash?

| Scenario | Likelihood | Impact | Notes |
|----------|------------|--------|--------|
| **Production on MySQL, normal use** | Low | High | Most paths are guarded; a few edge cases can still throw. |
| **Running on SQLite (local/tests)** | High | High | Several MySQL-only statements and spatial functions will throw. |
| **Empty or missing data (no service types, no admin, etc.)** | Medium | Medium | Specific pages or actions throw when assumptions break. |
| **Config / file system issues** | Medium | Medium | Missing config file or wrong disk usage can cause errors. |
| **Unhandled exceptions in views** | Medium | High | Some views assume non-null data; exception handler passes null and view crashes. |
| **Unbounded queries / large data** | Low–Medium | Medium | Timeouts or OOM under load, not an instant PHP fatal. |

**Overall:** The CRM can crash in a **narrow but real set of circumstances**. It is **not** “crash-proof” today, especially when using SQLite, when data is missing, or when config/files are wrong. The sections below detail each risk.

---

## 2. Database-Related Crashes

### 2.1 Using SQLite Instead of MySQL

**.env** defaults to `DB_CONNECTION=sqlite`. Several parts of the app use **MySQL-only** syntax. On SQLite they will throw and can take down a request or command.

| Location | What breaks | When it happens |
|----------|-------------|------------------|
| `SystemResetService::resetSystemData()` | `DB::statement('SET FOREIGN_KEY_CHECKS=0;')` | Admin runs “System Reset”. |
| `Console\Commands\DeleteAllData` | Same `SET FOREIGN_KEY_CHECKS` | Someone runs `php artisan db:delete-all`. |
| `AuthController` (register, storeUser, updateUser) | `DB::raw("ST_GeomFromText('POINT(...)', 4326)")` | User registers or admin updates user with pincode/location. |
| `LocationService::setUserLocation`, `createSpatialPoint`, `getNearbyStaff` | `ST_GeomFromText`, `ST_Distance_Sphere`, `whereRaw` with spatial SQL | Any flow that sets or queries user location (pincode-based staff listing, registration, profile). |
| `StaffController::getStaffList()` (no pincode branch) | `orderByRaw("CAST(SUBSTRING_INDEX(experience, '-', 1) AS UNSIGNED) DESC")` | Staff listing with sort=experience and no pincode. |

**Fix direction:** Guard MySQL-only code with `DB::getDriverName() === 'mysql'`, or provide SQLite-safe alternatives (no spatial, no `FOREIGN_KEY_CHECKS`, different sort expression).

---

### 2.2 ModelNotFoundException / findOrFail

These turn into **404 or 500** if not caught. Most are inside controllers that Laravel can convert to 404; ensure no critical flow is left unhandled.

- `User::findOrFail`, `Plan::findOrFail`, `Document::findOrFail`, `ServiceRequest::lockForUpdate()->findOrFail`, etc.
- If a route uses route-model binding and the ID is invalid, Laravel throws `ModelNotFoundException` and returns 404. That’s acceptable; just ensure no code assumes the model always exists after a “find” without binding.

---

### 2.3 Null on `->first()` Then Property Access

**Actual bug (can crash):**

- **`DashboardController::getFinancialStats()`** (around line 381–382):
  - Code: `->selectRaw('...')->first()->pending ?? 0`
  - If the query returns **no rows**, `first()` is **null**. Then `null->pending` throws.
  - **When:** Admin dashboard when there are no service requests (or no matching rows for that aggregate).

**Fix:** Use optional: `optional($query->first())->pending ?? 0`, or assign `$row = $query->first()` and then `$row ? $row->pending : 0`.

---

## 3. Empty or Missing Data (Views / Logic)

### 3.1 Staff Listing When There Are No Service Types

- **File:** `app/Modules/Services/Views/staff/index.blade.php`
- **Code:** `$serviceTypes->first()->nurse_payout ?? 2000` and `$serviceTypes->first()->caregiver_payout ?? 1500`
- **Issue:** If `ServiceType::active()->ordered()->get()` is **empty**, `$serviceTypes->first()` is **null**. Then `null->nurse_payout` throws.
- **When:** Patient opens Staff listing and there are **no active service types** in the DB (e.g. fresh install or all disabled).

**Fix:** Use `$serviceTypes->first()?->nurse_payout ?? 2000` (PHP 8+) or wrap in `@if($serviceTypes->isNotEmpty())` and use a default otherwise.

---

### 3.2 Profile View When Profile Fails to Load

- **Files:** `ProfileController::index()` and `edit()`; views `profiles::profile.index` and `profile.edit`
- **Flow:** If `$this->profileService->getProfile($user)` throws, the catch block sets `$profile = null` and still returns the same view with `compact('user', 'profile')`.
- **Issue:** The views use `$profile->avatar_path`, `$profile->getCompletionPercentage()`, etc. With `$profile === null`, this causes “Trying to get property … of null” or “Call to a member function … on null”.
- **When:** Any failure in `getProfile()` (DB error, missing profiles table, etc.).

**Fix:** In the catch block, redirect to an error page or a “profile unavailable” view instead of passing `$profile = null` into a view that assumes a profile object. Or make the view null-safe for every `$profile` use.

---

### 3.3 Dashboard View and `$service_types->first()`

- **File:** `app/Modules/Auth/Views/dashboard.blade.php`
- **Code:** `$service_types->first()->patient_charge` inside `@if($service_types->count() > 0)`.
- **Status:** Safe as long as the condition is really “count > 0” (so `first()` is non-null).

---

## 4. Config and File System

### 4.1 Subscription Settings Update — Missing Config File

- **File:** `SubscriptionSettingsController::update()`
- **Code:** `$configPath = config_path('subscription.php');` then `File::get($configPath);` and later `File::put($configPath, ...)`.
- **Issue:** If `config/subscription.php` is missing (e.g. not committed, or wrong env), `File::get($configPath)` throws. Same if the path is not readable.
- **When:** Admin saves “Subscription settings” when the config file is absent or not readable.

**Fix:** Check `File::exists($configPath)` before read; return a clear error or create a default config instead of throwing.

---

### 4.2 Subscription Index — Exposing Exception in Response

- **File:** `SubscriptionController::index()`
- **Code:** In catch block: `return response('Error loading subscriptions: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);`
- **Issue:** Any exception (including DB driver errors, missing tables, etc.) is turned into a 500 with **full message and path** in the body. That can leak paths and internal state; it also looks like a “crash” to the user.
- **When:** Any uncaught exception in that method (e.g. bad DB connection, missing subscription config, wrong driver).

**Fix:** Log the full exception; return a generic error message and 500 (or a friendly error view) without `getMessage()`, `getFile()`, or `getLine()` in the response.

---

### 4.3 Payment Screenshot View — Error Response

- **File:** `SubscriptionController::viewPaymentScreenshot()`
- **Code:** In catch: `return response('Error loading screenshot: ' . $e->getMessage(), 500);`
- **Issue:** Same pattern: exception message in response. Less critical than index but still not ideal for production.

**Fix:** Log and return a generic message.

---

### 4.4 Storage Disk Consistency

- **ProfileService::uploadAvatar()** and **DocumentService::deleteDocument()** use `Storage::exists()` / `Storage::delete()` **without** specifying a disk. Files are stored with `'public'` (e.g. `store(..., 'public')`).
- **Issue:** If the default disk is not `public`, exists/delete may run against the wrong disk. Result: file not found or not deleted; usually no PHP fatal, but logic can be wrong. If the default disk driver misbehaves on a path, it could throw.
- **Recommendation:** Use `Storage::disk('public')->exists(...)` and `Storage::disk('public')->delete(...)` for these paths so behavior is consistent and predictable.

---

## 5. Thrown Exceptions in Business Logic

These are intentional `throw new \Exception` or `abort()`. They **will** stop the request or flow unless caught higher up:

- **SubscriptionService::createSubscription()** — “Invalid payment frequency” if plan options don’t match.
- **SubscriptionController** — “Subscription was not created successfully”, “File upload failed”, etc.
- **ServiceController::assign()** — “Cannot assign staff. Invalid status transition …”.
- **StaffDashboardController** — “Assigned staff not found”, “Service type not found” (inside transaction).
- **SystemResetService** — “Admin user not found. Cannot proceed with reset.”

Ensure that:

- All such throws are either caught and turned into user-friendly messages or logged and rethrown in a controlled way.
- No uncaught exception propagates to the global handler in a way that exposes stack traces or paths (see 4.2, 4.3).

---

## 6. Concurrency and Locking

- **ServiceController::assign()** uses `ServiceRequest::lockForUpdate()->findOrFail($serviceRequest->id)` for assignment. That’s correct for avoiding double-assignment. If the lock fails or the transaction is misused, you could get timeouts or deadlocks rather than a clean “crash”; monitor and test under load.

---

## 7. Environment and Deployment

- **APP_KEY** empty: Laravel will complain on encrypt/session; can cause runtime errors.
- **APP_DEBUG=true** in production: Any uncaught exception shows a full stack trace and env details — treat as “information leak” and possible crash-like experience for users.
- **Missing .env or wrong DB credentials:** Requests that hit the DB will fail (connection errors). With the current SubscriptionController index catch block, that still returns a 500 with exception text (see 4.2).

---

## 8. Unbounded Queries (Stability, Not Immediate Crash)

- Several places use `->get()` on large sets (e.g. `ServiceRequest::with([...])->get()`, `User::where(...)->get()`) in dashboard and admin. With big data, this can cause:
  - High memory usage.
  - Slow responses or timeouts.
- That behaves like “the app is down” rather than a PHP fatal. Mitigation: use `paginate()` or `limit()` and cap per-page size where appropriate.

---

## 9. Checklist: Circumstances That Can Crash or Severely Break the CRM

- [ ] **SQLite as DB driver** — System reset, delete-all, registration/update with location, staff listing (experience sort), and nearby-staff queries can throw.
- [ ] **No admin user** — System reset throws “Admin user not found.”
- [ ] **No active service types** — Staff listing page can throw on `$serviceTypes->first()->…`.
- [ ] **No rows for pending service payment aggregate** — Admin dashboard `getFinancialStats()` can throw on `->first()->pending`.
- [ ] **Profile load failure** — Profile index/edit can throw when view uses `$profile` while it’s null.
- [ ] **Missing config/subscription.php** — Subscription settings update can throw on `File::get`.
- [ ] **Any uncaught exception in SubscriptionController::index()** — Returns 500 with full exception text (and path) in body.
- [ ] **Wrong or missing APP_KEY / DB_* / default disk** — Can cause encryption, session, or storage errors and 500s.

---

## 10. Recommended Order of Fixes (To Reduce Crash Risk)

1. **DashboardController** — Fix `->first()->pending` (use `optional(…)->pending ?? 0` or equivalent).
2. **SubscriptionController::index()** — Stop putting exception message/file/line in the 500 response; log and return generic message.
3. **ProfileController + profile views** — Either stop passing `$profile = null` to the same view, or make the view fully null-safe; prefer redirect or dedicated error view on failure.
4. **Staff index view** — Guard or null-safe `$serviceTypes->first()` (e.g. `?->` or `@if($serviceTypes->isNotEmpty())` with default).
5. **SystemResetService and DeleteAllData** — Run MySQL-only statements only when `DB::getDriverName() === 'mysql'`; document or implement SQLite-safe path.
6. **LocationService / AuthController** — Guard or abstract spatial and raw SQL so they run only on MySQL (or provide SQLite fallback).
7. **StaffController** — Make experience sort SQLite-safe (e.g. avoid `SUBSTRING_INDEX` on SQLite).
8. **SubscriptionSettingsController** — Check config file existence before `File::get`; handle missing file with a clear message or default.
9. **Storage** — Use `Storage::disk('public')` explicitly where files are stored on the public disk (ProfileService, DocumentService) to avoid wrong-disk behavior and edge-case errors.

After these, the CRM will be much less likely to crash in the circumstances listed above. Re-run this checklist when adding new features or changing DB/config assumptions.

# Change Report – Session (Last 2–3 Hours)

This report lists all code changes made in this session and whether any **existing** functionality was modified (and what you should test before pushing to git).

---

## 1. NEW FILES (additive only – no impact on old behaviour)

| File | Purpose |
|------|--------|
| `database/migrations/2026_01_30_000000_create_achievement_media_table.php` | Table for carousel images |
| `database/migrations/2026_01_30_000001_widen_service_requests_contact_phone.php` | `contact_phone` VARCHAR(10)→20 |
| `database/migrations/2026_01_30_000002_create_site_settings_table.php` | Table for logo/company/tagline |
| `app/Models/AchievementMedia.php` | Model for carousel items |
| `app/Models/SiteSetting.php` | Model for site settings (key-value) |
| `app/Http/Controllers/Admin/AchievementMediaController.php` | CRUD + reorder for carousel |
| `app/Http/Controllers/Admin/SiteSettingsController.php` | Logo/company/tagline admin |
| `resources/views/admin/achievement-media/index.blade.php` | List + add carousel images |
| `resources/views/admin/achievement-media/edit.blade.php` | Edit single carousel image/caption |
| `resources/views/admin/site-settings/index.blade.php` | Site settings form (logo, name, tagline) |
| `database/seeders/AchievementMediaSeeder.php` | Demo images for carousel |

---

## 2. EXISTING FILES MODIFIED – IMPACT SUMMARY

### 2.1 **routes/web.php**

**Changes:**

- Pass `$achievementMedia` into the view for `/` and `/landing`.
- New admin routes: achievement-media (index, edit, store, update, order, move-up, move-down, destroy), site-settings (index, update).

**Existing behaviour:**

- All previous routes unchanged.
- Only **addition**: two closures now pass one extra variable to `welcome`.

**Risk:** If migrations are **not** run, `AchievementMedia::ordered()->get()` will throw (table missing). **Action:** Run `php artisan migrate` before using `/` or `/landing`.

**What to test:** Homepage (`/`), landing (`/landing`), all existing auth, services, plans, subscriptions, admin sections (except the new ones).

---

### 2.2 **resources/views/welcome.blade.php**

**Changes:**

- Header logo: `asset('images/med-logo.png')` → `$siteLogoUrl ?? asset('images/med-logo.png')`, alt/sr-only use `$siteCompanyName ?? 'MeD Miracle Health Care'`.
- New block: “Achievements & Media Coverage” carousel **above** “Our Core Values”, only if `isset($achievementMedia) && $achievementMedia->isNotEmpty()`.

**Existing behaviour:**

- Rest of page (hero, plans, about, mission/vision, core values, awards, founder, contact, etc.) **unchanged**.
- Logo falls back to current image if `$siteLogoUrl` is missing.

**What to test:** Full landing scroll (hero, plans, about, mission/vision, **new carousel**, core values, awards, founder, contact). Logo still shows when no site settings are set.

---

### 2.3 **app/Modules/Auth/Views/layout.blade.php**

**Changes:**

- Sidebar logo: use `$siteLogoUrl`, `$siteCompanyName`, `$siteTagline` with `??` fallbacks.
- New sidebar links (admin only): “Achievements & Media”, “Site Settings”.
- Site Settings icon changed from `fa-image` to `fa-cog`.

**Existing behaviour:**

- All existing sidebar links, structure, and styling **unchanged**.
- Logo/tagline fall back to current text and image if variables are missing.

**What to test:** Admin and staff dashboards: sidebar logo and tagline, all existing menu items (Dashboard, Manage Users, Service Requests, etc.), and that new items (Achievements & Media, Site Settings) work.

---

### 2.4 **app/Modules/Auth/Views/components/navbar.blade.php**

**Changes:**

- Logo `src` and `alt` use `$siteLogoUrl ?? asset('images/med-logo.png')` and `$siteCompanyName ?? 'MeD Miracle Health Care'`.

**Existing behaviour:**

- Only the logo image and alt text are dynamic; rest of navbar **unchanged**.

**What to test:** Top navbar (when logged in) shows logo and no layout/links broken.

---

### 2.5 **app/Modules/Auth/Views/login.blade.php**

**Changes:**

- Logo: `asset('images/med-logo.png')` → `$siteLogoUrl ?? asset('images/med-logo.png')`, alt uses `$siteCompanyName`.

**Existing behaviour:**

- Form, validation, redirects, and layout **unchanged**.

**What to test:** Login (mantu@themmhc.com / password123), error messages, redirect after login.

---

### 2.6 **app/Modules/Auth/Views/register-tabbed.blade.php**

**Changes:**

- Single line: logo `src` and `alt` use `$siteLogoUrl` and `$siteCompanyName` with `??` fallbacks.

**Existing behaviour:**

- Registration flow, tabs, validation **unchanged**.

**What to test:** Register (patient/nurse/caregiver), all tabs, submit.

---

### 2.7 **app/Providers/AppServiceProvider.php**

**Changes:**

- In `boot()`: if `site_settings` table exists, share `siteLogoUrl`, `siteCompanyName`, `siteTagline` from `SiteSetting`; else share default logo/name/tagline.

**Existing behaviour:**

- No other providers or boot logic touched.
- If `site_settings` does not exist, only defaults are shared (no DB read).

**Risk:** None for old features; logo/name/tagline are additive. **What to test:** One request to a page that uses layout (e.g. dashboard) to confirm no 500 and logo appears.

---

### 2.8 **database/seeders/DemoDataSeeder.php**

**Changes:**

- Imports: added `DB`, `Hash`.
- After creating/finding admin: `DB::table('users')->where('id', $admin->id)->update(['password' => Hash::make('password123')])` so admin password is always correct for login.
- When `ServiceType::all()->isEmpty()`: call `ServiceTypesSeeder` then re-fetch service types (so running only `DemoDataSeeder` works).

**Existing behaviour:**

- Nurse, caregiver, patient creation **unchanged**.
- Service-request and daily-service creation logic **unchanged** (same data, same flow).
- Only admin password write and “seed service types if empty” are new.

**What to test:** Run `php artisan db:seed --class=DemoDataSeeder` (after migrations). Then login as mantu@themmhc.com / password123; check dashboard and that demo service requests still appear as before.

---

### 2.9 **database/seeders/DatabaseSeeder.php**

**Changes:**

- Call `AchievementMediaSeeder::class` after `HealthcarePlansSeeder`, before `SubscriptionPlansSeeder`.

**Existing behaviour:**

- Order of other seeders **unchanged**.
- New seeder only inserts achievement media when table is empty.

**What to test:** Full `php artisan db:seed` (or `migrate:fresh --seed` if you use it). No duplicate errors; existing seeded data (users, plans, service types, etc.) unchanged.

---

### 2.10 **app/Console/Commands/ResetAdminPassword.php**

**Changes:**

- Password update: from `$admin->password = Hash::make(...); $admin->save();` to `DB::table('users')->where('id', $admin->id)->update(['password' => Hash::make($newPassword)]);`.

**Existing behaviour:**

- Command signature and output **unchanged**.
- Only the way the hash is written to DB changed (bypasses model to avoid double-hash).

**What to test:** Run `php artisan admin:reset-password` (or with email/password). Then log in with the new password.

---

### 2.11 **resources/views/admin/page-content/index.blade.php**

**Changes:**

- New card at top: “Achievements & Media Coverage” with link “Manage carousel images” to `admin.achievement-media.index`.

**Existing behaviour:**

- All existing section cards and “Edit Section” links **unchanged**.

**What to test:** “Edit Landing Page” list: all sections still editable; new card goes to Achievements & Media.

---

### 2.12 **app/Modules/Auth/Views/admin/dashboard.blade.php**

**Changes:**

- New quick-action card: “Achievements & Media” → “Carousel images” linking to `admin.achievement-media.index`.

**Existing behaviour:**

- Stats, other quick actions, recent activity **unchanged**.

**What to test:** Admin dashboard: all stats and existing quick actions (e.g. Edit Landing Page, Manage Users) still work; new card opens Achievements & Media.

---

## 3. CONTROLLERS / MODULES NOT TOUCHED

- **AuthController** – login/register logic unchanged.
- **PageContentController** – unchanged.
- **SubscriptionController, PlanController, SubscriptionSettingsController** – unchanged.
- **ServiceController, StaffController, StaffDashboardController** – unchanged.
- **ProfileController, DocumentController** – unchanged.
- **Payments, Referrals, Rewards** – unchanged.
- **HealthcarePlanController** – unchanged (page-content plans are in PageContentController).

So no changes to auth, subscriptions, plans, services, staff, profiles, documents, payments, referrals, or rewards logic.

---

## 4. MIGRATIONS REQUIRED BEFORE USE

Run once (if not already):

```bash
php artisan migrate
php artisan storage:link   # for uploaded logo and carousel images
```

- **achievement_media** – required for `/`, `/landing`, and Achievements & Media admin.
- **site_settings** – required for Site Settings admin; optional for app (fallbacks exist).
- **service_requests.contact_phone** – required for DemoDataSeeder (and any code that stores long contact numbers).

---

## 5. RECOMMENDED TEST CHECKLIST BEFORE GIT PUSH

1. **Auth:** Login (admin), Register (one role), Logout.
2. **Landing:** Open `/` and `/landing`; scroll through all sections; confirm new carousel and logo.
3. **Admin sidebar:** All menu items load (Dashboard, Manage Users, Service Requests, Referral Management, Reward Submissions, Manage Subscriptions, Manage Plans, Subscription Settings, Staff Payments, **Achievements & Media**, **Site Settings**).
4. **Admin – Achievements & Media:** List, Add image, Edit (image + caption), Move up/down, Delete; check landing carousel updates.
5. **Admin – Site Settings:** Change logo/company name/tagline; confirm sidebar and login/register/welcome update.
6. **Admin – Edit Landing Page:** All section cards and “Manage carousel images” link work.
7. **Admin – other:** Quick actions (e.g. Edit Landing Page, Manage Users), Manage Subscriptions, Manage Plans (no regression).
8. **Seeding:** `php artisan db:seed --class=DemoDataSeeder` completes; then login as admin and see demo data.
9. **Password reset:** `php artisan admin:reset-password` then login with new password.

---

## 6. SHORT SUMMARY

- **New behaviour only:** Achievements & Media carousel (front + admin), Site Settings (logo/name/tagline), edit carousel item, contact_phone length fix, admin password fix in seeder and reset command.
- **Touched but backward-safe:** `welcome`, `layout`, `navbar`, `login`, `register-tabbed` (logo/name/tagline with fallbacks), `routes/web.php` (extra variable + new routes), `AppServiceProvider` (View::share with table check), `DemoDataSeeder`, `DatabaseSeeder`, `ResetAdminPassword`, `page-content/index`, admin dashboard.
- **Not touched:** Auth, subscriptions, plans, services, staff, profiles, documents, payments, referrals, rewards, or any other existing controllers/modules.

If you want, we can add a one-line “Session change report” note in your main README or DEPLOYMENT.md and point to this file.

# MMHC CRM — Mobile coverage audit (deep check)

Last audit: mobile Phases 1–3 + gap fixes.

## Covered automatically (auth layout + global CSS)

**~95 module views** extend `auth::layout` and receive:

- `public/css/mobile-crm.css`
- `public/js/mobile-crm.js` (table scroll wrappers, `mmhc-mobile` class)
- `public/css/capacitor-app.css` + `capacitor-app.js` (native WebView)
- Top navbar + sidebar offcanvas (☰ / **Menu**)
- Bottom navigation on every logged-in page (`auth::components.bottom-nav`)

Includes: staff/patient dashboards, profile, documents, services, plans, subscriptions, rewards, referrals, payments, academics, community, admin users, admin service requests, admin rewards/referrals, incentives preview, site backups (auth layout).

## Bottom nav shortcuts (full menu = sidebar offcanvas)

| Role | Tabs | Full menu |
|------|------|-----------|
| Patient | Home (Community), Requests, Staff, Menu | All sidebar links |
| Staff | Home, Jobs, Rewards, Menu | All sidebar links |
| Admin | Home (admin dashboard), Users, Services, Menu | All sidebar links |
| Academic | Academics, Community, Menu | All sidebar links |

**Menu** opens `#mmhcAppSidebar` — same links as desktop; nothing removed.

## Intentionally separate layouts (OK)

| Page | Layout | Mobile |
|------|--------|--------|
| `/`, `/landing` | `welcome.blade.php` | Tailwind + `mobile-crm.css` + capacitor |
| `/login`, register | `auth::layout` (guest) | Auth full-screen + global CSS |
| Staff ID print | `id-cards/layout-print` | Print-focused; no bottom nav |
| Public QR verify | `id-cards/verify.blade.php` | Standalone; already responsive |

## Legacy admin CMS (fixed in audit)

These use standalone Tailwind HTML (not `auth::layout`). Now include `admin/partials/mobile-assets` + `mmhc-admin-standalone` body class:

- Site settings, page content, featured team, testimonials, achievement media, healthcare plans (index/edit/create)

**Note:** They do not show CRM sidebar/bottom nav (by design — simple admin tools). Mobile: scrollable tables, touch inputs, wrapped headers. Use **Back to Dashboard** → full CRM chrome.

`admin/site-backups` already uses `auth::layout`.

## Route fixes applied

- Admin bottom **Home** → `admin.dashboard` (was `dashboard` → community redirect)
- Staff bottom **Rewards** → `staff.rewards.index` (matches sidebar + staff dashboard)
- Patient bottom **Home** → `community.index` (matches post-login experience)

## Optional cleanup (not required)

- Some pages still `@include('auth::components.bottom-nav')` — `@once` prevents duplicate bars
- `dashboard.blade.php` / `staff/index.blade.php` retain legacy `.app-bottom-nav` CSS (harmless)

## Deploy checklist

```bash
git pull
php artisan view:clear
```

Hard-refresh browser / reinstall APK only for native splash changes.

## Manual test matrix

1. **Patient** — Community, My Requests, Find Staff, Profile (via Menu), book service, OTP banners
2. **Staff** — Dashboard, accept/reject job, Rewards, Referrals (Menu), Payment settings (Menu)
3. **Admin** — Dashboard, Manage Users (table scroll), Service Requests, Site settings (standalone), Menu → all admin links
4. **APK** — Same URLs; verify Menu opens sidebar and tables swipe horizontally

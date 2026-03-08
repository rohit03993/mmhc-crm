# Academics Module – Audit, Scope & Server Checklist

## 1. What the Academics Module Is

The **Academics** module is a separate area for **college/nursing institution** use (admin, faculty, students). It reuses the same `users` table and login; academic users are redirected to `/academics` after login.

**What it does:**
- **Institutions** – Super Admin creates colleges (e.g. MeD Miracle College of Nursing).
- **Batches** – Group of students per institution; students and faculty are assigned to batches.
- **Subjects** – Per batch (e.g. Anatomy, Physiology); faculty are assigned to subjects.
- **Topics** – Per subject; marked completed when enough students submit the linked assignment (configurable threshold).
- **Assignments** – Per topic (title, due date, attachments); students submit one file per assignment.
- **Submissions** – Student uploads; when submission % ≥ threshold, the topic is auto-marked completed.
- **Scores** – SPI (student: % assignments submitted), FPI (faculty: % topics completed), ICR (institution: % topics completed). Shown on dashboards and in reports.
- **Reports** – CSV export + Print: faculty performance, topic completion, student submission, batch progress.
- **Attendance** – Per batch, per date: faculty/admin mark Present/Absent/Leave; students view “My attendance” and summary %.

**Roles:** `super_admin` | `institution_admin` | `faculty` | `student`. All use the same login; post-login redirect sends them to `/academics` (or main dashboard if not academic).

---

## 2. What Touches the Rest of the App (and Why It’s Safe)

| Change | Where | Impact on non-academic users |
|--------|--------|------------------------------|
| **Post-login redirect** | `DashboardController::index()` | Only runs for logged-in users; if `hasAcademicRole()` is true → redirect to `/academics`. Patients, nurses, caregivers, admin are unchanged. |
| **User model** | `User.php` | New: `hasAcademicRole()`, `academicInstitution()`, `academicBatches()`, `academicSubmissions()`, `academicAttendances()`, `academic_institution_id` in fillable. These are only used when code explicitly uses them (academics routes/views). No global scope or boot that runs for every request. |
| **Layout sidebar** | `auth::layout` | Academics links are inside `@if(auth()->user()->hasAcademicRole())`. Others never see them and no academics queries run. |
| **Login page** | `AuthController`, `login.blade.php` | Two entry points: main login and academics login. Same form and POST; only wording differs. Redirect after login is still role-based. |
| **Navbar / welcome** | `navbar.blade.php`, `welcome.blade.php` | Extra “Academics” link for academics login. No academics DB access. |
| **CheckRole middleware** | `CheckRole.php` | Only checks `auth()->user()->role` against allowed list. No academics tables. |
| **Routes** | Main `routes/web.php` | No academics routes here. Academics routes live in `app/Modules/Academics/Routes/web.php`, all under `web` + `auth` + prefix `academics`. |

So: **patients, nurses, caregivers, admin, services, plans, subscriptions, staff dashboard, payments, etc. are unchanged.** Academics runs only when an academic user visits `/academics/*` or when code explicitly uses academic relations on a user.

---

## 3. What’s Complete vs Incomplete

**Complete:**
- Institutions, Batches, Subjects, Topics, Assignments, Submissions (CRUD and flows).
- Auto-completion of topics from submission threshold (`config/academics.php`: `completion_threshold`).
- SPI / FPI / ICR and role-based dashboard.
- Reports (batch progress, faculty performance, topic completion, student submission) with CSV and Print.
- Attendance (mark by batch+date; student “My attendance” and summary %).
- Separate “Academics” login entry and copy on main vs academics login.
- Demo seeder (`AcademicDemoSeeder`) including `location` for `users` (POINT).

**Incomplete / Optional (per earlier design):**
- Attendance report (e.g. % per student, defaulters list).
- Excel (.xlsx) export for reports.
- Draft submissions; late-submission weighting.
- Department / academic year as separate entities.
- Multiple assignments per topic and completion rule (any/all/average).

---

## 4. Server Checklist – Avoid Database Errors

Database errors on the server are almost always due to **migrations or seed data**, not “academics breaking other functionality.”

### Step 1: Run all migrations

Academics adds:
- **Main app:** `database/migrations/2026_03_05_100000_add_academic_roles_to_users_table.php` (extends `users.role` enum).
- **Module:** `app/Modules/Academics/Database/Migrations/`:
  - `000001` – `academic_institutions`
  - `000002` – `users.academic_institution_id`
  - `000003` – `academic_batches`
  - `000004` – `academic_batch_users`
  - `000005` – `academic_subjects`
  - `000006` – `academic_subject_faculty`
  - `000007` – `academic_topics`
  - `000008` – `academic_assignments`
  - `000009` – `academic_submissions`
  - `000010` – `academic_attendance`

Module migrations are loaded by `ModuleServiceProvider::loadModuleMigrations()`. So a single:

```bash
php artisan migrate --force
```

must run **both** the main migration and the Academics module migrations (order is by migration name).

If you see “table doesn’t exist” or “column doesn’t exist” for any `academic_*` or `users.role` / `users.academic_institution_id`, run:

```bash
php artisan migrate --force
```

and fix any failed migration (e.g. enum change on `users.role` if your MySQL version is strict).

### Step 2: `users.location` (POINT) and seeder

If the **seeder** fails with “Field 'location' doesn't have a default value”, the `users` table has a NOT NULL `location` (POINT) column from an existing spatial migration. The **AcademicDemoSeeder** already sets `location` for every user it creates (using `ST_GeomFromText('POINT(0 0)', 4326)`). Ensure the server has the **latest** seeder (with `location` in all user creation arrays). Then:

```bash
php artisan db:seed --class=AcademicDemoSeeder --force
```

If you create academic users **outside** this seeder (e.g. manual insert or another seeder), they must also set `location` (or the column must allow NULL / have a default).

### Step 3: `users.role` enum

If you see errors when **saving** a user with role `super_admin`, `institution_admin`, `faculty`, or `student`, the `users.role` enum may not include these values. That is fixed by:

```bash
php artisan migrate --force
```

which runs `2026_03_05_100000_add_academic_roles_to_users_table.php`. If that migration was skipped or failed, run it (or re-run migrations after fixing the cause).

### Step 4: Quick sanity check

After deploy:

1. **Non-academic:** Log in as patient/nurse/caregiver/admin → main dashboard and existing features (services, plans, etc.) work; no academics.
2. **Academic:** Log in as an academic user (e.g. from AcademicDemoSeeder) → redirect to `/academics`, dashboard loads, no “table/column doesn’t exist” errors.

If something still breaks, the exact error message (and, if possible, the URL and role) is enough to pinpoint whether it’s migration order, missing migration, or a one-off (e.g. `location` in another seeder).

---

## 5. Summary

- **Academics** is an add-on: same login, extra roles and tables, used only under `/academics` and when using academic relations.
- **Rest of the app** is unchanged for non-academic users.
- **Database errors** on the server are addressed by: (1) running all migrations, (2) using the updated AcademicDemoSeeder that sets `location`, and (3) ensuring `users.role` enum includes the four academic roles.

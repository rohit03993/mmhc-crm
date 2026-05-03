# MMHC CRM - Full Flow and Robustness Review

Date: 2026-04-27  
Scope: Full system walkthrough across modules, business flows, and production hardening priorities.

**Incremental delivery:** For sprint-sized parts, DoD, and test order, use `docs/DELIVERY-AND-TESTING-BREAKDOWN.md`.

## 1) What this CRM currently does

This is a modular Laravel CRM that manages:
- User onboarding and authentication (admin, nurse, caregiver, patient, academic roles).
- Patient service booking and staff assignment/execution.
- Subscription sales and verification.
- Referral tracking (staff and subscription referral paths).
- Incentive calculation (service, subscription, referral) with rule sets and ledger.
- Staff payout aggregation and settlement.
- Patient reward workflows.
- **Academics** (nursing/college training): institutions, batches, subjects, topics, assignments, student submissions, attendance, and role-based reports (SPI/FPI/ICR), all under `/academics` with the same login as the main CRM.
- Community features integrated into the same user identity model.

Core architecture style:
- Routes -> Controllers -> Services -> Models -> Blade views.
- Financial writes mostly use transactions and row locks.
- Incentive domain is moving from legacy direct-table fields to ledger-first accounting.

## 2) Module-wise architecture map

### Core bootstrapping
- `bootstrap/app.php`: middleware aliases (`role`) and application boot setup.
- `bootstrap/providers.php`: provider registration, including module providers.
- `app/Providers/ModuleServiceProvider.php`: auto-discovers module providers/routes/views/migrations.
- `routes/web.php`: global routes plus additional manual module route wiring.

### Primary modules and responsibilities
- `Auth`: login/register/OTP/dashboard redirect.
- `Profiles`: profile and document management.
- `Plans`: plan catalog, subscriptions, payment verification.
- `Services`: booking lifecycle, staff dashboard, request states.
- `Payments`: admin payout queue, payment creation, settlement, history.
- `Referrals`: referral links, referral completion tracking, stats.
- `Rewards`: caregiver/patient reward records and conversion logic.
- `Incentives`: rule sets, slabs, rate tables, ledger, preview.
- `Community`: feed, comments, reactions, notifications.
- `Academics`: multi-tenant academic LMS slice — institutions, batches, faculty/student membership, subjects, topics, assignments, submissions, attendance, scored dashboards, exports.

### 2.1) Academics — functional tour (how it works in the product)

**Entry and roles**  
- All academics URLs live under **`/academics`** (`app/Modules/Academics/Routes/web.php`), behind `web` + `auth`.  
- Access is split by **`role` middleware**: `super_admin`, `institution_admin`, `faculty`, `student`.  
- Users are the same **`users`** table as the rest of the CRM; academics adds **`academic_institution_id`** (and batch/subject links via pivots) so a person can be healthcare staff and/or academic role depending on how accounts are provisioned.

**Hierarchy (what gets created, in order)**  
1. **Institution** — `super_admin` only: CRUD institutions (`InstitutionController`). This is the tenant root.  
2. **Batch** — `institution_admin`: cohorts inside one institution; students and faculty are attached via **`academic_batch_users`** (`BatchController`, including `updateAssignments`).  
3. **Faculty users** — `institution_admin`: invite/add faculty tied to the institution (`FacultyController`).  
4. **Subject** — per batch; faculty assigned on subject (`SubjectController`, `updateFaculty`).  
5. **Topic** — under a subject; `institution_admin` or `faculty` (`TopicController`). Topics carry **`is_completed`** used for performance indices.  
6. **Assignment** — linked to a topic; deadlines and file attachments (`AssignmentController`); faculty/admin can list submissions per assignment (`SubmissionController::forAssignment`).  
7. **Submission** — `student` only: **My Assignments** list, upload file (10MB max), optional notes; resubmission replaces file (`SubmissionController`). After each save, **`TopicCompletionService::checkAndCompleteTopic()`** runs: if the share of **eligible students who submitted** reaches **`config('academics.completion_threshold')`** (default **70%**), the **topic** is marked completed — this drives FPI/ICR.

**Scores shown on the academics dashboard** (`AcademicsDashboardController` + `AcademicScoreService`)  
- **SPI (Student)** — % of eligible assignments the student has submitted.  
- **FPI (Faculty)** — % of topics (in subjects where they are assigned) marked **`is_completed`**.  
- **ICR (Institution)** — % of topics completed across all subjects in that institution; **super_admin** sees per-institution ICR on the dashboard.

**Attendance**  
- `institution_admin` / `faculty`: list and **mark** attendance (`AttendanceController`: `index`, `mark`, `store`).  
- `student`: **My attendance** (`attendance.my`).

**Reports**  
- `super_admin`, `institution_admin`, `faculty`: report index, detail, download (`ReportController`: `index`, `show`, `download`).  
- **Per-student report** (`reports.student`): attendance over a period (`this_month` / `last_month` / `all`), batch/institution context, assignment/submission summary — with strict checks so institution admins only see their institution’s students and faculty only students in shared batches.

**Important files (quick map)**  
- Routes: `app/Modules/Academics/Routes/web.php`  
- Dashboard: `app/Modules/Academics/Controllers/AcademicsDashboardController.php`  
- Scores: `app/Modules/Academics/Services/AcademicScoreService.php`  
- Topic auto-complete: `app/Modules/Academics/Services/TopicCompletionService.php`  
- Student workflow: `app/Modules/Academics/Controllers/SubmissionController.php`  
- Reports: `app/Modules/Academics/Controllers/ReportController.php`  
- Views: `app/Modules/Academics/Views/**`  
- Migrations: `app/Modules/Academics/Database/Migrations/2026_03_05_*.php`  

**Deeper product narrative (diagrams and flows)**  
- See also: `docs/academics-functional-flows.md` and related `docs/academics-*.md` for stakeholder-level flow and planned phases.

**How academics fits the “whole CRM”**  
- It does **not** currently tie into incentives or staff payouts; it is a **parallel product area** on shared auth/users. Robustness work for payouts does not automatically cover academics — schedule **separate** tests (submission eligibility, report authorization, attendance date ranges, topic completion threshold edge cases).

## 3) End-to-end business flow (practical sequence)

1. Staff user registers (optionally via referral code).
2. Referral service records/updates referral and syncs incentive ledger for staff referral payout events.
3. Patient books service (direct or assigned path), creating service request records.
4. Staff progresses request state (`assigned` -> `in_progress` -> `completed`).
5. Admin approves service payment; incentive engine computes final payout and writes ledger snapshot.
6. Patient buys subscription; on verification, subscription referral commission is computed and ledgered.
7. Payment module aggregates pending amounts from service/reward/referral/subscription sources.
8. Admin settles payout; system marks source rows and ledger rows as paid.
9. Staff/admin dashboards read totals and detailed histories (including paginated incentive detail tabs).

## 4) Incentive model (current behavior)

Main engine:
- `app/Modules/Incentives/Services/IncentiveCalculatorService.php`

Configuration tables:
- `incentive_rule_sets`
- `incentive_growth_dta_slabs`
- `incentive_service_rates`
- `incentive_subscription_rates`

Runtime accounting table:
- `incentive_ledger`

Current design intent:
- Ledger should be primary source for payable incentive events.
- Legacy fields still exist for compatibility and transitional reads/writes.

## 5) What is strong already

- Good use of `DB::transaction()` and `lockForUpdate()` in payment-critical paths.
- Idempotent patterns (`updateOrCreate`) in many seeders and ledger writes.
- Ledger uniqueness on (`source_type`, `source_id`) to reduce duplicate event creation.
- Global pagination standardization with custom modern paginator views.
- Role middleware and role helper methods are in place.

## 6) Main risks / fragility areas

1. **Dual source-of-truth in financial domain**  
   Ledger + legacy fields are both used in some reads, increasing mismatch risk.

2. **Route duplication and drift risk**  
   Some module routes are defined in module files and also wired manually in `routes/web.php`.

3. **Migration history risk**  
   Duplicate/destructive migration patterns can produce environment drift.

4. **Missing DB-level invariants in some workflows**  
   Example: daily service uniqueness expected by code but not fully enforced by database constraints.

5. **Referral code concurrency edge cases**  
   Referral generation/consumption can still see race windows under concurrency.

6. **Large controllers with mixed responsibilities**  
   Increases bug surface and makes behavior hard to test and maintain.

## 7) Practical roadmap to make system robust and advanced

## Phase A - Safety and consistency first (highest priority)

1. Make ledger the single source for incentive settlement reads (after backfill window).
2. Add reconciliation job to detect mismatch between ledger, source rows, and `staff_payments`.
3. Add or tighten indexes for payout and dashboard hot queries.
4. Add missing unique constraints where code assumes uniqueness.
5. Remove route duplication; keep one canonical registration path.

## Phase B - Reliability and maintainability

1. Extract heavy controller business logic into domain services/actions.
2. Introduce FormRequest classes for consistent validation and cleaner controllers.
3. Standardize state transitions as explicit state machine guards for service/subscription/payment statuses.
4. Add idempotency keys for externally retried operations (payment callbacks/imports).

## Phase C - Advanced capability

1. Add observability dashboard (daily payout accuracy, pending aging, failed reconciliation, ledger drift).
2. Add rule-set version governance (publish-only new versions, immutable historical rules).
3. Build event/audit timeline per financial object (service request, subscription, payout).
4. Add proactive anomaly alerts (sudden payout spikes, invalid state combinations, orphan records).

## 8) Data-safe execution strategy (no data loss)

- Use additive migrations first (new columns/indexes/tables), no destructive drops.
- Backfill in chunks with idempotent scripts and clear checkpoints.
- Keep compatibility reads during transition; remove legacy reads only after reconciliation passes.
- Release with feature flags where behavior changes are significant.
- Take validated backup before each schema-affecting release.

## 9) Suggested immediate next 10 actions

1. Freeze and document canonical route ownership (module vs root).
2. Implement ledger-first read path for all incentive totals.
3. Add daily reconciliation command and report surface.
4. Add missing unique/index constraints with pre-clean scripts.
5. Refactor `AdminPaymentController` into service-layer commands.
6. Refactor `SubscriptionController` heavy logic into dedicated services.
7. Introduce FormRequest validation for top financial and state-changing endpoints.
8. Add transition tests for service/subscription/referral settlement flows.
9. Add deadlock-safe retry wrapper for payout settlement transaction blocks.
10. Plan deprecation schedule for legacy payout columns after reconciliation stability.

## 10) Short conclusion

The CRM already has a strong functional foundation and a working incentive-ledger architecture.  
The **Academics** slice is a full LMS-style workflow (institution → batch → subject → topic → assignment → submission) with SPI/FPI/ICR metrics, attendance, and scoped reports — documented in detail under **section 2.1** above and in `docs/academics-functional-flows.md`.  
To make it production-grade robust, the key is to finish the ledger transition, enforce database invariants, reduce route/controller drift, and add reconciliation + observability around payouts; for academics, add focused authorization tests and config-driven completion rules validation.

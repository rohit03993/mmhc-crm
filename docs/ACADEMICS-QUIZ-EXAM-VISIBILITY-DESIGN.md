# Quiz / exam module — visibility & access (design)

**Audience:** Engineers + academic admins.  
**Status:** Design only — implement after review.  
**Grounded in:** Current CRM schema (`academic_batches`, `academic_subjects`, `academic_subject_faculty`, `academic_batch_users`, `users.academic_institution_id`, Community feed).

---

## 1. Academic vocabulary ↔ data model

| Colloquial (PDF / faculty) | In MMHC CRM | Notes |
|----------------------------|-------------|--------|
| Class / cohort | `Batch` | e.g. “B.Sc Nursing 2024-25”; belongs to one `Institution`. |
| Course paper / teaching unit | `Subject` | Belongs to exactly one `Batch`. Faculty assigned per subject. |
| Teacher “assigned to class” | `academic_subject_faculty` + often `academic_batch_users` (type `faculty`) | A faculty may be on the batch **and** on specific subjects they teach. |
| Students in that class | `batch->students()` via `academic_batch_users` (type `student`) | Same batch usually takes the same subject list (typical Indian nursing programme shape). |

**Implication:** “Only students whose class is taught by this teacher” is **not** one vague rule — it must be one of:

1. **Subject cohort** — students enrolled in the **batch of that subject**, and the teacher is on **`subject.faculty`** (strongest match to “my Anatomy class”).
2. **Whole batch** — all students in a **batch**, and the teacher is on **`batch.faculty`** (e.g. class test across papers).
3. **Institution** — all **students** under the same **institution** (open mock, inter-batch).
4. **Community / platform** — broader discovery (policy-heavy; see §5).

---

## 2. Core entities (proposed)

Keep **Exam** (or `AcademicQuiz`) as its own aggregate — do not overload `Assignment` for MCQ banks in v1 if you want clean reporting; optionally **link** an exam to a topic/assignment for gradebook later.

| Entity | Purpose |
|--------|---------|
| `academic_exams` | Metadata: title, instructions, time limit, window (`opens_at`, `closes_at`), shuffle flags, max attempts, **scope** (below), owning `institution_id`, `created_by`. |
| `academic_exam_questions` | Question text, type (MCQ single/multi), order, marks, optional explanation. |
| `academic_exam_options` | Options, `is_correct` (for auto-mark). |
| `academic_exam_attempts` | `exam_id`, `user_id`, started/finished, score, snapshot if needed. |
| `academic_exam_attempt_answers` | Per-question response. |

**Scope fields on `academic_exams` (single source of truth):**

- `audience_type` — enum (see §3).
- `subject_id` — nullable; required when `audience_type = subject_cohort`.
- `batch_id` — nullable; required when `audience_type = batch` (and must match `subject.batch_id` if `subject_id` also set for validation).
- `institution_id` — required for all exams (owner); for `institution_open` the audience is “all students with this institution”.

Optional later: `topic_id` / `assignment_id` for LMS integration.

---

## 3. `audience_type` — rules and who may create

| Value | Who may take it (student) | Who may create / publish |
|--------|---------------------------|---------------------------|
| `subject_cohort` | Users with role `student` who appear in `academic_batch_users` for **`subjects.batch_id`** with `type = student`. | Faculty who are on **`subject.faculty`** for that `subject_id`. **Institution_admin** for that institution may override (mock tests, coordinator). |
| `batch` | All `student` users in that `batch_id`. | Faculty on **`batch.faculty()`** **or** institution_admin of the batch’s institution. |
| `institution_open` | All users with `role = student` and `academic_institution_id = exam.institution_id`. | Institution_admin **or** faculty whose `academic_institution_id` matches (policy: faculty may need extra permission flag later). |
| `community` | See §5 — not the same as “student in batch”. | Same as publisher rules for the **underlying** scope (usually start as `institution_open` + “listed in community”) **or** super_admin-only for true cross-institution (discouraged default). |

**Enforcement (single function, e.g. `ExamPolicy::canTake($user, $exam)`):**

1. User must be authenticated, `is_active`, and allowed role (typically `student` for attempts; faculty/admin for preview).
2. Exam must be within `opens_at` / `closes_at` and `published`.
3. Resolve cohort by `audience_type` using queries above — **no duplicated allow-lists** unless you add optional `academic_exam_invites` for exceptions.

**Co-teachers:** Any user on `subject.faculty` may edit/publish exams scoped to that subject (v1). Optional: add `exam_owner_id` vs `exam_editors` later.

---

## 4. Faculty workflow (academic + developer view)

1. **Create exam** — choose **audience_type** first (UI drives allowed fields).
   - Subject test → pick **Subject** (batch implied; show batch name).
   - Batch test → pick **Batch**.
   - Institution mock → no cohort picker; institution from user.
2. **Build questions** — CRUD MCQ (v1); version freeze when attempts exist (or clone-to-new-version).
3. **Publish** — sets `published_at`; optional schedule.
4. **Results** — attempts list filtered to **same scope** faculty can see (subject faculty see only their subject’s exams; institution_admin sees institution).

**Integrity (minimum viable):**

- `max_attempts` per user per exam.
- Optional: shuffle questions / options.
- Late window: hard block after `closes_at`.
- Audit: who created, who published (for disputes).

---

## 5. “Community” visibility — separate concern from cohort

Today **Community** is a **global feed** (`community_posts`) with no `institution_id`. Running a quiz “in community” can mean:

| Mode | Meaning | Implementation sketch |
|------|---------|-------------------------|
| **A. Discoverability only** | Exam still graded under `institution_open` or `subject_cohort`; community only **links** to it. | New post type or rich text with URL to `/academics/exams/{id}`; exam policy unchanged. |
| **B. Institution community** (recommended later) | Posts scoped to institution — only users from that college see the share. | Extend `community_posts` with nullable `institution_id` + filter feed; exam scope stays `institution_open` for that college. |
| **C. Truly public / cross-institution** | Any logged-in user (or wider) can attempt. | `audience_type = community` + explicit `allows_cross_institution` flag; **only super_admin** or central content team; legal/academic sign-off. |

**Recommendation for v1:** Implement **A** (link from community) + exam scopes **subject_cohort**, **batch**, **institution_open**. Defer **C** until product/legal clarity.

---

## 6. Edge cases checklist

| Scenario | Behaviour |
|----------|-----------|
| Student in two batches | Subject exam uses **that subject’s batch**; eligibility is membership in **that** batch only. |
| Faculty not on subject tries to attach exam to subject | Deny at authorization. |
| Institution admin creates subject exam | Allow if batch/subject belongs to their institution; optionally log as “admin on behalf”. |
| Student transfers batch mid-term | Eligibility is **current** pivot rows; old attempts remain tied to user. |
| Super_admin | Full read; create only if you add explicit policy (default: read + support, not teaching). |
| CRM `admin` without `academic_institution_id` | Treat as non-teaching; no exam creation unless you extend roles. |

---

## 7. API / UI surfaces (conceptual)

- **Faculty:** “My exams” = exams where `created_by = me` OR I’m on `subject.faculty` / `batch.faculty` for scoped exams.
- **Student:** “Available exams” = `canTake` for published exams in window; group by subject/batch/institution.
- **Reports:** Export by subject, batch, institution; align with existing academics reports module.

---

## 8. Order of implementation (after this design is approved)

### Sprint 1 (done in repo)

- Migrations: `academic_exams`, `academic_exam_questions`, `academic_exam_options`, `academic_exam_attempts`, `academic_exam_attempt_answers`.
- Eloquent models: `AcademicExam`, `AcademicExamQuestion`, `AcademicExamOption`, `AcademicExamAttempt`, `AcademicExamAttemptAnswer`.
- `ExamAccessService`: `canTake` / `canManage` for `subject_cohort`, `batch`, `institution_open`, `community` (see code).
- `ExamController@index` + `academics::exams.index` — list exams filtered by role (manage vs take).
- Feature tests: `tests/Feature/ExamAccessServiceTest.php` (requires `pdo_sqlite` for default `phpunit.xml` in-memory DB, or run tests with MySQL test DB).

### Next sprints

1. **Exam CRUD** (create/edit/publish) with validation rules per `audience_type` (`subject_id` / `batch_id` / institution-only).
2. **Question & option** editor (MCQ single-answer first) + reorder.
3. **Student attempt** flow: start attempt, answer, submit, compute score, enforce `max_attempts` and schedule.
4. **Reporting** hooks (per exam / per batch) and optional community **link/share** (mode A).
5. Optional: institution-scoped community posts (mode B); cross-institution (mode C) last.

---

## 9. Related documents

- [`ACADEMICS-VISION-AND-ROADMAP.md`](./ACADEMICS-VISION-AND-ROADMAP.md) — Phase B (quiz / MCQ) fits here.

---

## Document history

| Date | Change |
|------|--------|
| 2026-05-03 | Initial design: subject / batch / institution / community modes aligned to current schema |

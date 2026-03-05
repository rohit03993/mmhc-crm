# MeD Miracle Academic CRM — Functional Flows & Dependencies

This document describes **how each part of the system works** and **how it connects to other parts**, from a user and business perspective (not technical implementation). Use it to review, give inputs, and align before we build anything.

---

## 1. How Everything Depends on Each Other (Big Picture)

```
                    ┌─────────────────┐
                    │   INSTITUTION   │  ← Super Admin creates; everything else sits under it
                    └────────┬────────┘
                             │
         ┌───────────────────┼───────────────────┐
         │                   │                   │
         ▼                   ▼                   ▼
   ┌──────────┐        ┌──────────┐        ┌──────────┐
   │  BATCH   │        │  USERS   │        │ DEPT /   │
   │          │        │(Faculty, │        │ ACAD YR  │
   │ Students │        │ Students,│        │ (opt)    │
   │ Faculty  │        │  Admin)  │        └──────────┘
   └────┬─────┘        └────┬─────┘
        │                   │
        │    ┌──────────────┘
        │    │
        ▼    ▼
   ┌─────────────────┐
   │    SUBJECT      │  ← Belongs to Batch; has assigned Faculty
   └────────┬────────┘
            │
            ▼
   ┌─────────────────┐
   │     TOPIC       │  ← Belongs to Subject; has completion status (pending/done)
   └────────┬────────┘
            │
            ▼
   ┌─────────────────┐
   │  ASSIGNMENT     │  ← Linked to Topic; has deadline, attachments
   └────────┬────────┘
            │
            ▼
   ┌─────────────────┐
   │  SUBMISSION     │  ← Student submits per Assignment; drives auto-completion
   └────────┬────────┘
            │
            ▼
   ┌─────────────────┐
   │ AUTO-COMPLETE   │  ← When X% students submit → Topic done → Faculty progress up
   │ PROGRESS/SCORES │  ← SPI, FPI, ICR calculated from above
   │ DASHBOARDS      │  ← Show the above in role-based views
   │ REPORTS         │  ← Export the above (PDF/Excel)
   └─────────────────┘
```

**Rule of thumb:** Institution → Batch → Subject → Topic → Assignment → Submission. Each step feeds the next; completion and scores are derived from submission data.

---

## 2. Section-by-Section Functional Flows & Connections

---

### 2.1 INSTITUTION

**What it is:** The college or nursing institution that uses the system (multi-tenant).

**Who does what:**
- **Super Admin:** Creates institutions, edits basic info, (later: subscription/limits).
- **Institution Admin:** Works inside one institution; cannot create other institutions.

**Flow:**
1. Super Admin creates an institution (name, code, maybe contact).
2. Optionally: departments and academic year are set under this institution.
3. All batches, subjects, faculty, and students of that college are tied to this institution.

**How it connects to others:**
- **Batch:** Every batch belongs to one institution.
- **Users:** Faculty and students are linked to an institution (directly or via batch).
- **Admin scope:** Institution Admin sees only data of their institution; Super Admin sees all.
- **Reports/Dashboards:** Institution-level reports (e.g. ICR) are per institution.

**Your input to decide:** Do you need multiple departments per institution in Phase 1, or one institution = one “college” with no department split?

---

### 2.2 USERS & ROLES (and login → academics)

**What it is:** Same users as main CRM; roles decide who can access academics and what they see.

**Who exists:**
- **Super Admin:** Full system; manages institutions.
- **Institution Admin:** One institution; manages batches, faculty, students, subjects.
- **Faculty:** Teaching; creates topics/assignments; sees own progress.
- **Student:** Submits assignments; sees own progress/SPI.

**Flow:**
1. User logs in on **main CRM page** (single login).
2. System checks if user has an **academic role** (and optionally institution access).
3. If yes → redirect to **academics URL** (e.g. `/academics` or academics dashboard).
4. Inside academics, every screen and list is filtered by role and (for Admin/Faculty/Student) by institution/batch/subject as applicable.

**How it connects to others:**
- **Institution:** Admin is “Institution Admin for Institution X”; Faculty/Students belong to an institution (and batches).
- **Batch:** Admin assigns faculty and students to batches; faculty/students see only their batches.
- **Subject:** Admin assigns faculty to subjects; faculty see only their subjects and create topics/assignments there.
- **Topic/Assignment/Submission:** Created and seen according to role and assignment (faculty vs student).
- **Dashboards/Reports:** Each role gets a different dashboard and report set based on these links.

**Your input to decide:** Can one user be both Faculty and Institution Admin (two roles), or one user = one role in academics?

---

### 2.3 BATCH

**What it is:** A group of students (e.g. “2024 Batch”, “Batch A”) in an institution for a given academic period.

**Who does what:**
- **Institution Admin:** Creates batch (name, maybe start/end date), assigns **students** to batch, assigns **faculty** to batch (teaching responsibility for that batch).

**Flow:**
1. Admin creates a batch under an institution.
2. Admin assigns students to the batch (from a list of users who have “Student” role and belong to that institution).
3. Admin assigns faculty to the batch (from users who have “Faculty” role and belong to that institution).
4. Later, subjects are created “for” or “under” that batch (see Subject).

**How it connects to others:**
- **Institution:** Batch belongs to one institution.
- **Users:** Students and faculty are assigned to batches; this defines “who teaches whom” and “who is in which group”.
- **Subject:** Subjects are typically created per batch (e.g. “Anatomy for 2024 Batch”); same subject name can exist in different batches as different records.
- **Topic / Assignment / Submission:** All are under a subject, which is under a batch — so batch indirectly defines which students get which assignments and which faculty track progress.
- **Reports:** “Batch progress report” = how much of the syllabus (topics/assignments) is completed for that batch.

**Your input to decide:** Is one batch = one academic year only, or can a batch span years? Do we need “academic year” as a separate dropdown when creating a batch?

---

### 2.4 SUBJECT

**What it is:** A course/paper (e.g. “Anatomy”, “Nursing Fundamentals”) under a batch, with one or more faculty responsible.

**Who does what:**
- **Institution Admin:** Creates subject (name, maybe code) under a batch; assigns **faculty** to the subject.

**Flow:**
1. Admin selects a batch, then creates a subject for that batch.
2. Admin assigns one or more faculty to the subject (only faculty who are already assigned to that batch make sense).
3. Faculty will later create topics and assignments for this subject.

**How it connects to others:**
- **Batch:** Subject belongs to one batch; the batch’s students are the “audience” for this subject.
- **Faculty:** Assigned to subject; only they can create/edit topics and assignments for this subject.
- **Topic:** Each topic belongs to one subject; faculty create topics here.
- **Assignment:** Linked to topic (and thus to subject and batch); students in the batch who have this subject get the assignment.
- **Progress/Reports:** “Subject completion” = how many topics of this subject are completed (via assignment submission threshold).

**Your input to decide:** Can the same subject (e.g. “Anatomy”) exist in multiple batches as separate records, or do you want one global “Anatomy” shared across batches with only assignment differing?

---

### 2.5 TOPIC

**What it is:** A teaching unit within a subject (e.g. “Cardiovascular System”, “Unit 3 – Ethics”). Completion is **automatic** when enough students submit the linked assignment(s).

**Who does what:**
- **Faculty:** Creates topic under their assigned subject; sees list of topics with status (pending / completed).

**Flow:**
1. Faculty selects a subject (they are assigned to) and creates a topic (name, maybe order/sequence).
2. Topic starts as **pending**.
3. Faculty creates one or more assignments linked to this topic (see Assignment).
4. When the **auto-completion rule** is met (e.g. 70% of eligible students submitted), system marks topic as **completed** (no manual “mark complete” as primary path).
5. Faculty (and admin) see topic status and progress in dashboards/reports.

**How it connects to others:**
- **Subject:** Topic belongs to one subject (and hence one batch).
- **Assignment:** Each assignment is linked to one topic; completion of topic is derived from assignment submission %.
- **Auto-completion:** When submission % for an assignment (or set of assignments for that topic) reaches threshold → topic becomes completed.
- **Faculty progress:** Faculty’s “teaching progress” = % of their topics (across subjects) that are completed.
- **Reports:** “Topic completion report” = which topics are pending/done per subject/batch.

**Your input to decide:** Can one topic have multiple assignments (e.g. “Assignment 1” and “Assignment 2” for same topic), and if yes, is topic completed when (a) any one assignment hits threshold, or (b) all assignments hit threshold, or (c) average of all?

---

### 2.6 ASSIGNMENT

**What it is:** A task created by faculty, linked to a topic, with deadline and optional attachments; students submit work against it.

**Who does what:**
- **Faculty:** Creates assignment (title, description, deadline, optional file attachments) and links it to a topic. Only for subjects they are assigned to.

**Flow:**
1. Faculty selects subject and topic, then creates assignment with deadline and materials.
2. System considers “eligible students” = students in the batch that has this subject (and possibly enrolled in that subject if you add enrollment later).
3. Students see this assignment in their list (see Submission).
4. Submission count / percentage is calculated from eligible students; when it crosses threshold, topic is marked completed (see Auto-completion).

**How it connects to others:**
- **Topic:** Assignment is linked to one topic; topic completion depends on assignment submission %.
- **Subject & Batch:** Via topic → subject → batch; defines which students see the assignment.
- **Submission:** Each student submission is “for” one assignment; one student = one submission per assignment (or you allow resubmission — to be decided).
- **Auto-completion:** Uses “number of submissions / eligible students” to get % and compare with threshold.
- **Reports:** “Student assignment report” = who submitted what and when; “Faculty performance” can include assignment creation and completion rates.

**Your input to decide:** One submission per student per assignment, or allow resubmission (and if so, which submission counts for completion — latest only)? Are “eligible students” = all students in the batch, or only those “enrolled” in the subject (if you have enrollment)?

---

### 2.7 STUDENT SUBMISSION

**What it is:** Student uploads their work (file/response) for an assignment; system records it and uses it for completion % and scores.

**Who does what:**
- **Student:** Sees list of assignments (from their batch’s subjects), uploads submission before/after deadline, sees status (submitted / pending / late).

**Flow:**
1. Student logs in (main page) → redirected to academics.
2. Sees “My assignments” (filtered by batch and subjects of that batch).
3. Clicks an assignment → uploads file(s) and/or text → submits.
4. System records submission (time, file); status shows as “Submitted” (and optionally “Late” if past deadline).
5. This submission is counted in the assignment’s completion % → feeds auto-completion and scores.

**How it connects to others:**
- **Assignment:** Submission is for one assignment; assignment is linked to topic → subject → batch.
- **Auto-completion:** Each submission increases “submitted count”; when (submitted / eligible students) ≥ threshold → topic completed.
- **Faculty progress:** Topic completion updates faculty progress (FPI / teaching completion).
- **SPI (Student):** Student’s score (SPI) is based on their submission rate (and later attendance/clinical if you add).
- **Reports:** “Student submission report” = per student, which assignments submitted/pending; “Faculty performance” and “Batch progress” depend on this data.

**Your input to decide:** Do you want “draft” (save without submitting) or only “submit”? Should late submissions still count for topic completion and SPI, or be marked but weighted differently?

---

### 2.8 AUTO-COMPLETION & THRESHOLD

**What it is:** Business rule: when a defined percentage of eligible students have submitted an assignment, the linked topic is automatically marked **completed** and faculty progress is updated.

**Who does what:**
- **System:** Calculates submission %; when ≥ threshold, marks topic completed and recalculates faculty progress. Optionally **Super Admin** can set default threshold (e.g. 70%) per institution or globally.

**Flow:**
1. Eligible students = students in the batch that has this subject (and assignment’s topic).
2. For each assignment: submission % = (number of students who submitted / eligible students) × 100.
3. When submission % ≥ threshold (e.g. 70%):
   - Mark the **topic** as **completed** (and do not revert if someone withdraws — unless you define that rule).
   - Recalculate **faculty progress** (e.g. % of topics completed for that faculty in that subject/batch).
4. Dashboards and reports read this “completed” status and updated progress.

**How it connects to others:**
- **Topic:** Status changes from pending → completed.
- **Assignment & Submission:** Source of the count (submissions) and denominator (eligible students).
- **Faculty progress / FPI:** Driven by “how many topics are completed” for the faculty’s subjects.
- **Batch/Institution:** Batch progress = how many topics are completed in that batch; ICR can aggregate this.
- **Reports:** Topic completion report, faculty performance, batch progress all use this.

**Your input to decide:** Threshold same for all (e.g. 70%) or configurable per institution/subject/assignment? If one topic has multiple assignments, confirm the rule (any one / all / average) as in Topic section.

---

### 2.9 PROGRESS & SCORING (SPI, FPI, ICR)

**What it is:** Three indices that summarize performance; all **derived** from the data above (no separate data entry).

**Definitions (functional):**
- **SPI (Student Professional Index):** Student’s score — from assignment completion (and later attendance/clinical participation if you add).
- **FPI (Faculty Performance Index):** Faculty’s score — from syllabus/topic coverage (topics completed) and student performance in their subjects.
- **ICR (Institution Clinical Readiness):** Institution’s score — from aggregate student and faculty performance (and later compliance/engagement if you add).

**Flow:**
1. As submissions and auto-completion run, the system has: per-student submission counts, per-topic completed status, per-faculty completed topics.
2. SPI: e.g. (assignments submitted / total assigned) or weighted by subject (you define formula).
3. FPI: e.g. % of faculty’s topics completed, maybe adjusted by student submission rate in their subjects.
4. ICR: e.g. average of batch progress or weighted SPI/FPI across the institution.
5. These values are shown on dashboards and in reports; they update when submission or completion changes.

**How it connects to others:**
- **Submission:** Feeds SPI (and completion %).
- **Topic completion:** Feeds FPI and batch progress.
- **Batch/Institution:** FPI per faculty, SPI per student, ICR per institution; all tie to institution/batch for filtering.
- **Dashboards/Reports:** Main content of role-based dashboards and export reports.

**Your input to decide:** Exact formula for SPI/FPI/ICR (simple % vs weighted); whether Phase 1 has only “assignment completion” or you want placeholder for future attendance/clinical.

---

### 2.10 DASHBOARDS

**What it is:** Role-based summary screens that show the right metrics for each user, using the same underlying data (institutions, batches, subjects, topics, assignments, submissions, completion, SPI/FPI/ICR).

**Who sees what (functionally):**
- **Student:** My assignments (pending/submitted), my progress, my SPI (and portfolio if you add).
- **Faculty:** My subjects and topics (pending/completed), my assignments, pending validations (if you add approval), my FPI / teaching progress.
- **Institution Admin:** Batch-wise progress, faculty performance (FPI), student submission overview, compliance/engagement summary.
- **Super Admin:** List of institutions, institution-wise ICR/progress, comparisons, system-wide analytics.

**Flow:**
1. User lands on academics (after login redirect).
2. Default page = dashboard for their role.
3. Every number and list on the dashboard comes from: institutions → batches → subjects → topics → assignments → submissions → completion and scores.
4. Clicking a card/row can drill down (e.g. batch → subjects → topics, or faculty → subjects → completion %).

**How it connects to others:**
- **All previous sections:** Dashboards are the “view” layer on top of institutions, batches, subjects, topics, assignments, submissions, auto-completion, and SPI/FPI/ICR. No new data; only filters and grouping by role.

**Your input to decide:** Which 3–5 metrics are must-have on each role’s dashboard in Phase 1 (e.g. Student: “Assignments due”, “Submitted”, “My SPI”; Faculty: “Topics pending”, “Topics completed”, “My FPI”).

---

### 2.11 REPORTS

**What it is:** Exportable (PDF/Excel) reports that answer fixed questions, using the same data as dashboards.

**Planned reports (functional):**
- **Faculty performance:** Per faculty, subjects, topics completed, assignment completion rates, FPI.
- **Topic completion:** Per batch/subject, which topics are pending/completed and when.
- **Student assignment/submission:** Per student (or per batch), which assignments submitted/pending/late.
- **Batch progress:** Per batch, overall syllabus completion (e.g. % topics completed), maybe SPI summary.

**Flow:**
1. Admin (or Management) chooses report type and filters (institution, batch, date range, etc.).
2. System runs the same logic as dashboards but for the selected scope and outputs table/summary.
3. User exports as PDF or Excel.

**How it connects to others:**
- Same data as dashboards; reports are a different “view” (filtered, exportable). No new entities; only filters and export format.

**Your input to decide:** Which report is highest priority for Day 1 (e.g. “Faculty performance” or “Batch progress”), and any extra columns you want (e.g. “Submitted on” date, “Late” flag).

---

## 3. Order of Building (Dependency Order)

We will build in this order so that each section has what it needs:

1. **Institution** (and optional Department/Academic year)
2. **Users & Roles** (academic roles + login redirect to academics URL)
3. **Batch** (create batch, assign students and faculty to batch)
4. **Subject** (create under batch, assign faculty to subject)
5. **Topic** (create under subject; status pending → completed by rule)
6. **Assignment** (create under topic; deadline, attachments)
7. **Submission** (student submits; count and status)
8. **Auto-completion** (threshold → topic completed → faculty progress)
9. **Progress & scoring** (SPI, FPI, ICR formulas)
10. **Dashboards** (per role)
11. **Reports** (PDF/Excel)

---

## 4. Your Inputs We Need (Summary)

- **Institution:** Departments in Phase 1 or not?
- **Users/Roles:** One user = one role or multiple roles (e.g. Faculty + Admin)?
- **Batch:** One batch = one academic year? Need “academic year” field?
- **Subject:** Same subject name in multiple batches = separate records or one shared?
- **Topic:** Multiple assignments per topic? Rule: any one / all / average for completion?
- **Assignment:** One submission per student or resubmission? Who are “eligible students”?
- **Submission:** Draft vs submit only? Do late submissions count fully for completion and SPI?
- **Auto-completion:** Threshold global or configurable? Rule when topic has multiple assignments?
- **SPI/FPI/ICR:** Simple formula for Phase 1 (e.g. % only) or specific weights?
- **Dashboards:** Top 3–5 metrics per role for Phase 1?
- **Reports:** Which report first and any must-have columns?

Once you confirm or adjust these, we can keep this doc as the single “functional spec” and build section by section, with flow and connections already agreed.

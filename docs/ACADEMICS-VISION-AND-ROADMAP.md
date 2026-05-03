# Academics vision & roadmap

**Source:** stakeholder PDF `Acadmics page _260503_202851.pdf` (teaching methods, clinical skills, assessment, internships, community, research, engagement).  
**Purpose:** Single reference so scope and phases are not lost. Update this file when priorities change.

---

## 1. Vision themes (from PDF)

| Theme | Examples |
|--------|-----------|
| Teaching formats | Micro-teaching (basic/advanced), demonstration, presentation, bedside, role play, peer teaching, skill simulation, clinical instructor, lab demo, assignment presentation, care plan, seminar, workshop (physical/online), community role play, individual education, field visit, health talk, health teaching/awareness |
| Assessment / evaluation | Rating, MCQ / test on presentation, OSCE, practical, formative, quiz, viva, checklist, summative (written) |
| Clinical / skills | OSCE practice, procedure library (video + checklist), drug calculation zone, case simulations, emergency drills (Code Blue, CPR) |
| Smart learning | Weekly quiz / mock tests, leaderboard, peer feedback, mentor rating, AI-based weak-topic hints |
| Clinical exposure | Hospital internship listings, digital logbook, live case discussion, placement & interview prep |
| Live learning | Webinars, panel discussions, student-led teaching, recorded session library |
| Community / public health | Health campaign creator, field activity upload (proof + impact), awareness drive tracking |
| Research / writing | Assignment builder (templates), plagiarism integration, citation guide (Vancouver/APA), project submission portal |
| Profile / branding | Digital portfolio, nursing-specific resume builder, badges, verified mentor tag |

---

## 2. Current product baseline (MMHC CRM academics)

Already in scope today (high level):

- Organisational: institutions, batches, subjects, topics, assignments, file submissions, attendance, role-based dashboards, documents surfacing, reports (including SPI/FPI/ICR-style metrics where implemented).

**Not** yet covered by that baseline: typed teaching methods, OSCE/quiz engines, procedure video library, internships/logbooks, community campaigns, research workflow, plagiarism, live-session hub, leaderboards/badges, AI hints.

---

## 3. Gap summary

| PDF area | In CRM today | To build |
|----------|----------------|----------|
| Curriculum / activity labelling | Topics / assignments | Taxonomy: teaching + assessment types on topics/assignments |
| Homework | File submission | Assignment **types** (upload, quiz, checklist, care plan, etc.) |
| Grading | Basic submission | Rubrics, checklists, viva notes, OSCE stations (phased) |
| Quizzes / MCQ | — | Question bank, attempts, scoring |
| Procedures / video | — | Resource library (links/uploads + checklists) |
| Internship / logbook | — | Placements, entries, supervisor sign-off |
| Live / recordings | — | Session model (links + metadata + library listing) |
| Community | — | Campaigns, field proof uploads, simple tracking |
| Research | — | Milestones, templates, citations; plagiarism later |
| Engagement | — | Points, badges, leaderboard, peer/mentor ratings |
| AI | — | Rule-based weak topics first; optional ML later |

---

## 4. Phased roadmap (execution order)

### Phase A — Foundation (align PDF lists with existing module)

**Goal:** Same screens, richer semantics; minimal new infrastructure.

1. **Activity / method taxonomy** — Seed or config list matching PDF teaching + evaluation labels; attach to topic and/or assignment (`teaching_method`, `assessment_type`, multi-select if needed).
2. **Assignment types** — e.g. `file_upload`, `quiz`, `presentation`, `care_plan`, `checklist`, etc.; student/faculty UI reflects type (v1 can still be upload + notes where needed).
3. **Evaluation metadata** — Store formative/summative, MCQ, practical, viva, checklist flags on assignments for reporting.
4. **Reports** — Filters by activity/assessment type.

**Exit:** Everything on PDF pages 1–2 is **selectable and reportable**.

---

### Phase B — Structured assessment (OSCE, checklist, quiz)

**Quiz / exam visibility (subject vs batch vs institution vs community)** is specified in [`ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md`](./ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md) — read before implementation.

1. **Checklist assessments** — Faculty-defined items; scored/ticked; rollup.
2. **Quiz / MCQ (MVP)** — Per-subject question bank; timed attempts; auto-mark; tie to assignments.
3. **OSCE (MVP)** — Stations, schedules (as needed), checklist per station, timers; no full proctoring in v1.
4. **Procedure library (light)** — Topic-linked resources: video URL or file + PDF checklist.

**Exit:** Skills-oriented day runnable on platform (stations + checklists + optional quiz).

---

### Phase C — Clinical exposure & logbook

1. Placement records (sites, student assignment, dates).
2. Digital logbook (entries, hours, activity type; supervisor approval).
3. Placement/interview prep content (static/CMS first).

**Exit:** Single place for **where**, **what**, **sign-off**.

---

### Phase D — Community & public health

1. Campaign / drive entity (institution-scoped).
2. Field activity submission (proof uploads, narrative, optional date/geo).
3. Simple dashboards (counts by batch/campaign).

**Exit:** Assign community work; collect and review proof.

---

### Phase E — Research & writing

1. Project workspace with milestones (proposal → draft → final).
2. Templates + static citation guides (APA/Vancouver).
3. Plagiarism — **after** stable uploads; integrate one vendor/API.

**Exit:** Project portal + versioning; plagiarism when approved.

---

### Phase F — Live & recorded hub

1. **Session** model: external webinar link, schedule, speakers, recording URL.
2. **Library** listing with tags (topic, batch).
3. Optional: watch progress if player supports it.

**Exit:** v1 is **metadata + links**, not necessarily self-hosted video CDN.

---

### Phase G — Engagement

1. Points (on-time submission, quiz, attendance — rules TBD).
2. Leaderboard (per batch/institution; privacy controls).
3. Badges (rule engine; depends on Phase B+ signals).
4. Peer feedback on specific activities.
5. Mentor/session ratings.

**Exit:** Motivation layer without blocking core grading.

---

### Phase H — AI / advanced analytics

1. Aggregate weak signals (wrong quiz answers, missing work, low checklist scores).
2. v1: **Rule-based** “focus on topic X”.
3. v2: optional ML or external API.

---

## 5. Cross-cutting requirements

- **RBAC:** Faculty, institution admin, student (and super/admin) for each new entity.
- **Audit:** Logbook approvals, grade changes, sign-offs.
- **Mobile-friendly:** Logbook and field uploads.
- **Demos:** Extend academic seeders with sample taxonomy, one OSCE-ish flow, one community flow when those phases exist.

---

## 6. Open questions (resolve before deep build on B+)

- Which **3–5** teaching methods are mandatory in year one?
- OSCE: documentation only vs full room/time scheduling?
- Logbook: digital signature legal requirements (draw vs upload scan)?
- Video: YouTube unlisted, Zoom only, or self-hosted?
- Budget for plagiarism API?

---

## 7. Document history

| Date | Change |
|------|--------|
| 2026-05-03 | Initial roadmap from PDF + CRM gap analysis |

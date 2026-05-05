# Academics vision & roadmap

**Source:** stakeholder PDF `Acadmics page _260503_202851.pdf` (teaching methods, clinical skills, assessment, and related themes).  
**Product scope:** **Phase A and Phase B only.** OSCE workflows, clinical logbooks, internships, community campaigns, research portals, live-session hubs, gamification, and AI analytics are **out of scope** for this codebase unless priorities change.

---

## 1. Vision themes (PDF reference — not all are built)

The PDF spans teaching formats, assessments, skills practice, quizzes, community work, research, engagement, etc. This CRM implements the subset captured in **§4 Phase A and Phase B** below. Broader themes (logbooks, campaigns, plagiarism, leaderboards, etc.) remain **documentation-only** here.

---

## 2. Current product baseline (MMHC CRM academics)

- **Organisation:** institutions, batches, subjects, topics, assignments, file submissions, attendance, role-based dashboards, reports (SPI / FPI / ICR where implemented).
- **Phase A (implemented):** taxonomy config for teaching methods and assessment labels; topics carry teaching-method keys; assignments carry types, assessment keys, and evaluation flags; reports can filter by taxonomy.
- **Phase B (implemented):** checklist-style items on assignments; quizzes / MCQ exams (questions, attempts, scoring, optional link to assignments; audience rules per `ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md`); topic-linked **resource library** (e.g. video URL + files).

---

## 3. Gap summary (within Phase A + B only)

| Area | Status / next steps |
|------|---------------------|
| Curriculum labelling | Taxonomy on topics and assignments; extend labels in `config/academics-taxonomy.php` as needed. |
| Homework / assignment types | Types and UI in place; refine per-type student/faculty UX as you learn from users. |
| Grading | File submission + quiz auto-mark + student checklist completion; **rubrics, manual scoring rollups, viva notes** — optional enhancements still open. |
| Quizzes / MCQ | Exams module in place; optional **shared question bank** across exams if desired later. |
| Procedure / video library | Topic resources in place. |

---

## 4. Phased roadmap (execution order — **in scope only**)

### Phase A — Foundation

**Goal:** Same screens, richer semantics; minimal new infrastructure.

1. **Activity / method taxonomy** — Config lists for teaching + evaluation labels; attach to topics and assignments (multi-select where needed).
2. **Assignment types** — e.g. `file_upload`, `quiz`, `presentation`, `care_plan`, `checklist`, `mixed`, etc.
3. **Evaluation metadata** — Formative/summative; flags for MCQ, practical, viva, checklist on assignments for reporting.
4. **Reports** — Filters by activity / assessment / assignment type.

**Exit:** Stakeholder PDF lists for teaching and assessment types are **selectable and reportable** in the product.

---

### Phase B — Structured assessment (checklist, quiz, resources)

**Quiz / exam visibility** (subject vs batch vs institution vs community) is specified in [`ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md`](./ACADEMICS-QUIZ-EXAM-VISIBILITY-DESIGN.md).

1. **Checklist assessments** — Faculty-defined checklist items on assignments; student completion where applicable.
2. **Quiz / MCQ (MVP)** — Exams per visibility rules; timed attempts; auto-mark; optional link to assignments.
3. **Procedure library (light)** — Topic-linked resources (video URL, files, checklist links).

**Exit:** Assignments can combine **upload + checklist + linked quiz**; topics can surface **learning resources**.

---

## 5. Cross-cutting requirements

- **RBAC:** Faculty, institution admin, student (and super/admin) for academics routes and data scope.
- **Audit:** Consider logging for grade-affecting changes as the product matures.
- **Mobile-friendly:** Submissions and attendance views should remain usable on small screens.
- **Demos:** Academic demo seeder reflects taxonomy, sample exam, and topic resources.

---

## 6. Open questions (Phase A + B)

- Which **3–5** teaching methods are mandatory in year one (institution policy)?
- Exam **proctoring** and integrity: out of scope for v1 or any hard requirements?
- Video resources: institution preference (e.g. YouTube unlisted vs uploaded files only)?

---

## 7. Document history

| Date | Change |
|------|--------|
| 2026-05-03 | Initial roadmap from PDF + CRM gap analysis |
| 2026-05-04 | Scope locked to Phase A + B only; OSCE and phases C–H removed from product roadmap |

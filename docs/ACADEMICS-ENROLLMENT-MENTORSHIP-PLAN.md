# Academics — Institute enrollment + cross-institute mentorship

## Two parallel tracks (product)

| Track | Owner | Scope |
|-------|--------|--------|
| **A — Institute enrollment** | Institute admin | Student joins a college; admin approves, assigns batch + faculty |
| **B — Mentorship** | Any mentee ↔ any faculty | No institute boundary; builds profile engagement |
| **C — Mobile (Track 1+2)** | All roles | Bottom nav, student list, mobile academics UI |

Tracks A + B are implemented in phases below. Track C continues separately.

---

## Track A: Student enrollment (institute-gated)

### Flows

**A1 — Student self-registration** (`/register?academics=1`, role = student)

1. Student picks **institute** + preferred **batch(es)** (request only).
2. Account created with `academic_enrollment_status = pending`.
3. **No batch assignment** until institute admin approves.
4. Institute admin sees **Pending enrollments** → Approve (pick batch(es), optional faculty notes) or Reject.
5. On approve: batch sync, status = `approved`, student gets assignments via batch.

**A2 — Institute admin registers student** (`/academics/students/create`)

1. Admin creates student (like faculty create).
2. Status = `approved` immediately; admin assigns batch on same form.
3. Faculty assignment remains via **Subjects → faculty** and **Batch edit** (existing).

**A3 — Faculty self-registration**

- Unchanged: auto-approved, batch sync on register (institute still assigns subjects).

### Notifications

- Dashboard badge + list for institute admin (pending count).
- Student sees banner: “Awaiting approval from [Institute name]”.

---

## Track B: Mentorship (cross-institute)

### Who can participate

| Role | Can be mentee | Can be mentor |
|------|---------------|---------------|
| student | ✅ | ❌ |
| nurse | ✅ | ❌ |
| caregiver | ✅ | ❌ |
| faculty | ❌ | ✅ |
| patient | ❌ | ❌ |

### Flow

1. Mentee browses **all active faculty** on the platform.
2. Sends mentorship **request** (optional message).
3. Faculty **accepts** or **declines**.
4. Active mentorship shown on **both profiles** (counts + names).

### Assignments + ratings (Phase B2)

1. On submit, mentee selects **mentor(s)** to share with (only active mentorships).
2. Institute grading path unchanged (subject faculty / existing submission).
3. Each selected mentor can **view submission** and **rate 1–5 + feedback**.
4. **Profile / SPI extension (Phase B3):** optional “mentor-verified” flag when all selected mentors rated; feeds engagement stats on faculty profile.

---

## Implementation phases

| Phase | Deliverable | Status |
|-------|-------------|--------|
| **1** | DB + enrollment pending + admin approve/reject + admin create student | Done |
| **2** | Mentorship request/accept + profile counts + sidebar | Done |
| **3** | Submit → share with mentors + mentor rating UI | Done (v1) |
| **4** | SPI / profile scoring includes mentor verification | Done |
| **5** | Track 1+2 mobile academics UI | Planned |

---

## Database (new)

- `users.academic_enrollment_status` — `pending` \| `approved` \| `rejected` (students)
- `academic_enrollment_applications` — request audit trail
- `academic_mentorships` — mentee ↔ mentor lifecycle
- `academic_submission_mentor_shares` — which mentors see a submission
- `academic_submission_mentor_reviews` — rating + feedback

---

## Open decisions (confirm with stakeholders)

1. Can a **pending** student request mentors before institute approval? **Default: yes** (mentorship is independent).
2. Must **all** selected mentors rate before submission counts for SPI? **Default: optional Phase B3** — start with institute submission only; mentor ratings as profile engagement first.
3. Email/SMS when enrollment pending? **Default: in-app only v1**.

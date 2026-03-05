# Academics – Attendance (Phase 2)

This document describes how attendance works. **Implemented:** Option A (batch + date); mark by faculty/admin; students view own attendance.

## Purpose

- Record **per-session** attendance: who was present for a given batch/session on a given date.
- Allow **faculty** (or institution admin) to mark attendance for their batches/sessions.
- Optionally use attendance later in **SPI** or eligibility (e.g. minimum attendance to pass).

## Concepts

- **Session** (or "class"): A logical slot—e.g. "Anatomy – Monday 9–10". Can be tied to a **batch** and optionally a **subject**. For Phase 2 we can keep it simple: one attendance record per **(batch, date)** or per **(batch, subject, date)** if we want subject-wise attendance.
- **Attendance record**: One row per (session/date, student): **present**, **absent**, or **leave** (optional).

## Who does what

| Role              | Action |
|-------------------|--------|
| Faculty           | Mark attendance for batches/subjects they teach (for a given date). |
| Institution Admin | Mark or override attendance for their institution’s batches. |
| Super Admin       | View/report only, or same as institution admin if we allow. |
| Student           | View own attendance (read-only). |

## Data model (when implemented)

- **Option A – Batch + date only**  
  - Table: e.g. `academic_attendance`  
  - Columns: `batch_id`, `date`, `user_id` (student), `status` (present/absent/leave).  
  - One row per student per batch per date.  
  - Faculty see batches they’re assigned to; filter by batch and date to mark.

- **Option B – Session (batch + subject + slot)**  
  - Table: `academic_sessions` (optional): `batch_id`, `subject_id`, name, optional day/time.  
  - Table: `academic_attendance`: `session_id` (or batch_id + subject_id + date), `user_id`, `status`, `date`.  
  - Allows subject-wise attendance (e.g. Anatomy vs Physiology separately).

Recommendation: start with **Option A** (batch + date) to keep Phase 2 simple; add subject/session later if needed.

## UI (when implemented)

- **Mark attendance**: Faculty/Admin chooses batch (and date). System lists students in that batch; for each student they set Present / Absent (and optionally Leave). Save writes one row per student for that batch+date.
- **View attendance**: Student sees a list or calendar of dates with status. Admin/Faculty see the same for a selected batch/student.
- **Reports**: Optional “Attendance summary” report (e.g. % present per student per batch in a date range) and “Defaulters” (below X% attendance).

## How it will plug into SPI (later)

- Today **SPI** = % of eligible assignments submitted. We can keep that as is.
- Phase 2 can add a separate **attendance score** (e.g. % of sessions present).
- Later, we can combine or gate: e.g. “Eligible for certificate only if SPI ≥ 70% and attendance ≥ 75%.” That would be a config or rule in the same academics config/dashboard, using the new attendance data.

## Summary

- **Phase 2**: Add `academic_attendance` (batch_id, date, user_id, status). Faculty/Institution Admin mark by batch and date; students view own. Optional report.
- **Later**: Optionally add sessions (subject-wise), then tie attendance into SPI/eligibility rules.

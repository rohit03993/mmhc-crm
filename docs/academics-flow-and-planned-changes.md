# Academics – Whole Flow & Planned Changes

## Part 1: The Whole Flow (How It Works)

Think of it like a **college supervisor** who manages many **colleges**. Each college has its own **admin**, **staff (faculty)**, and **students**. Everyone uses **one login page**; the system sends them to the right place after login.

---

### Step 1: College supervisor (Super Admin)

- **Who:** You (or the person who runs the system for many colleges).
- **What they do:**
  - Create **colleges** (institutions) – name, code, contact.
  - For each college, they **give a login** to the **college admin** (one person per college). That person’s role = **Institution Admin** and is tied to that college.
- **After login:** They see the Academics dashboard with **all** institutions, batches, subjects, topics, assignments – and we will add **student counts** and **reports** so they can see how many students each college has and how they are doing.

---

### Step 2: College (Institution Admin)

- **Who:** The admin of **one** college (the login you gave them).
- **What they do:**
  - Create **batches** (e.g. “B.Sc Nursing 2024–25”) for their college.
  - **Assign students and faculty** to batches (these users must already exist in the system; assignment is “who is in which batch”).
  - Create **subjects** per batch and **assign faculty** to subjects.
  - So effectively they **give logins** by: (a) having users created – e.g. by you or by a separate “add user” flow – and (b) **assigning** those users to batches as “student” or “faculty”. Once assigned, those users log in and see their own Academics area.
- **After login:** They see only **their** institution’s data: their batches, subjects, topics, assignments. We will add a clear **student count** for their institution and easy access to **student reports** (submission/SPI, etc.).

---

### Step 3: Faculty (staff of the college)

- **Who:** Teachers assigned to batches and subjects by the Institution Admin.
- **What they do:**
  - Create **topics** and **assignments** for the subjects they teach.
  - Mark **attendance** for their batches.
  - See **submissions** and download student work.
- **After login:** They see only **their** subjects and batches. We will add **number of students** they teach (across their batches/subjects) and a clear way to open **reports** for those students.

---

### Step 4: Students

- **Who:** Students assigned to a batch by the Institution Admin.
- **What they do:**
  - Log in, see **My Assignments**, submit files, see **My attendance** and **SPI**.
- **After login:** They see only their own assignments, attendance, and progress (no admin/report views).

---

### Summary flow (who gives logins)

| Who                | Gives login to                    | How (in the system) |
|--------------------|-----------------------------------|----------------------|
| College supervisor | College admin (Institution Admin) | Create user with role “institution_admin” and link to institution. |
| College admin      | Faculty, Students                 | Users must exist; admin **assigns** them to batches (as faculty or student). So “giving login” = ensure user exists + assign to batch. |
| (No one “gives” login to supervisor; they are the top-level account.) | | |

So: **Super Admin creates institutions and institution admins.** **Institution Admin creates batches and assigns faculty/students to batches (and subjects).** **Faculty and students then log in** with the same main login page and are redirected to Academics.

---

## Part 2: What We Will Change (Practical Visibility)

Goal: **Admins and faculty see student counts and student reports easily**, without extra steps. Everything stays simple and aligned with the doc you provided.

---

### Change 1: Dashboard – show student counts

- **Super Admin dashboard**
  - Add a card: **Total students** (count across all institutions).
  - In the existing **ICR-by-institution** table, add a column: **Students** (number of students per institution). So at a glance: Institution name | Students | ICR %.

- **Institution Admin dashboard**
  - Add a card: **Students** (total students in their institution – i.e. students in any batch of that institution). Same style as Batches/Subjects cards, with a short label like “Students in our batches”.

- **Faculty dashboard**
  - Add a card: **My students** (count of distinct students in batches/subjects they teach). So they see “I have X students” and can click to go to a list or reports.

---

### Change 2: Dashboard – quick link to student reports

- For **Super Admin** and **Institution Admin**: Under or next to the Students card, add a button/link: **“Student report”** or **“View student report”** that goes to the Reports page with **Student submission** report pre-selected (and institution/batch filter pre-filled for Institution Admin).
- For **Faculty**: Same idea – a link like **“My students’ report”** that opens Reports with Student submission report and filters set to their batches/subjects (so they see only their students).

So: one click from dashboard to the relevant **student submission** (and later attendance) report.

---

### Change 3: Reports – keep it simple and visible

- **Reports** page already has “Student submission” report. We will:
  - Ensure **Institution Admin** sees only their institution’s students (already scoped in backend).
  - Ensure **Faculty** sees only students from batches/subjects they teach (already scoped).
  - Optionally add a short line on the Reports page: “To see student-wise submission and SPI, use **Student submission** report.”

No new report type unless you ask; we just make the existing student report easy to reach from the dashboard and clearly named.

---

### Change 4: Optional – “Students” in sidebar for Admin and Faculty

- **Super Admin / Institution Admin:** In the sidebar, add **“Students”** (or “Student list”) that goes to a simple list:
  - Super Admin: list of all academic students (with institution, batch); show count in sidebar or on page.
  - Institution Admin: list of students in their institution (batch, optional SPI); show count.
- **Faculty:** Same idea: **“My students”** in sidebar → list of students in their batches/subjects (name, batch, optional SPI).

This gives a single place to **see who the students are** and how many, and from there they can use **Reports** for detailed submission/SPI. If you prefer to keep the sidebar minimal, we can skip this and only add the **dashboard cards + report links** (Changes 1 and 2).

---

### Change 5: Wording on dashboard (no new features)

- Replace the generic “Academic Dashboard” / “Role-based content will be added…” line with one short line per role, for example:
  - Super Admin: “Manage institutions and view all colleges’ progress and students.”
  - Institution Admin: “Manage batches, subjects, and view your institution’s students and reports.”
  - Faculty: “Manage your topics, assignments, and view your students and their reports.”
  - Student: (keep as is – assignments and progress.)

So the flow (supervisor → college → staff/students and logins) is clear from the first screen.

---

## Part 3: What We Will *Not* Change (So Nothing Breaks)

- Login flow: same main login + Academics login link; redirect by role stays as is.
- Creating institutions, batches, subjects, topics, assignments: no change.
- Assigning students/faculty to batches and faculty to subjects: no change.
- Submissions, attendance, SPI/FPI/ICR logic: no change.
- Other reports (batch progress, faculty performance, topic completion): no change; we only make **student** counts and **student report** easier to see.

---

## Part 4: Summary of Code Changes (for implementation)

1. **AcademicsDashboardController**
   - Compute: total students (Super Admin), students per institution (for ICR table), students for current institution (Institution Admin), “my students” count (Faculty).
   - Pass these to the dashboard view.

2. **Dashboard view (dashboard.blade.php)**
   - Super Admin: add “Total students” card; add “Students” column to ICR table; add “Student report” link (→ reports with type=student_submission).
   - Institution Admin: add “Students” card; add “Student report” link (→ reports with type=student_submission, institution pre-filled).
   - Faculty: add “My students” card; add “My students’ report” link (→ reports with type=student_submission, scoped to their batches).

3. **Optional:** Sidebar + one “Students” / “My students” list page (controller + route + view) for Admin and Faculty with a simple table (name, institution/batch, optional SPI). If you confirm you want this, we add it; otherwise we only do the dashboard + report links.

4. **Docs**
   - Update `academics-frontend-visibility.md` (and if needed `academics-audit-and-server-checklist.md`) to say: dashboard shows student counts and quick links to student reports; optional Students list for admin/faculty.

---

Once you confirm this flow and that you want **Changes 1, 2, 3 and 5** (and whether you want **Change 4** – Students list in sidebar), we can implement in that order.

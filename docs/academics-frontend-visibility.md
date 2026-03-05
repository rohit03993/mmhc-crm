# Academics module – where to see what (front end)

## Where is the Academics login?

**There is no separate Academics login.** Use the same **Login** button on the main landing page (top right). Enter an academic user’s email and password. After sign-in, if your account has an academic role (Super Admin, Institution Admin, Faculty, or Student), you are automatically redirected to **`/academics`** (Academics dashboard). The login page also shows a short note: *Academic users: use this same login — you will be redirected to the Academics portal.*

---

All academic URLs are under **`/academics`**. The sidebar and dashboard change by role.

---

## 1. Super Admin

| Where | What you see |
|-------|----------------|
| **Sidebar** | Academics (home), Institutions, Batches, Subjects, Topics, Assignments |
| **Dashboard** | Cards: Institutions, Batches, Subjects, Topics, Assignments (all counts) |
| **Institutions** | `/academics/institutions` – List all institutions, Create/Edit/Delete |
| **Batches** | `/academics/batches` – List all batches (all institutions), Create/Edit, Assign students & faculty |
| **Subjects** | `/academics/subjects` – List all subjects (filter by batch), Create/Edit, Assign faculty |
| **Topics** | `/academics/topics` – List all topics (filter by subject), Create/Edit |
| **Assignments** | `/academics/assignments` – List all assignments (filter by topic), Create/Edit/View, **View submissions** per assignment |
| **Submissions** | From Assignment **View** or **Submissions** → list of eligible students and who submitted, Download per student |

---

## 2. Institution Admin

| Where | What you see |
|-------|----------------|
| **Sidebar** | Same as Super Admin (Institutions hidden) |
| **Dashboard** | Same cards (no Institutions card) – counts **only for their institution** |
| **Batches** | Only batches of **their institution** |
| **Subjects** | Only subjects of batches of their institution |
| **Topics** | Only topics under those subjects |
| **Assignments** | Only assignments under those topics |
| **Submissions** | Same as above, only for assignments they can access |

---

## 3. Faculty

| Where | What you see |
|-------|----------------|
| **Sidebar** | Academics (home), Topics, Assignments |
| **Dashboard** | Cards: My Topics, My Assignments |
| **Topics** | `/academics/topics` – Only topics for **subjects they are assigned to** |
| **Assignments** | `/academics/assignments` – Only assignments for those topics; Create/Edit/View, **Submissions** per assignment |
| **Submissions** | From Assignment **View** or **Submissions** → list of students in the batch, who submitted, Download |

---

## 4. Student

| Where | What you see |
|-------|----------------|
| **Sidebar** | Academics (home), **My Assignments** |
| **Dashboard** | Card: My Assignments (total + pending count) |
| **My Assignments** | `/academics/my-assignments` – List of **assignments for their batch(s)** with status (Pending / Submitted / Late) and **Submit** or **Re-submit** / **Download mine** |
| **Submit** | `/academics/assignments/{id}/submit` – Upload file + optional notes; re-submit replaces previous |

Students do **not** see Institutions, Batches, Subjects, Topics, or the full Assignments list. They only see **My Assignments** and the submit flow.

---

## 5. Flow summary

1. **Super Admin** creates **Institutions**.
2. **Super Admin** or **Institution Admin** creates **Batches** (and assigns students/faculty to batches).
3. **Super Admin** or **Institution Admin** creates **Subjects** (per batch) and assigns **faculty** to subjects.
4. **Faculty** (or admins) create **Topics** under subjects, then **Assignments** under topics.
5. **Students** see those assignments under **My Assignments** and **Submit** (or re-submit).
6. **Faculty** and **admins** see **Submissions** per assignment (who submitted, download).

---

## 6. Quick links by role

- **Super Admin:** Institutions → Batches → Subjects → Topics → Assignments → (per assignment) Submissions.
- **Institution Admin:** Batches → Subjects → Topics → Assignments → Submissions (all scoped to their institution).
- **Faculty:** Topics → Assignments → (per assignment) Submissions.
- **Student:** My Assignments → Submit / Re-submit / Download mine.

# Demo Testing Guide

This guide explains how to validate TARRAYA after installing it from scratch. It is meant for demos, interviews, QA passes, and functional reviews.

## 1. Fresh Setup

Run:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan check:seed-integrity
npm run dev
php artisan serve
```

Open:

```http
http://127.0.0.1:8000/login
```

All demo users use this password:

```text
password
```

## 2. Demo Users

| Role | Email | Purpose |
| --- | --- | --- |
| Admin | `admin@unischedulerhub.test` | Full system and academic administration. |
| Academic Coordinator | `coordinator@unischedulerhub.test` | Academic operations without security administration. |
| Professor | `professor@unischedulerhub.test` | Assigned software engineering groups and grading demo. |
| Professor | `professor.math@unischedulerhub.test` | Assigned math/general education groups. |
| Student | `student@unischedulerhub.test` | Open enrollment scenario. |
| Student | `student.enrolled@unischedulerhub.test` | Already enrolled scenario. |
| Student | `student.probation@unischedulerhub.test` | Probation academic load scenario. |
| Student | `student.suspended@unischedulerhub.test` | Blocked enrollment scenario. |
| Student | `student.graded@unischedulerhub.test` | Historical grades scenario. |

## 3. Demo Data Map

The demo seed creates:

- Active enrollment period: `2026-II Enrollment Demo`.
- Grading period: `2026-I Grading Demo`.
- Program: `Software Engineering`.
- Curriculum: `SWE-2026`.
- Subjects:
  - `SWE101` Programming Fundamentals
  - `MTH101` Calculus I
  - `SWE201` Data Structures
  - `SWE220` Databases
  - `GEN110` Professional Ethics
- Published class groups with schedules and classrooms.
- A small-capacity group to test full capacity.
- Existing enrollments and one graded enrollment.

## 4. Automated Smoke Tests

Run:

```bash
php artisan test tests/Feature/DemoSeedIntegrityTest.php
```

Expected result:

- Demo users exist and have correct roles.
- One active academic period exists.
- Subjects, students, groups, schedules, enrollments, and grades exist.
- `php artisan check:seed-integrity` succeeds.
- Admin dashboard opens.
- Professor teaching portal opens.
- Student enrollment portal opens.
- Academic API report and available groups endpoint work with demo data.

For a broader functional pass:

```bash
php artisan test tests/Feature/DemoSeedIntegrityTest.php tests/Feature/EnrollmentApiTest.php tests/Feature/ApiAcademicResourcesTest.php tests/Feature/StudentSubjectEnrollmentGroupsTest.php tests/Feature/AcademicFlowTest.php tests/Feature/RoleAccessTest.php
```

Expected result:

- Enrollment validations pass.
- Role restrictions pass.
- REST API endpoints pass.
- Academic period lifecycle passes.
- Grade operations pass.

## 5. Admin Dashboard Scenario

Login as:

```text
admin@unischedulerhub.test
```

Go to:

```http
/admin/dashboard
```

Validate:

- The dashboard loads without errors.
- Metrics show real seeded data.
- Charts render.
- Recent academic events or attention items appear when applicable.
- The dashboard is operational, not just decorative.

The system should validate:

- Admin can access academic dashboard.
- Dashboard data is scoped to academic operations.
- Metrics come from real tables, not hardcoded values.

## 6. Academic Coordinator Scenario

Login as:

```text
coordinator@unischedulerhub.test
```

Validate:

- Coordinator can access academic modules such as students, class groups, academic periods, enrollments, and audit logs.
- Coordinator cannot access security administration such as users, roles, or permissions.

The system should validate:

- Academic coordinator has operational access.
- Security administration remains admin-only.
- Typing restricted URLs directly returns forbidden access instead of bypassing navigation.

## 7. Student Enrollment Scenario

Login as:

```text
student@unischedulerhub.test
```

Go to:

```http
/student/subject-enrollment
```

Validate:

- The student sees subjects from their curriculum.
- Subjects show availability and academic status.
- Available groups show professor, schedule, capacity, modality, and shift.
- The student can compare professors for available groups.
- Selecting a valid group creates a `SubjectEnrollment`.

The system should validate:

- Student can only enroll in subjects from their curriculum.
- Student can only operate on their own enrollment.
- The group must be published.
- The academic period must allow enrollment.
- The student academic status must allow enrollment.
- Capacity must be available.
- Schedule conflicts must be blocked.
- Maximum credit load must be respected.
- Minimum credit load should produce warnings until the student reaches the configured minimum.

## 8. Duplicate Enrollment Scenario

Login as:

```text
student.enrolled@unischedulerhub.test
```

Go to:

```http
/student/subject-enrollment
```

Validate:

- The system identifies that the student already has an active enrollment for an existing subject.
- The student cannot duplicate the same subject in the same academic period.
- If another group for the same subject is available, selecting it changes the group instead of creating a duplicate enrollment.

The system should validate:

- One active enrollment per student, subject, and academic period.
- Group changes preserve the same enrollment history.
- The previous group is replaced only after validations pass.

## 9. Capacity Scenario

Login as:

```text
student@unischedulerhub.test
```

Find the small-capacity demo group:

- Subject: `SWE220` Databases
- Group capacity: `1`

Validate:

- If the group is already full, the UI/API marks it as unavailable.
- The student cannot enroll in a full group.
- The error message is understandable.

The system should validate:

- Only active enrollment statuses count toward capacity.
- Cancelled or withdrawn enrollments should not consume seats.
- Capacity validation happens in backend, not only visually.

## 10. Schedule Conflict Scenario

Login as:

```text
student.enrolled@unischedulerhub.test
```

Validate:

- The student already has a class scheduled.
- Attempting to select another class group with overlapping day/time should be blocked.
- The system should show conflict information.

The system should validate:

- Same day overlapping schedules are blocked.
- Cancelled schedules should not trigger conflicts.
- Cancelled/withdrawn enrollments should not trigger conflicts.

## 11. Suspended Student Scenario

Login as:

```text
student.suspended@unischedulerhub.test
```

Go to:

```http
/student/subject-enrollment
```

Validate:

- Enrollment actions are blocked.
- The student can still access their portal if allowed by role.
- The reason is clear enough to understand the restriction.

The system should validate:

- Suspended, graduated, and withdrawn academic statuses cannot enroll.
- The restriction is enforced by backend validations.

## 12. Professor Portal Scenario

Login as:

```text
professor@unischedulerhub.test
```

Go to:

```http
/professor/subjects
```

Validate:

- Professor only sees assigned groups.
- Professor sees enrolled students for assigned groups.
- Professor sees schedules and group capacity.
- Professor does not see another professor's private group workspace.

The system should validate:

- Professor data is scoped to `class_groups.professor_id`.
- Direct URL access to another professor's group is forbidden.
- Grade management is available only when academic period and group rules allow it.

## 13. Grades Scenario

Login as:

```text
professor@unischedulerhub.test
```

Use the grading demo period/group.

Validate:

- Grades can be viewed for assigned groups.
- Grades can be registered only when the academic period is in a grading-capable state.
- Closed/cancelled groups or non-editable periods block grade changes.

The system should validate:

- Only assigned professors can manage grades for their groups.
- Grade values must be within valid ranges.
- Grade changes are audited.
- Students can only view their own grades.

## 14. Student My Subjects Scenario

Login as:

```text
student.graded@unischedulerhub.test
```

Go to:

```http
/student/subjects
```

Validate:

- The student sees enrolled or historical subjects.
- Group, professor, schedule, status, and grade information are shown when available.
- The student cannot see another student's subjects or grades by typing URLs.

The system should validate:

- Student subject data is scoped to the authenticated student.
- Historical academic records remain available.

## 15. REST API Scenario

Use an authenticated session or Sanctum-capable client.

Validate assignment report:

```http
GET /api/reports/student-assignments
```

Expected:

- Paginated by student.
- Each student contains nested subject assignments.
- Each assignment includes professor and class group.

Validate available groups:

```http
GET /api/subjects/1/available-groups
```

Expected:

- Published groups only.
- Professor data included.
- Capacity, warnings, conflicts, recommendations, and academic load returned.

Validate enrollment:

```http
POST /api/class-groups/{classGroup}/enrollments
```

Expected:

- Student can enroll only themselves.
- Admin/coordinator can pass `student_id`.
- Duplicate enrollment becomes group change when appropriate.
- Enrollment history is preserved.

## 16. Security And URL Bypass Checks

Validate:

- Student cannot access admin CRUD routes.
- Student cannot access grade management routes.
- Professor cannot access another professor's group enrollments.
- Coordinator cannot access user/role/permission administration.

The system should validate:

- Route-level middleware and policies enforce permissions.
- UI visibility is not the only protection.
- Direct URL typing cannot bypass role restrictions.

## 17. What A Successful Demo Should Prove

At the end of the demo, the system should prove:

- CRUD modules are standardized and usable.
- Assignment flow uses `SubjectEnrollment + ClassGroup`, not legacy pivot tables.
- Students cannot repeat the same subject with multiple professors in the same period.
- Students can compare available professors through class groups.
- Minimum credit load is visible as an academic warning.
- Capacity and schedule conflicts are blocked by backend rules.
- Professors see and manage only their assigned academic workload.
- Grades and academic history are preserved.
- Dashboards and reports use real academic data.
- REST API exists and supports the core academic product requirements.

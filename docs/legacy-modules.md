# Legacy Module Inventory

This document records modules that existed before the current TARRAYA academic flow and how they should be treated going forward.

## Official Current Flow

The production academic flow is:

- Admin and academic coordinator manage academic structure through Programs, Subjects, Students, Professors, Buildings, Classrooms, Class Groups and Academic Periods.
- Enrollment operations happen through Enrollment Management and Subject Enrollment.
- Students use My Subjects and My Schedule.
- Professors use My Subjects, My Schedule, Group Enrollments and Grades.
- Institutional analysis happens through Dashboard, Reports and Academic Audit Logs.
- Identity and role assignment happens through Identity & Access.

## Deprecated Or Compatibility Routes

Compatibility routes must not be promoted in navigation. They may remain temporarily to protect old bookmarks, automated checks or external references while the current TARRAYA flows are stabilized.

Removal rule:

- Keep a compatibility route only when it redirects to an official flow or clearly explains that it is legacy.
- Do not add new features to compatibility screens.
- Remove the legacy route/component after route access tests, Ziggy generation and a manual navigation smoke test confirm that no active menu item depends on it.

### `/user-assignments`

Status: deprecated compatibility route.

Reason:

- The previous assignments page mixed student and professor assignment views.
- The official student flow now lives in `student.subjects`, `student.schedule` and `student.subject-enrollment.index`.
- The official professor flow now lives in `professor.subjects`, `professor.schedule` and `admin.group-enrollments.index`.
- The institutional student-assignment view now lives in `reports.student-assignments.index`.

Current behavior:

- Students are redirected to My Subjects.
- Professors are redirected to My Subjects.
- Admins and academic coordinators are redirected to the Student Assignment Report.

Removal candidate:

- `resources/js/Pages/Assignments/Index.vue`
- `UserController::getUserAssignments`

Remove only after QA confirms no menu item, bookmark or automated check depends on that page.

Current product decision:

- Do not use this page for student or professor workflows.
- Do not use this page for institutional reporting.
- Keep the page only as a compatibility fallback until the next route cleanup pass.

### `/roles` and `/permissions`

Status: deprecated compatibility routes.

Reason:

- Role assignment is managed through Identity & Access.
- The system currently uses code-defined permissions as the source of truth.
- Dedicated UI screens for roles and permissions are not part of the current product model.

Current behavior:

- `/roles` redirects to `/users`.
- `/permissions` redirects to `/users`.

Removal candidate:

- `app/Http/Controllers/RoleController.php`
- `app/Http/Controllers/PermissionController.php`
- `resources/js/Components/Security/Roles.vue`
- `resources/js/Components/Security/Permissions.vue`
- `resources/js/Components/Security/Users.vue`

Remove only after route cache, Ziggy route generation and role access smoke tests are complete.

Current product decision:

- Do not reintroduce these screens in the active navigation.
- Keep role/permission governance code-defined until the product needs an audited permission-management UI.

## Keep For Now

### `TableActionButton` `indigo` alias

Status: compatibility alias.

Reason:

- Some older screens used `color="indigo"`.
- The alias now renders as TARRAYA `brand`.
- New code must use `brand`.

Removal candidate:

- Remove the `indigo` alias after a full codebase search confirms there are no `color="indigo"` references.

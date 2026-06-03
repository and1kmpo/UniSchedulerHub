# UniSchedulerHub

UniSchedulerHub is an academic scheduling and enrollment management system built with Laravel, Inertia, Vue, and Tailwind CSS.

The project started from a junior developer challenge about assigning university subjects to students and professors, and has evolved into a broader academic operations platform.

## Current Scope

- Academic CRUD modules: programs, subjects, students, professors, buildings, classrooms, and class groups.
- Enrollment engine with duplicate validation, capacity validation, schedule conflict detection, academic load warnings, and recommendations.
- Student self-service enrollment through class groups.
- Professor portal for assigned groups, enrolled students, schedules, and grades.
- Role-based dashboards for academic administration and professors.
- REST API for students, professors, subjects, enrollment operations, and student assignment reports.
- Audit trail for critical academic actions.

## Main Roles

- `admin`: system and academic administration.
- `academic_coordinator`: academic operations without security administration.
- `professor`: teaching workspace, assigned groups, students, and grading.
- `student`: own subjects, enrollment options, schedules, and grades.

## Technical Stack

- Laravel 10
- Inertia.js
- Vue 3
- Tailwind CSS
- Laravel Sanctum
- Spatie Laravel Permission
- Chart.js / vue-chartjs

## REST API

See [docs/api.md](docs/api.md) for the current academic REST API documentation.

See [docs/demo-testing.md](docs/demo-testing.md) for the functional demo testing guide.

See [docs/database.md](docs/database.md) for the database model reference used to generate ER diagrams.

See [docs/production-checklist.md](docs/production-checklist.md) for deployment and production readiness checks.

Covered API areas:

- Students CRUD
- Professors CRUD
- Subjects CRUD
- Student assignment report
- Available class groups
- Enrollment
- Group changes
- Unenrollment without deleting academic history

## Local Validation

Recommended functional test set:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php tests/Feature/ApiAcademicResourcesTest.php tests/Feature/StudentSubjectEnrollmentGroupsTest.php tests/Feature/AcademicFlowTest.php tests/Feature/RoleAccessTest.php
```

Frontend build:

```bash
npm run build
```

## Demo Environment

Fresh local setup:

```bash
composer install
npm install
php artisan migrate:fresh --seed
php artisan check:seed-integrity
npm run dev
php artisan serve
```

Functional demo guide:

```text
docs/demo-testing.md
```

Demo credentials use the password `password`.

| Role | Email |
| --- | --- |
| Admin | `admin@unischedulerhub.test` |
| Academic Coordinator | `coordinator@unischedulerhub.test` |
| Professor | `professor@unischedulerhub.test` |
| Professor | `professor.math@unischedulerhub.test` |
| Student | `student@unischedulerhub.test` |
| Student | `student.enrolled@unischedulerhub.test` |
| Student | `student.probation@unischedulerhub.test` |
| Student | `student.suspended@unischedulerhub.test` |
| Student | `student.graded@unischedulerhub.test` |

## Academic Design Principles

- The official assignment flow is `Student -> SubjectEnrollment -> ClassGroup -> Professor`.
- Students do not enroll directly into a subject without a class group.
- Enrollment records preserve history through statuses instead of destructive deletes.
- Soft deletes are reserved for recoverable catalog or infrastructure entities.
- Academic history has priority over physical deletion.

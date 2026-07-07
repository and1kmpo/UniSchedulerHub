# TARRAYA

TARRAYA is an academic operating system for scheduling, enrollment and institutional academic operations, built with Laravel, Inertia, Vue, and Tailwind CSS.

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

See [docs/functional-scope.md](docs/functional-scope.md) for the current academic capabilities and validation references.

See [docs/demo-testing.md](docs/demo-testing.md) for the functional demo testing guide.

See [docs/database.md](docs/database.md) for the database model reference used to generate ER diagrams.

See [docs/production-checklist.md](docs/production-checklist.md) for deployment and production readiness checks.

See [docs/development-workflow.md](docs/development-workflow.md) for the branch, commit, PR, and database safety workflow.

Covered API areas:

- Students CRUD
- Professors CRUD
- Subjects CRUD
- Academic resource creation through REST
- Student assignment report
- Available class groups
- Enrollment
- Group changes
- Unenrollment without deleting academic history

## Local Validation

Recommended functional test set:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php tests/Feature/EnrollmentApiTest.php tests/Feature/StudentSubjectEnrollmentGroupsTest.php tests/Feature/AcademicFlowTest.php tests/Feature/RoleAccessTest.php
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
cp .env.example .env
php artisan key:generate
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
| Admin | `admin@tarraya.test` |
| Academic Coordinator | `coordinator@tarraya.test` |
| Professor | `professor@tarraya.test` |
| Professor | `professor.math@tarraya.test` |
| Student | `student@tarraya.test` |
| Student | `student.enrolled@tarraya.test` |
| Student | `student.probation@tarraya.test` |
| Student | `student.suspended@tarraya.test` |
| Student | `student.graded@tarraya.test` |

## Academic Design Principles

- The official assignment flow is `Student -> SubjectEnrollment -> ClassGroup -> Professor`.
- Students do not enroll directly into a subject without a class group.
- Enrollment records preserve history through statuses instead of destructive deletes.
- Soft deletes are reserved for recoverable catalog or infrastructure entities.
- Academic history has priority over physical deletion.

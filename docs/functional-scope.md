# Functional Scope

This document summarizes the core academic capabilities currently covered by UniSchedulerHub. It is intended as a portfolio and project reference, not as an internal planning log.

## Academic Resource Management

### Students

Covered by:

- Web CRUD: Students module.
- REST API: `POST /api/students`.
- Fields: document, name, phone, email, address, city, semester.
- Additional academic fields: program, curriculum, academic status.

Validation:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php
```

### Professors

Covered by:

- Web CRUD: Professors module.
- REST API: `POST /api/professors`.
- Fields: document, name, phone, email, address, city.
- Role assignment: created professors receive the `professor` role.

Validation:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php
```

### Subjects

Covered by:

- Web CRUD: Subjects module.
- REST API: `POST /api/subjects`.
- Fields: name, description, credits, knowledge area, elective/required flag.
- Subject codes are generated automatically when omitted.

Validation:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php
```

## Academic Assignment Model

The official assignment model is:

```text
Student -> SubjectEnrollment -> ClassGroup -> Professor
```

This reflects a real academic workflow: a student enrolls in a subject offering for an academic period, taught by a professor, with capacity and schedule rules.

Covered by:

- Student self-service enrollment.
- Administrative enrollment operations.
- Class groups with professor, subject, academic period, capacity, modality, shift, and schedules.
- Enrollment history through statuses instead of destructive deletes.

Validation:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php tests/Feature/AcademicFlowTest.php
```

## Academic Report

Covered by:

- REST API: `GET /api/reports/student-assignments`.
- Dashboard/reporting views with real data.
- Pagination by student to keep the report usable when a student has many enrolled subjects.

The report includes:

- Student.
- Enrolled subjects.
- Professor.
- Class group.
- Academic period.
- Active credits and minimum credit load status.

Validation:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php
```

## Academic Rules

### Professor Teaching Load

Supported model:

```text
professors many-to-many subjects through professor_subject
subjects one-to-many class_groups
class_groups professor_id -> users.id
```

### Duplicate Enrollment Prevention

Covered by:

- Duplicate enrollment validation.
- Active enrollment status checks.
- Group changes reuse the existing enrollment instead of creating duplicate active records.

Validation:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php
```

### Minimum Credit Load

Covered by:

- Academic load validation.
- Warnings before the minimum is met.
- Confirmation endpoint blocks final confirmation below the configured minimum credits.

Validation:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php
```

## Platform Capabilities

### Authentication And Roles

Covered by Laravel authentication, role-based redirects, route middleware, and authorization policies.

Validation:

```bash
php artisan test tests/Feature/RoleAccessTest.php
```

### Dashboards And Charts

Covered by role-based dashboards with operational metrics and charts for academic/admin and professor views.

Validation:

```bash
npm run build
```

## Demo Entry Points

Use the demo testing guide:

```text
docs/demo-testing.md
```

Use the API documentation:

```text
docs/api.md
```

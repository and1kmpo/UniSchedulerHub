# TARRAYA Academic REST API

This document describes the current REST API surface for the academic assignment flow. The web UI is built with Inertia/Vue, but these endpoints expose the core academic resources and enrollment operations as JSON APIs.

## Authentication And Roles

All API endpoints require `auth:sanctum`.

Supported roles:

- `admin`: full academic API access.
- `academic_coordinator`: full academic API access except security administration.
- `student`: self-service enrollment API only.

Students can only manage their own enrollments. If a student sends another `student_id`, the API returns `403 Forbidden`.

## Base URL

Local development:

```http
http://127.0.0.1:8000/api
```

## Common Query Parameters

Pagination:

```http
?per_page=15
```

Search and sorting support depends on each resource filter.

## Academic CRUD Resources

These endpoints are available to `admin` and `academic_coordinator`.

### Students

```http
GET    /api/students
POST   /api/students
GET    /api/students/{student}
PUT    /api/students/{student}
PATCH  /api/students/{student}
DELETE /api/students/{student}
```

Create/update payload:

```json
{
  "document": "S1001",
  "name": "Grace Hopper Student",
  "phone": "3000000000",
  "email": "student@example.test",
  "password": "secret123",
  "address": "Main street",
  "city": "Bogota",
  "semester": 1,
  "program_id": 1,
  "curriculum_id": 1,
  "academic_status": "active"
}
```

### Professors

```http
GET    /api/professors
POST   /api/professors
GET    /api/professors/{professor}
PUT    /api/professors/{professor}
PATCH  /api/professors/{professor}
DELETE /api/professors/{professor}
```

Create/update payload:

```json
{
  "document": "P1001",
  "name": "Ada Lovelace",
  "phone": "3000000000",
  "email": "professor@example.test",
  "password": "secret123",
  "address": "Professor address",
  "city": "Bogota"
}
```

### Subjects

```http
GET    /api/subjects
POST   /api/subjects
GET    /api/subjects/{subject}
PUT    /api/subjects/{subject}
PATCH  /api/subjects/{subject}
DELETE /api/subjects/{subject}
```

Create/update payload:

```json
{
  "name": "Differential Calculus",
  "description": "Differential calculus for engineering students.",
  "credits": 4,
  "knowledge_area": "Mathematics",
  "elective": false
}
```

## Student Assignment Report

Available to `admin` and `academic_coordinator`.

```http
GET /api/reports/student-assignments
```

Optional filters:

```http
?academic_period_id=1&search=Grace&per_page=15
```

The report is paginated by student and nests the student's assignments. This keeps the response readable when a student has 5, 6, 7, or more enrolled subjects.

Response shape:

```json
{
  "data": [
    {
      "student": {
        "id": 1,
        "document": "S1001",
        "name": "Grace Hopper Student",
        "email": "student@example.test",
        "semester": 1,
        "program": "Software Engineering"
      },
      "summary": {
        "assignments_count": 5,
        "active_credits": 16,
        "minimum_credits": 7
      },
      "assignments": [
        {
          "enrollment_id": 10,
          "status": "enrolled",
          "period": "2026-I",
          "subject": {
            "id": 3,
            "code": "MAT101",
            "name": "Differential Calculus",
            "credits": 4,
            "knowledge_area": "Mathematics",
            "elective": false
          },
          "professor": {
            "id": 7,
            "name": "Ada Lovelace",
            "email": "professor@example.test"
          },
          "class_group": {
            "id": 22,
            "code": "MAT101-2026-I-G1",
            "name": "Differential Calculus - Group G1",
            "status": "published"
          }
        }
      ]
    }
  ]
}
```

## Enrollment API

Available to `admin`, `academic_coordinator`, and `student`.

### List Enrollments

```http
GET /api/enrollments
```

Optional filters:

```http
?student_id=1&academic_period_id=1&status=enrolled&per_page=15
```

Rules:

- `admin` and `academic_coordinator` may filter by `student_id`.
- `student` always receives only their own enrollments.

### Available Groups For A Subject

```http
GET /api/subjects/{subject}/available-groups
```

Optional filters:

```http
?student_id=1&academic_period_id=1
```

Rules:

- Returns published groups with active schedules.
- Includes professor data so students can compare group options.
- Includes validation results: capacity, duplicate enrollment, schedule conflicts, credit load warnings, and recommendations.

Response shape:

```json
{
  "data": [
    {
      "id": 22,
      "code": "MAT101-2026-I-G1",
      "name": "Differential Calculus - Group G1",
      "capacity": 30,
      "enrolled": 18,
      "available_seats": 12,
      "modality": "In-person",
      "shift": "Day",
      "professor": {
        "id": 7,
        "name": "Ada Lovelace",
        "email": "professor@example.test"
      },
      "is_current": false,
      "can_select": true,
      "validation": {
        "allowed": true,
        "errors": [],
        "warnings": [],
        "recommendations": [],
        "load": {
          "credits": 7,
          "min_credits": 7,
          "max_credits": 21,
          "meets_minimum": true
        }
      },
      "schedules": [
        {
          "id": 15,
          "day": "monday",
          "start_time": "09:00",
          "end_time": "10:00"
        }
      ]
    }
  ],
  "meta": {
    "student_id": 1,
    "subject_id": 3,
    "academic_period_id": 1,
    "current_group_id": null
  }
}
```

### Enroll In A Class Group

```http
POST /api/class-groups/{classGroup}/enrollments
```

Admin/coordinator payload:

```json
{
  "student_id": 1
}
```

Student payload:

```json
{}
```

Rules:

- The group must be published.
- The academic period must allow enrollment.
- The student must have an enrollable academic status.
- The subject must belong to the student's curriculum.
- The group must have available capacity.
- The new schedule must not conflict with active enrollments.
- The enrollment must not exceed the configured maximum credit load.
- If the student already has an active enrollment for the same subject and period, the API changes the group instead of creating a duplicate.

Successful responses:

```json
{
  "message": "Enrollment successful.",
  "type": "enrolled",
  "data": {
    "id": 10
  }
}
```

```json
{
  "message": "Group changed successfully.",
  "type": "group_changed",
  "data": {
    "id": 10
  }
}
```

### Change Group

```http
PATCH /api/enrollments/{enrollment}/change-group
```

Payload:

```json
{
  "class_group_id": 23
}
```

Rules:

- The target group must belong to the same subject.
- The operation runs the same academic validations as enrollment.

### Cancel Or Withdraw Enrollment

```http
DELETE /api/enrollments/{enrollment}
```

Rules:

- The record is not deleted.
- `pre_enrolled` becomes `cancelled`.
- `enrolled` becomes `withdrawn`.
- The operation is blocked if the academic period does not allow unenrollment.

## Domain Error Codes

Common `422` codes:

- `BLOCK_ALREADY_ENROLLED`
- `BLOCK_ALREADY_IN_GROUP`
- `BLOCK_CAPACITY`
- `BLOCK_GROUP_NOT_PUBLISHED`
- `BLOCK_GROUP_WITHOUT_SCHEDULE`
- `BLOCK_MAX_CREDITS`
- `BLOCK_NO_CURRICULUM`
- `BLOCK_OUT_OF_CURRICULUM`
- `BLOCK_SCHEDULE_CONFLICT`
- `BLOCK_STATUS_GRADUATED`
- `BLOCK_STATUS_SUSPENDED`
- `BLOCK_STATUS_WITHDRAWN`
- `BLOCK_UNENROLL_INVALID_STATUS`

## Validation

Relevant test suites:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php
php artisan test tests/Feature/ApiAcademicResourcesTest.php
php artisan test tests/Feature/AcademicFlowTest.php
```

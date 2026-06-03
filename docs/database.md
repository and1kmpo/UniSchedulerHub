# Database Model Reference

This document is a flat reference for building an Entity Relationship Diagram in an external tool. It focuses on the academic model and leaves out low-level Laravel infrastructure tables unless they are relevant to authentication, roles, or API access.

## ERD Scope

Recommended ERD entities:

- `users`
- `students`
- `professors`
- `programs`
- `curricula`
- `subjects`
- `subject_areas`
- `curriculum_subjects`
- `program_subject`
- `professor_subject`
- `academic_periods`
- `academic_period_statuses`
- `academic_period_status_transitions`
- `subject_enrollment_statuses`
- `subject_enrollment_status_transitions`
- `subject_enrollments`
- `class_groups`
- `class_schedules`
- `buildings`
- `classrooms`
- `grades`
- `grade_statuses`
- `subject_prerequisites`
- `subject_equivalences`
- `academic_audit_logs`
- `enrollment_overrides`
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`
- `personal_access_tokens`

Optional/internal tables you can omit from a presentation ERD:

- `sessions`
- `password_reset_tokens`
- `failed_jobs`

## Main Academic Flow

Official assignment flow:

```text
users
  -> students
  -> subject_enrollments
  -> class_groups
  -> users as professor user

subjects
  -> class_groups
  -> subject_enrollments
  -> grades
```

Important rule:

```text
Students do not enroll directly into a subject without a class group.
The real academic assignment is SubjectEnrollment + ClassGroup.
```

## Tables

### users

Purpose: authentication identity for admins, coordinators, professors, and students.

Columns:

```text
id PK
name
email UNIQUE
email_verified_at nullable
password
two_factor_secret nullable
two_factor_recovery_codes nullable
remember_token nullable
current_team_id nullable
profile_photo_path nullable
status
created_at
updated_at
```

Relationships:

```text
users 1--0..1 students
users 1--0..1 professors
users 1--many class_groups as professor_id
users 1--many subject_enrollments as enrolled_by
users 1--many subject_enrollments as cancelled_by
users 1--many grades as created_by
users 1--many grades as updated_by
users 1--many class_schedules as created_by
users 1--many class_schedules as updated_by
users 1--many class_schedules as cancelled_by
users 1--many academic_audit_logs
users polymorphic--many roles through model_has_roles
users polymorphic--many permissions through model_has_permissions
```

### students

Purpose: academic profile for student users.

Columns:

```text
id PK
user_id FK -> users.id
document UNIQUE
phone
address
city
semester
program_id FK -> programs.id
curriculum_id FK -> curricula.id nullable
academic_status
created_at
updated_at
```

Relationships:

```text
students many--1 users
students many--1 programs
students many--0..1 curricula
students 1--many subject_enrollments
students 1--many grades through subject_enrollments
```

Academic status values:

```text
active
probation
suspended
graduated
withdrawn
```

### professors

Purpose: academic profile for professor users.

Columns:

```text
id PK
user_id FK -> users.id
document UNIQUE
phone
address
city
created_at
updated_at
```

Relationships:

```text
professors many--1 users
professors many--many subjects through professor_subject
professors 1--many class_groups through users.id = class_groups.professor_id
professors 1--many grades
```

Implementation note:

```text
class_groups.professor_id references users.id, not professors.id.
grades.professor_id references professors.id.
```

### programs

Purpose: academic programs or degrees.

Columns:

```text
id PK
name
description nullable
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
programs 1--many students
programs 1--many curricula
programs many--many subjects through program_subject
```

### curricula

Purpose: versioned academic plan for a program.

Columns:

```text
id PK
program_id FK -> programs.id
code UNIQUE
name
valid_from
valid_to nullable
is_active
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
curricula many--1 programs
curricula 1--many students
curricula many--many subjects through curriculum_subjects
```

### subjects

Purpose: catalog of academic subjects.

Columns:

```text
id PK
code UNIQUE
name
description nullable
credits
knowledge_area
elective
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
subjects many--many professors through professor_subject
subjects many--many programs through program_subject
subjects many--many curricula through curriculum_subjects
subjects 1--many class_groups
subjects 1--many subject_enrollments
subjects many--many subjects through subject_prerequisites
subjects many--many subjects through subject_equivalences
subjects 1--many grades through subject_enrollments
```

### subject_areas

Purpose: knowledge areas used by curriculum subjects.

Columns:

```text
id PK
code UNIQUE
name
description nullable
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
subject_areas 1--many curriculum_subjects
```

### curriculum_subjects

Purpose: pivot table that defines which subjects belong to a curriculum.

Columns:

```text
id PK
curriculum_id FK -> curricula.id
subject_id FK -> subjects.id
semester_recommended
credits
type
area_id FK -> subject_areas.id nullable
created_at
updated_at
```

Relationships:

```text
curriculum_subjects many--1 curricula
curriculum_subjects many--1 subjects
curriculum_subjects many--0..1 subject_areas
```

Recommended unique constraint for ERD:

```text
UNIQUE(curriculum_id, subject_id)
```

Type values:

```text
required
elective
```

### program_subject

Purpose: pivot table for program-level subject association.

Columns:

```text
id PK
program_id FK -> programs.id
subject_id FK -> subjects.id
semester
created_at
updated_at
```

Relationships:

```text
program_subject many--1 programs
program_subject many--1 subjects
```

Recommended unique constraint for ERD:

```text
UNIQUE(program_id, subject_id)
```

### professor_subject

Purpose: pivot table for subjects a professor is allowed/assigned to teach.

Columns:

```text
id PK
subject_id FK -> subjects.id
professor_id FK -> professors.id
created_at nullable
updated_at nullable
```

Relationships:

```text
professor_subject many--1 professors
professor_subject many--1 subjects
```

Recommended unique constraint for ERD:

```text
UNIQUE(professor_id, subject_id)
```

### academic_period_statuses

Purpose: status catalog for academic periods.

Columns:

```text
id PK
code UNIQUE
label
description nullable
created_at
updated_at
```

Relationships:

```text
academic_period_statuses 1--many academic_periods
academic_period_statuses 1--many academic_period_status_transitions as from_status_id
academic_period_statuses 1--many academic_period_status_transitions as to_status_id
```

Typical codes:

```text
draft
enrollment_open
enrollment_closed
in_progress
academically_closed
archived
frozen
```

### academic_period_status_transitions

Purpose: allowed state transitions for academic periods.

Columns:

```text
id PK
from_status_id FK -> academic_period_statuses.id
to_status_id FK -> academic_period_statuses.id
created_at
updated_at
```

Relationships:

```text
academic_period_status_transitions many--1 academic_period_statuses as from_status
academic_period_status_transitions many--1 academic_period_statuses as to_status
```

### academic_periods

Purpose: academic terms or periods where enrollments, schedules, and grades occur.

Columns:

```text
id PK
name
start_date
end_date
enrollment_deadline nullable
unenrollment_deadline nullable
is_active
academic_period_status_id FK -> academic_period_statuses.id nullable
created_at
updated_at
```

Relationships:

```text
academic_periods many--1 academic_period_statuses
academic_periods 1--many class_groups
academic_periods 1--many subject_enrollments
```

### subject_enrollment_statuses

Purpose: status catalog for subject enrollments.

Columns:

```text
id PK
code UNIQUE
label
description nullable
color nullable
created_at
updated_at
```

Relationships:

```text
subject_enrollment_statuses 1--many subject_enrollments
subject_enrollment_statuses 1--many subject_enrollment_status_transitions as from_status_id
subject_enrollment_statuses 1--many subject_enrollment_status_transitions as to_status_id
```

Typical codes:

```text
pre_enrolled
enrolled
cancelled
withdrawn
approved
failed
revalidation
```

### subject_enrollment_status_transitions

Purpose: allowed state transitions for enrollments.

Columns:

```text
id PK
from_status_id FK -> subject_enrollment_statuses.id
to_status_id FK -> subject_enrollment_statuses.id
created_at
updated_at
```

Relationships:

```text
subject_enrollment_status_transitions many--1 subject_enrollment_statuses as from_status
subject_enrollment_status_transitions many--1 subject_enrollment_statuses as to_status
```

### subject_enrollments

Purpose: official record of a student's subject enrollment in a class group and academic period.

Columns:

```text
id PK
student_id FK -> students.id
subject_id FK -> subjects.id
class_group_id FK -> class_groups.id
academic_period_id FK -> academic_periods.id
status_id FK -> subject_enrollment_statuses.id
enrolled_at nullable
enrolled_by FK -> users.id nullable
cancelled_by FK -> users.id nullable
cancelled_at nullable
created_at
updated_at
```

Relationships:

```text
subject_enrollments many--1 students
subject_enrollments many--1 subjects
subject_enrollments many--1 class_groups
subject_enrollments many--1 academic_periods
subject_enrollments many--1 subject_enrollment_statuses
subject_enrollments many--0..1 users as enrolled_by
subject_enrollments many--0..1 users as cancelled_by
subject_enrollments 1--0..1 grades
subject_enrollments 1--many enrollment_overrides
```

Important business constraint:

```text
One active enrollment per student + subject + academic_period.
```

The original database unique index may exist at table level, but the product rule is status-aware:

```text
Active statuses count for duplicate/capacity/conflict checks.
Cancelled/withdrawn records preserve history.
```

### class_groups

Purpose: specific offering of a subject in a period, taught by a professor.

Columns:

```text
id PK
code UNIQUE nullable
name nullable
subject_id FK -> subjects.id
professor_id FK -> users.id
academic_period_id FK -> academic_periods.id nullable
semester
group_code nullable
capacity
modality
shift
status
created_at
updated_at
```

Relationships:

```text
class_groups many--1 subjects
class_groups many--1 users as professor_id
class_groups many--1 academic_periods
class_groups 1--many class_schedules
class_groups 1--many subject_enrollments
class_groups many--many students through subject_enrollments
```

Status values:

```text
draft
published
cancelled
closed
```

### class_schedules

Purpose: schedule blocks for class groups.

Columns:

```text
id PK
class_group_id FK -> class_groups.id
classroom_id FK -> classrooms.id nullable
day
start_time
end_time
classroom legacy nullable
status
created_by FK -> users.id nullable
updated_by FK -> users.id nullable
cancelled_by FK -> users.id nullable
cancelled_at nullable
created_at
updated_at
```

Relationships:

```text
class_schedules many--1 class_groups
class_schedules many--0..1 classrooms
class_schedules many--0..1 users as created_by
class_schedules many--0..1 users as updated_by
class_schedules many--0..1 users as cancelled_by
```

Day values:

```text
monday
tuesday
wednesday
thursday
friday
saturday
```

Status values:

```text
draft
published
cancelled
closed
```

### buildings

Purpose: campus building catalog.

Columns:

```text
id PK
code UNIQUE
name
description nullable
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
buildings 1--many classrooms
```

### classrooms

Purpose: classrooms and labs.

Columns:

```text
id PK
building_id FK -> buildings.id nullable
name
floor nullable
capacity
description nullable
status
created_at
updated_at
deleted_at nullable
```

Relationships:

```text
classrooms many--0..1 buildings
classrooms 1--many class_schedules
classrooms many--many class_groups through class_schedules
```

Status values:

```text
active
maintenance
inactive
```

### grade_statuses

Purpose: status catalog for grade outcomes.

Columns:

```text
id PK
code UNIQUE
label
description nullable
created_at
updated_at
```

Relationships:

```text
grade_statuses 1--many grades
```

Typical codes:

```text
pending
passed
failed
```

### grades

Purpose: grades associated with subject enrollments.

Columns:

```text
id PK
subject_enrollment_id FK -> subject_enrollments.id
professor_id FK -> professors.id
partial_1 nullable
partial_2 nullable
partial_3 nullable
activities nullable
attendance nullable
final_grade nullable
grade_status_id FK -> grade_statuses.id nullable
created_by FK -> users.id nullable
updated_by FK -> users.id nullable
created_at
updated_at
```

Relationships:

```text
grades many--1 subject_enrollments
grades many--1 professors
grades many--0..1 grade_statuses
grades many--0..1 users as created_by
grades many--0..1 users as updated_by
```

Legacy note:

```text
Earlier schema versions had student_id and subject_id on grades.
Current official grade linkage is subject_enrollment_id.
```

### subject_prerequisites

Purpose: prerequisite subjects.

Columns:

```text
id PK
subject_id FK -> subjects.id
prerequisite_subject_id FK -> subjects.id
logic nullable
min_grade nullable
created_at
updated_at
```

Relationships:

```text
subject_prerequisites many--1 subjects as subject
subject_prerequisites many--1 subjects as prerequisite_subject
```

### subject_equivalences

Purpose: equivalent subjects for curriculum/degree audit logic.

Columns:

```text
id PK
subject_id FK -> subjects.id
equivalent_subject_id FK -> subjects.id
created_at
updated_at
```

Relationships:

```text
subject_equivalences many--1 subjects as subject
subject_equivalences many--1 subjects as equivalent_subject
```

### enrollment_overrides

Purpose: administrative overrides for enrollment rules.

Columns:

```text
id PK
subject_enrollment_id FK -> subject_enrollments.id
admin_id FK -> users.id
reason
created_at
updated_at
```

Relationships:

```text
enrollment_overrides many--1 subject_enrollments
enrollment_overrides many--1 users as admin_id
```

### academic_audit_logs

Purpose: audit log for important academic actions.

Columns:

```text
id PK
user_id FK -> users.id nullable
auditable_type nullable
auditable_id nullable
action
summary nullable
metadata JSON nullable
created_at
```

Relationships:

```text
academic_audit_logs many--0..1 users
academic_audit_logs polymorphic--0..1 auditable
```

Common actions:

```text
enrollment.created
enrollment.group_changed
enrollment.cancelled
grade.updated
academic_period.transitioned
schedule.updated
```

## Authorization Tables

These tables are generated by Spatie Laravel Permission.

### roles

Columns:

```text
id PK
name
guard_name
created_at
updated_at
```

### permissions

Columns:

```text
id PK
name
guard_name
created_at
updated_at
```

### model_has_roles

Columns:

```text
role_id FK -> roles.id
model_type
model_id
```

Relationship:

```text
roles many--many users through model_has_roles
```

### model_has_permissions

Columns:

```text
permission_id FK -> permissions.id
model_type
model_id
```

Relationship:

```text
permissions many--many users through model_has_permissions
```

### role_has_permissions

Columns:

```text
permission_id FK -> permissions.id
role_id FK -> roles.id
```

Relationship:

```text
roles many--many permissions through role_has_permissions
```

## API Token Table

### personal_access_tokens

Purpose: Sanctum API tokens.

Columns:

```text
id PK
tokenable_type
tokenable_id
name
token UNIQUE
abilities nullable
last_used_at nullable
expires_at nullable
created_at
updated_at
```

Relationships:

```text
personal_access_tokens polymorphic--many users
```

## Relationship Summary

Copy-friendly cardinality list:

```text
users 1--0..1 students
users 1--0..1 professors
programs 1--many students
programs 1--many curricula
programs many--many subjects through program_subject
curricula many--1 programs
curricula many--many subjects through curriculum_subjects
subject_areas 1--many curriculum_subjects
professors many--many subjects through professor_subject
subjects 1--many class_groups
users 1--many class_groups as professor_id
academic_periods 1--many class_groups
class_groups 1--many class_schedules
buildings 1--many classrooms
classrooms 1--many class_schedules
students 1--many subject_enrollments
subjects 1--many subject_enrollments
class_groups 1--many subject_enrollments
academic_periods 1--many subject_enrollments
subject_enrollment_statuses 1--many subject_enrollments
subject_enrollments 1--0..1 grades
professors 1--many grades
grade_statuses 1--many grades
subjects many--many subjects through subject_prerequisites
subjects many--many subjects through subject_equivalences
subject_enrollments 1--many enrollment_overrides
users 1--many academic_audit_logs
```

## DBML Starter

This can be pasted into tools such as dbdiagram.io and then expanded with the remaining columns if needed.

```dbml
Table users {
  id bigint [pk]
  name varchar
  email varchar [unique]
  password varchar
  status varchar
  created_at timestamp
  updated_at timestamp
}

Table students {
  id bigint [pk]
  user_id bigint [ref: > users.id]
  document varchar [unique]
  phone varchar
  address varchar
  city varchar
  semester int
  program_id bigint [ref: > programs.id]
  curriculum_id bigint [ref: > curricula.id]
  academic_status varchar
  created_at timestamp
  updated_at timestamp
}

Table professors {
  id bigint [pk]
  user_id bigint [ref: > users.id]
  document varchar [unique]
  phone varchar
  address varchar
  city varchar
  created_at timestamp
  updated_at timestamp
}

Table programs {
  id bigint [pk]
  name varchar
  description text
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table curricula {
  id bigint [pk]
  program_id bigint [ref: > programs.id]
  code varchar [unique]
  name varchar
  valid_from date
  valid_to date
  is_active boolean
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table subjects {
  id bigint [pk]
  code varchar [unique]
  name varchar
  description text
  credits int
  knowledge_area varchar
  elective boolean
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table subject_areas {
  id bigint [pk]
  code varchar [unique]
  name varchar
  description text
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table curriculum_subjects {
  id bigint [pk]
  curriculum_id bigint [ref: > curricula.id]
  subject_id bigint [ref: > subjects.id]
  semester_recommended int
  credits int
  type varchar
  area_id bigint [ref: > subject_areas.id]
  created_at timestamp
  updated_at timestamp
}

Table program_subject {
  id bigint [pk]
  program_id bigint [ref: > programs.id]
  subject_id bigint [ref: > subjects.id]
  semester int
  created_at timestamp
  updated_at timestamp
}

Table professor_subject {
  id bigint [pk]
  professor_id bigint [ref: > professors.id]
  subject_id bigint [ref: > subjects.id]
}

Table academic_period_statuses {
  id bigint [pk]
  code varchar [unique]
  label varchar
  description text
  created_at timestamp
  updated_at timestamp
}

Table academic_periods {
  id bigint [pk]
  name varchar
  start_date date
  end_date date
  enrollment_deadline date
  unenrollment_deadline date
  is_active boolean
  academic_period_status_id bigint [ref: > academic_period_statuses.id]
  created_at timestamp
  updated_at timestamp
}

Table class_groups {
  id bigint [pk]
  code varchar [unique]
  name varchar
  subject_id bigint [ref: > subjects.id]
  professor_id bigint [ref: > users.id]
  academic_period_id bigint [ref: > academic_periods.id]
  semester varchar
  group_code varchar
  capacity int
  modality varchar
  shift varchar
  status varchar
  created_at timestamp
  updated_at timestamp
}

Table buildings {
  id bigint [pk]
  code varchar [unique]
  name varchar
  description text
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table classrooms {
  id bigint [pk]
  building_id bigint [ref: > buildings.id]
  name varchar
  floor int
  capacity int
  description text
  status varchar
  deleted_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table class_schedules {
  id bigint [pk]
  class_group_id bigint [ref: > class_groups.id]
  classroom_id bigint [ref: > classrooms.id]
  day varchar
  start_time time
  end_time time
  status varchar
  created_by bigint [ref: > users.id]
  updated_by bigint [ref: > users.id]
  cancelled_by bigint [ref: > users.id]
  cancelled_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table subject_enrollment_statuses {
  id bigint [pk]
  code varchar [unique]
  label varchar
  description text
  color varchar
  created_at timestamp
  updated_at timestamp
}

Table subject_enrollments {
  id bigint [pk]
  student_id bigint [ref: > students.id]
  subject_id bigint [ref: > subjects.id]
  class_group_id bigint [ref: > class_groups.id]
  academic_period_id bigint [ref: > academic_periods.id]
  status_id bigint [ref: > subject_enrollment_statuses.id]
  enrolled_at timestamp
  enrolled_by bigint [ref: > users.id]
  cancelled_by bigint [ref: > users.id]
  cancelled_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table grade_statuses {
  id bigint [pk]
  code varchar [unique]
  label varchar
  description text
  created_at timestamp
  updated_at timestamp
}

Table grades {
  id bigint [pk]
  subject_enrollment_id bigint [ref: > subject_enrollments.id]
  professor_id bigint [ref: > professors.id]
  partial_1 decimal
  partial_2 decimal
  partial_3 decimal
  activities decimal
  attendance decimal
  final_grade decimal
  grade_status_id bigint [ref: > grade_statuses.id]
  created_by bigint [ref: > users.id]
  updated_by bigint [ref: > users.id]
  created_at timestamp
  updated_at timestamp
}

Table subject_prerequisites {
  id bigint [pk]
  subject_id bigint [ref: > subjects.id]
  prerequisite_subject_id bigint [ref: > subjects.id]
  logic varchar
  min_grade decimal
  created_at timestamp
  updated_at timestamp
}

Table subject_equivalences {
  id bigint [pk]
  subject_id bigint [ref: > subjects.id]
  equivalent_subject_id bigint [ref: > subjects.id]
  created_at timestamp
  updated_at timestamp
}

Table academic_audit_logs {
  id bigint [pk]
  user_id bigint [ref: > users.id]
  auditable_type varchar
  auditable_id bigint
  action varchar
  summary varchar
  metadata json
  created_at timestamp
}
```

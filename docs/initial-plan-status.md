# Initial Plan Status

This document summarizes the current status against the academic hardening plan: Enrollment Engine, soft deletes, productive academic flow, reusable architecture, and production readiness.

## 1. Enrollment Engine

Status: mostly complete for the current demo/product scope.

Completed:

- `Student::subjectEnrollments()` compatibility alias exists.
- Enrollment validation uses `SubjectEnrollment + ClassGroup`.
- Duplicate enrollment validation exists.
- Capacity validation exists.
- Schedule conflict validation exists.
- Academic load validation exists.
- Minimum credit warning exists through `config/enrollment.php`.
- Enrollment API exists.
- Student self-service enrollment exists.
- Admin/coordinator enrollment through API exists.
- Unenrollment changes status instead of deleting history.
- Group change preserves enrollment history.
- Warnings and recommendations are returned by the validation layer.

Remaining:

- Improve frontend wording for every backend domain error.
- Add more user-facing detail for recommendations.
- Add full end-to-end browser coverage for the student enrollment modal.

## 2. Soft Deletes

Status: conservative strategy implemented where it makes sense.

Applied to:

- Programs
- Subjects
- Curricula
- Subject areas
- Buildings
- Classrooms

Not applied by design:

- Students
- Professors
- Users
- Subject enrollments
- Grades
- Academic periods
- Class groups
- Class schedules

Current rule:

- Academic history is preserved through statuses, not deletion.
- Catalog/infrastructure entities can use soft deletes when recovery is useful.

Remaining:

- Add dedicated restore screens consistently where needed.
- Add operational documentation for when to deactivate versus delete.

## 3. Academic Flow

Status: solid demo/product foundation.

Completed:

- Official assignment flow is `Student -> SubjectEnrollment -> ClassGroup -> Professor`.
- Students do not enroll directly into subjects.
- Student portal exists.
- Professor portal exists.
- Group enrollment workspace exists.
- Grade management exists.
- Academic period lifecycle exists.
- Academic audit log exists.
- REST API supports the core assignment flow.
- Demo seed covers main academic scenarios.

Remaining:

- Improve academic period operational UI.
- Add stronger bulk operations for coordinators.
- Add more explicit enrollment opening/closing guidance in UI.

## 4. Scheduler And Smart Scheduler

Status: functional foundation, not final production-grade scheduler.

Completed:

- Class schedules can be created and edited.
- Smart Scheduler board exists.
- Drag/resize behavior has been worked on.
- Schedule persistence and validation exist in backend services.
- Conflict detection services exist.
- Score/optimization services exist.

Remaining:

- Final UX pass for scheduler workspace.
- Confirm drag/resize persistence under all states.
- Add browser-level tests for scheduler interactions.
- Clearly separate manual schedule creation from smart recommendations.
- Add coordinator approval/publish workflow for generated schedules.

## 5. CRUD Standardization

Status: broadly implemented.

Completed modules:

- Programs
- Subjects
- Students
- Professors
- Buildings
- Classrooms
- Class groups

Common patterns:

- Reusable table components.
- Reusable form components.
- Reusable show components.
- Filtering/searching/sorting/pagination.
- Standardized controllers.
- Enterprise SaaS visual direction.

Remaining:

- Final UI consistency audit for action buttons across all tables.
- Confirm mobile behavior for all CRUD pages.
- Remove or document any remaining legacy pages that are future-intended.

## 6. Reporting And Dashboards

Status: strong demo foundation.

Completed:

- Academic/admin dashboard with real metrics.
- Professor dashboard context.
- Charts using Chart.js/vue-chartjs.
- Assignment report based on `SubjectEnrollment + ClassGroup + Professor`.
- Scalable report shape by student with nested assignments.

Remaining:

- Improve chart chunk/code splitting if bundle size becomes a concern.
- Add coordinator-specific dashboard refinements.
- Add export capability if required.

## 7. REST API

Status: implemented for challenge/core academic flow.

Completed:

- Students CRUD API.
- Professors CRUD API.
- Subjects CRUD API.
- Student assignment report API.
- Enrollment listing API.
- Available groups API.
- Enrollment API.
- Change group API.
- Unenrollment API.
- API documentation.

Remaining:

- Add API token usage examples if external integrations are expected.
- Add OpenAPI/Swagger only if needed for presentation or external clients.

## 8. Seeders And Demo Readiness

Status: good.

Completed:

- Demo users by role.
- Academic coordinator demo user.
- Demo periods.
- Demo program and curriculum.
- Demo subjects.
- Demo professors and students.
- Demo class groups and schedules.
- Demo enrollments and grades.
- Seed integrity command updated to the current model.
- Demo testing guide added.
- Demo seed integrity test added.

Remaining:

- Optionally add more scenarios for waitlist/overrides if those become product features.

## 9. Security And Roles

Status: meaningful coverage exists.

Completed:

- Role middleware protects route groups.
- Policies protect sensitive academic operations.
- Students cannot access admin/professor CRUD URLs.
- Professors cannot access another professor's group workspace.
- Coordinator cannot access security administration.
- Tests cover direct URL bypass scenarios.

Remaining:

- Full production security checklist.
- Review `.env.example` for production-ready defaults.
- Confirm Sanctum setup for external API access, if needed.

## 10. Production Readiness Estimate

Current state: strong portfolio/demo build, not full university production.

Estimated readiness:

- Demo/interview readiness: high.
- Challenge requirement readiness: high.
- Internal pilot readiness: medium.
- Real university production readiness: medium-low until scheduler, audit, permissions, backups, monitoring, and operational workflows are hardened.

Next recommended steps:

1. Production configuration checklist.
2. UI consistency audit for buttons/actions/mobile.
3. Scheduler UX and persistence hardening.
4. Browser tests for the highest-value flows.
5. Deployment guide.

# TARRAYA Visual QA Checklist

Use this checklist before declaring TARRAYA brand-complete or recording portfolio screenshots.

## Current QA Status

Static brand cleanup pass completed:

- No visible legacy project-name references remain in `resources`, public docs or `.env.example`.
- Main CRUD modules use shared layout/table/action primitives.
- `BaseButton`, `StatusBadge` and `DataTable` are the official atomic UI layer.
- Dashboard, authenticated layout, reports, enrollment engine, grades and scheduler have been aligned to the TARRAYA surface/token rules.
- Heavy shadows and alternate palette tokens were removed from audited app surfaces.

Manual browser QA still required before final brand-complete sign-off:

- Capture the listed screens in mobile, tablet and desktop.
- Toggle light/dark mode on each critical screen.
- Confirm no overlap in dense tables, scheduler events, filters or report actions.
- Confirm role-specific navigation with admin, coordinator, professor and student demo users.

## Viewports

Validate each critical screen in:

- Mobile: 390px wide.
- Tablet: 768px wide.
- Desktop: 1440px wide.

Validate both light and dark mode.

## Global Criteria

Each screen must pass:

- The purpose is clear in less than 5 seconds.
- The primary action is obvious.
- Buttons use the shared action system.
- Cards use 1px borders and calm surfaces.
- No large decorative shadows.
- Text is readable in light and dark mode.
- No text overlaps buttons, cards, tables or calendar events.
- Empty/loading/error states are understandable.
- Tables do not require unnecessary horizontal scrolling on desktop.
- Mobile layouts stack cleanly.
- The browser title uses the current screen name and TARRAYA.
- No old project name appears.

## Screens To Capture

### Public And Auth

- `/`
- `/login`
- `/forgot-password`
- `/register` if enabled.
- `/user/profile`
- `/user/api-tokens`

### Insights

- `/admin/dashboard`
- `/reports`
- `/reports/student-assignments`
- `/reports/professor-load`
- `/reports/classroom-occupancy`
- `/reports/group-capacity-conflicts`
- `/reports/grade-operations`
- `/reports/academic-events`

### Core

- `/students`
- `/students/create`
- `/professors`
- `/programs`
- `/subjects`
- `/class-groups`
- One class group show page.

### Sync

- `/admin/group-enrollments`
- One group enrollment engine page.
- One class group scheduler page.
- `/academic-periods`

### Rooms

- `/buildings`
- `/classrooms`

### Student Portal

- `/student/subjects`
- `/student/schedule`
- `/student/subject-enrollment`

### Professor Portal

- `/professor/subjects`
- `/professor/schedule`
- One assigned group grades page.

### Admin

- `/users`
- Temporary password modal.
- Block/restore actions.

## Logo Checks

- Favicon is recognizable at browser tab size.
- Navigation uses compact mark where space is tight.
- Login and public entry use the full lockup.
- Reports and print use TARRAYA text branding consistently.

## Scheduler Checks

- Event time format matches calendar labels.
- Short events remain legible.
- Drag and resize feedback is visible.
- Failed schedule updates revert cleanly.
- Mobile calendar does not hide critical actions.

## Reports And Print Checks

- Filters and actions do not collide.
- Export buttons use current filters.
- Print/PDF output has:
  - institutional header;
  - generated timestamp;
  - applied filters;
  - metrics;
  - readable table;
  - footer branding.

## Pass Criteria

TARRAYA can be marked as visually QA-approved when:

- All critical screens pass light and dark mode.
- Mobile issues are documented or fixed.
- Screenshots exist for public entry, login, dashboard, reports, scheduler, enrollment, student portal, professor portal and users.
- Any remaining legacy route is documented as compatibility-only.

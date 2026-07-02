# TARRAYA Brand Compliance Audit

Date: 2026-07-01

This audit checks whether the current product experience fully matches the TARRAYA brand direction: academic operating system, institutional minimalism, data geometry, precise surfaces, role-aware workflows and premium SaaS clarity.

## Executive Status

Current estimated compliance: **90%**.

The product is already recognizably TARRAYA in its main architecture, navigation, auth flow, public entry, dashboard direction, CRUD foundation, reports and core academic workflows. It is not yet fully brand-complete because a few compatibility components remain and final screenshot-based QA is still pending.

## Compliance Checklist

| Area | Status | Notes |
| --- | --- | --- |
| Brand name and product language | Aligned | Main UI and demo credentials use TARRAYA naming and `tarraya.test` demo emails. |
| Logo system | Partial | Favicon, compact mark, mark and lockup exist. Final polish and usage rules still need visual QA. |
| Color tokens | Mostly aligned | Global tokens exist. Some raw hex values and local gray/slate/zinc usage remain. |
| Typography | Mostly aligned | Geist/mono intent exists. Need final pass to avoid decorative monospace usage. |
| Surfaces | Mostly aligned | Main components use 1px borders and calm cards. Some report/detail screens still use local surface patterns. |
| Buttons/actions | Mostly aligned | BaseButton/TableActionButton exist. Reports now use BaseButton for main navigation/export/print actions. |
| Forms | Mostly aligned | Base inputs/selects are in place. API/Profile entry screens are aligned; some Jetstream-compatible internals remain. |
| Tables | Mostly aligned | DataTable exists and CRUDs use it broadly. Some report internals still build local tables. |
| Status badges | Mostly aligned | StatusBadge exists. Some inline badges/spans remain in reports and portals. |
| Navigation | Aligned | Modular navigation matches Insights/Core/Sync/Rooms/Admin and role-aware access. |
| Dark mode | Mostly aligned | Major issues have been improved. Needs final visual QA on critical flows and compatibility pages. |
| Reports/print | Aligned | Functional and professional. Report actions use BaseButton and print styles use a TARRAYA print token block. |
| Scheduler | Mostly aligned | FullCalendar is adopted and styled. Needs final event-density/legibility QA. |
| Motion | Not complete | No unified motion language yet beyond basic transitions. |
| Public portfolio/landing | Mostly aligned | Root page now presents TARRAYA as an academic operating system before login. |

## P0: Must Fix Before "Brand Complete"

1. **Remove or isolate old brand naming**
   - Status: completed for visible docs, seeders, tests and Laravel config defaults.
   - Demo users now use the intentional local domain `tarraya.test`.

2. **Eliminate visible legacy modules from active UI**
   - `resources/js/Pages/Assignments/Index.vue` is deprecated and should remain compatibility-only or be removed after route QA.
   - `resources/js/Components/Security/Users.vue`
   - `resources/js/Components/Security/Roles.vue`
   - `resources/js/Components/Security/Permissions.vue`

3. **Normalize report action buttons**
   - Status: completed for the current report bank and report detail pages.
   - Reports use `BaseButton` variants for back, export and print actions.

4. **Tokenize print template styling**
   - Status: completed for the current print helper.
   - `usePrintableReport.js` uses an explicit TARRAYA print token block.

5. **Final dark/light QA for critical screens**
   - Login
   - Dashboard
   - Users
   - Reports index and report details
   - ClassGroups show
   - Enrollment Engine
   - Smart Scheduler
   - Student My Subjects / My Schedule / Subject Enrollment
   - Professor My Subjects / My Schedule

## P1: Should Fix Before Public Portfolio Demo

1. **Dashboard premium polish**
   - Current dashboard is functional and aligned, but not yet as premium as the reference board.
   - Improve density, graph hierarchy, recent activity rhythm and top summary composition.

2. **Logo refinement pass**
   - Validate logo at these sizes:
     - favicon
     - nav compact
     - auth/login
     - report header
     - README/docs screenshots
   - Ensure the detailed mark is not used where it becomes visually noisy.

3. **Profile/API/auth legacy component pass**
   - Status: partially completed for API Tokens and Profile entry.
   - Jetstream compatibility components still exist:
     - `PrimaryButton.vue`
     - `SecondaryButton.vue`
     - `DangerButton.vue`
     - `TextInput.vue`
     - `InputLabel.vue`
     - `InputError.vue`
     - profile partials
   - Either wrap/migrate them to TARRAYA base components or document them as Jetstream compatibility.

4. **Report local table cleanup**
   - Several reports render local nested tables/cards.
   - Keep local layouts only where report structure requires it, but align borders, spacing, badges and mobile behavior with the design system.

5. **Calendar event legibility QA**
   - Confirm event content remains readable at short durations and on mobile widths.
   - Confirm 12h/AM-PM formatting consistency.

## P2: Nice To Have For Premium Brand Finish

1. **Motion system**
   - Define small transitions for sidebar/nav, report cards, scheduler events and modal openings.
   - Keep motion between 150ms and 250ms for UI microinteractions.

2. **Public product entry**
   - Status: implemented for the root route.
   - Final visual QA still needed on mobile and dark mode.

3. **Brand assets documentation**
   - Add examples for:
     - logo clear space
     - correct/incorrect logo usage
     - light/dark applications
     - report header usage
     - compact mark usage

4. **Screenshot-based QA**
   - Capture mobile and desktop screenshots for key screens and store them as QA evidence for the portfolio.

## Screen-By-Screen Audit

### Auth

Status: **Mostly aligned**

Strengths:
- Login follows brand tokens, minimal card, institutional copy and technical descriptor.

Remaining checks:
- Ensure password reset, confirm password, two-factor, verify email and register screens match the same density and tone.

### App Layout And Navigation

Status: **Aligned**

Strengths:
- Modular navigation maps to product architecture.
- Single-child groups are handled cleanly.
- Compact mark is used in dense navigation contexts.

Remaining checks:
- Final mobile navigation visual QA.

### Dashboard

Status: **Mostly aligned**

Strengths:
- Real operational metrics and role-aware dashboards exist.
- The dashboard communicates academic operations, not generic admin content.

Remaining checks:
- Improve visual hierarchy toward a more premium analytics OS feel.

### CRUD Core Modules

Status: **Mostly aligned**

Screens:
- Programs
- Subjects
- Students
- Professors
- Buildings
- Classrooms
- ClassGroups

Strengths:
- CRUD architecture, tables, forms, filters, actions and show pages are standardized.

Remaining checks:
- Scan for local style drift when a CRUD screen is next touched.

### Enrollment Engine

Status: **Mostly aligned**

Strengths:
- Operational purpose is clear.
- Validations, warnings and recommendations are functional.

Remaining checks:
- Ensure all cards use the same surface/token pattern in both light and dark mode.

### Smart Scheduler

Status: **Mostly aligned**

Strengths:
- FullCalendar replaced custom fragile scheduler logic.
- Drag/resize behavior is now closer to expected calendar UX.

Remaining checks:
- Short event content legibility.
- Mobile behavior.
- Final event styling polish.

### Reports

Status: **Aligned**

Strengths:
- Report bank exists.
- Reports have filters, CSV/export and print/PDF flow.
- Reports answer real operational questions.

Remaining checks:
- Final mobile and print-output QA.

### Student Portal

Status: **Mostly aligned**

Strengths:
- My Subjects, My Schedule and enrollment flow are separated clearly.
- Weekly schedule has student value.

Remaining checks:
- Final light/dark/mobile QA.

### Professor Portal

Status: **Mostly aligned**

Strengths:
- My Subjects and My Schedule now follow the same mental model as student portal.
- Role security tests cover professor boundaries.

Remaining checks:
- Final visual QA against student portal for consistency.

### Identity & Access

Status: **Aligned**

Strengths:
- Clear separation between technical access and academic profiles.
- Temporary password reset is controlled and audited.
- Roles/permissions remain code-governed.

Remaining checks:
- Visual QA of temporary password modal in dark mode.

## Definition Of Done For "Brand Complete"

TARRAYA can be declared brand-complete when:

- No visible product UI uses the old project name.
- P0 items are complete.
- Key screens pass light/dark/mobile visual QA.
- Report buttons and print templates follow design system rules.
- Deprecated modules are removed or documented as compatibility-only.
- Logo variants are documented and used by size/context.
- New UI work uses tokens/components instead of local ad hoc styling.

## Recommended Next Implementation Pass

Start with **Visual QA Pass 1: Critical Screens**.

Why:
- The remaining brand-complete risk is now visual consistency, not architecture.
- The system needs screenshot evidence for portfolio/demo confidence.
- Light/dark/mobile issues are easier to catch with a fixed checklist.

Scope:
- Use `docs/visual-qa-checklist.md`.
- Capture public entry, login, dashboard, reports, scheduler, enrollment, student portal, professor portal and users.
- Fix any visible text contrast, overlap, spacing, or responsive issues found.
- Run `npm run build`.

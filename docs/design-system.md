# TARRAYA Design System

TARRAYA is an academic operating system. The interface must feel precise, institutional, modular and calm. It should organize dense academic operations without looking decorative, playful or generic.

## Brand Tokens

Use semantic Tailwind tokens before raw color scales:

| Token | Value | Use |
| --- | --- | --- |
| `brand` / `brand-600` | `#2563EB` | Primary actions, active states, links, brand emphasis |
| `brand-dark` / `brand-700` | `#1D4ED8` | Hover and pressed states |
| `ink` | `#0F172A` | Primary text in light mode |
| `dark-bg` | `#09090B` | Global dark background |
| `surface` | `#FFFFFF` | Main light surfaces |
| `surface-dark` | `#18181B` | Main dark surfaces |
| `border-light` | `#E2E8F0` | Light borders |
| `border-dark` | `#27272A` | Dark borders |
| `accent` | `#06B6D4` | Analytics and secondary data signals |
| `success` | `#10B981` | Positive states |
| `warning` | `#F59E0B` | Warnings and schedule risks |
| `danger` | `#EF4444` | Errors, destructive actions and critical conflicts |

The legacy `indigo-*` scale is mapped to TARRAYA cobalt for backward compatibility. New code should prefer `brand-*`.

## Typography

- Use `font-sans` for navigation, titles, labels, content and forms.
- Use `font-mono` only for technical data: codes, periods, timestamps, schedules, metrics, logs and identifiers.
- Avoid using monospace as decoration.

## Surfaces

Preferred surface pattern:

```html
class="rounded-lg border border-border-light bg-surface shadow-sm dark:border-border-dark dark:bg-surface-dark"
```

Rules:

- Use 1px borders as the main separation system.
- Keep shadows subtle; avoid large diffuse shadows.
- Avoid nested cards unless the inner element is a repeated item, modal or framed tool.
- Keep cards at `rounded-lg` unless a component has a specific usability reason.

## Actions

Use shared buttons instead of hand-styled buttons:

- `BaseButton variant="primary"` for the main action.
- `BaseButton variant="secondary"` for neutral actions.
- `BaseButton variant="danger"` for destructive actions.
- `BaseButton variant="success"` for positive confirmations.
- `BaseButton variant="warning"` for risky operational actions.

There should be one visually dominant primary action per section.

## Forms

Use shared form controls:

- `BaseInput`
- `BaseSelect`
- `BaseTextarea`
- `BaseCheckbox`
- `FilterPanel`

Form controls should use visible labels, field-level errors and `brand` focus states. Required fields should use the `danger` token for the required marker.

`FilterPanel` is the standard container for report and index filters. Pages should not recreate filter bands with local Tailwind classes.

## Tables

Use `DataTable` for CRUD and report tables. Tables should provide:

- Search when the dataset is operationally searchable.
- Filters when the user needs decision support.
- Sorting for important columns.
- Pagination for large datasets.
- Visible actions without unnecessary navigation.

## Status

Use `StatusBadge` and semantic variants:

- `brand`: active, current, selected.
- `info`: analytical or informational.
- `success`: completed, enrolled, published, approved.
- `warning`: pending, draft, near capacity, incomplete.
- `danger`: cancelled, failed, blocked, conflict.
- `gray`: neutral or inactive.

Do not communicate state by color alone. Pair color with text and, when helpful, an icon.

## Calendar And Scheduler

Scheduler UI should use TARRAYA surfaces and `brand` interaction states. FullCalendar is styled globally through `resources/css/calendar.css`.

Time labels inside event content should stay consistent with the calendar time format.

## New UI Rule

New pages should not introduce raw hex colors or unrelated palettes. If a new visual need appears, add a semantic token first, then use it through Tailwind.

## Navigation Modules

Navigation should reflect the product architecture:

- `Core`: students, professors, programs, subjects and class groups.
- `Sync`: enrollment operations, academic periods and schedule-related workflows.
- `Rooms`: buildings and classrooms.
- `Grades`: grade operations when exposed as a primary module.
- `Insights`: dashboard, reports and audit trails.
- `Admin`: identity, access and sensitive system administration.

Groups with a single visible item should collapse to a direct navigation link in the UI.

## Logo Variants

Use the correct mark for the available size:

- `ApplicationMark`: full isotype for brand moments and larger identity surfaces.
- `ApplicationLogo`: horizontal lockup with wordmark and descriptor.
- `ApplicationCompactMark`: compact T mark for navigation, dense UI and mobile contexts.
- `public/favicon.svg`: micro-scale browser/PWA favicon.

Do not use the detailed isotype in very small navigation slots; use `ApplicationCompactMark` instead.

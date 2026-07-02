# Development Workflow

This workflow keeps the project easy to explain in interviews and easier to review in pull requests.

## Branch Strategy

Create a new branch when the next step changes the topic or ownership area.

Recommended branch names:

```text
feature/fullcalendar-scheduler
feature/academic-api-hardening
feature/dashboard-analytics
feature/demo-readiness
refactor/crud-standardization
test/role-access-coverage
docs/project-presentation
```

Stay on the same branch when changes belong to the same story. For example, scheduler UI, scheduler tests, and scheduler docs can live in one branch.

## Commit Strategy

Prefer small commits that tell the build story:

```text
feat(scheduler): migrate to FullCalendar
refactor(scheduler): remove legacy planner components
test(flow): cover admin student professor schedule visibility
docs: add testing database safety guidance
test(api): enforce role access boundaries
test(api): cover academic resource creation
```

Use one commit when the change is small and cohesive. Use several commits when the PR has implementation, tests, and docs.

## Pull Request Strategy

Open a PR when a branch proves a complete value slice:

- A CRUD module is standardized.
- A role boundary is protected and tested.
- A scheduler workflow is usable and tested.
- A demo or API capability is documented and validated.

Do not wait for the whole product to be finished before opening PRs. A good PR should be reviewable in one sitting.

## PR Description Template

```markdown
## Summary

Briefly explain the product value and technical scope.

## Changes

- Change 1.
- Change 2.
- Change 3.

## Validation

- `php artisan test ...`
- `npm run build`

## Notes

Mention known tradeoffs, follow-up work, or demo impact.
```

## Safe Database Rules

Use this for an existing development database:

```bash
php artisan db:seed
php artisan check:seed-integrity
```

Use this only for a new or disposable database:

```bash
php artisan migrate:fresh --seed
```

Tests must use a separate database:

```text
tarraya_testing
```

Never run destructive migration commands unless the target database is intentionally disposable.

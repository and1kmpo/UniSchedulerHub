# Production Readiness Checklist

This checklist is for preparing UniSchedulerHub for a demo deploy, interview presentation, or controlled pilot environment.

## Environment

Required production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
```

Validate:

- `APP_KEY` is generated with `php artisan key:generate`.
- `.env` is never committed.
- `.env.example` documents required variables without secrets.
- Database credentials use a non-root database user.
- `LOG_LEVEL` is appropriate for the environment.

## Build And Cache

Before deploy:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

After deploy smoke check:

```bash
php artisan about
php artisan check:seed-integrity
```

Use `check:seed-integrity` only in demo/seeded environments.

## Database

Validate:

- `php artisan migrate:fresh --seed` works in a new local/test environment.
- `php artisan migrate --force` works in existing environments.
- Soft deletes are only used for recoverable catalog/infrastructure records.
- Academic history is preserved by statuses.
- Backups exist before running production migrations.

Recommended backup checks:

- Automated database backup.
- Backup restore test.
- Migration rollback strategy.

## Security

Validate:

- `APP_DEBUG=false`.
- HTTPS enabled.
- Secure cookies enabled when deployed over HTTPS.
- Admin credentials are changed after demo setup.
- Demo users are disabled or removed in real production.
- Route middleware protects all role-specific areas.
- Policies protect direct URL access.
- API endpoints require authentication.
- Students cannot operate on another student's data.
- Professors cannot access another professor's group workspace.

Recommended `.env` values for HTTPS deployments:

```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

## Roles And Permissions

Validate:

- `admin` can access security and academic administration.
- `academic_coordinator` can access academic operations, not security administration.
- `professor` can access teaching workspace only.
- `student` can access own subjects, enrollment, schedules, and grades only.

Run:

```bash
php artisan test tests/Feature/RoleAccessTest.php
```

## Enrollment Engine

Validate:

- Duplicate enrollment is blocked.
- Capacity is enforced.
- Schedule conflicts are blocked.
- Suspended students cannot enroll.
- Unenrollment changes status and preserves history.
- Group changes do not create duplicate active enrollments.
- Minimum credit load warnings are visible.

Run:

```bash
php artisan test tests/Feature/EnrollmentApiTest.php tests/Feature/AcademicFlowTest.php
```

## REST API

Validate:

- API routes require authenticated users.
- Admin/coordinator can access CRUD APIs.
- Students, professors, and subjects can be created through REST APIs.
- Student assignment report works.
- Available groups API returns professor comparison data.
- Student self-service API is scoped to the authenticated student.

Run:

```bash
php artisan test tests/Feature/ApiAcademicResourcesTest.php tests/Feature/EnrollmentApiTest.php
```

## Frontend

Validate:

- `npm run build` succeeds.
- Login page loads without Vite dev server in production.
- Browser title is correct.
- Favicon/logo loads.
- Dashboard charts render.
- CRUD tables are searchable/filterable/sortable where expected.
- Main pages work on desktop and mobile.
- Error states are understandable.

Known non-blocking warnings:

- Browserslist data may be outdated.
- Element Plus may still produce a large vendor chunk while it is registered globally.

Future improvement:

- Replace global Element Plus registration with local/on-demand imports where it is still needed.

## Demo Verification

Run:

```bash
php artisan test tests/Feature/DemoSeedIntegrityTest.php
```

Then manually follow:

```text
docs/demo-testing.md
```

Demo should prove:

- CRUD standardization.
- Student enrollment flow.
- Professor assignment flow.
- Student assignment report.
- Academic functional scope documented in `docs/functional-scope.md`.
- Capacity and schedule conflict validation.
- Role-based access.
- Dashboards with real data.
- REST API support.

## Monitoring And Logs

Validate:

- Logs are writable.
- Log rotation is configured on the server.
- Scheduler/queue logs are monitored if queues are enabled.
- Critical academic operations are recorded in `academic_audit_logs`.

Recommended:

- Error tracking service.
- Uptime monitoring.
- Database backup monitoring.

## Go/No-Go

Do not deploy as production if:

- `APP_DEBUG=true`.
- Migrations fail on a clean environment.
- Demo users remain active in a real production environment.
- Role bypass tests fail.
- Enrollment validation tests fail.
- Build fails.
- Database backups are not configured.

Ready for demo/pilot when:

- Tests pass.
- Build passes.
- Seeded demo flow is documented and reproducible.
- Roles are validated.
- Database diagram and API docs are available.
- Branch, commit, PR, and database safety workflow is documented.

# Identity & Access

TARRAYA separates technical account governance from academic profile management.

## Official Responsibility

`/users` is the official Identity & Access module. It is admin-only and is used for:

- Creating operational access accounts.
- Assigning technical roles such as `admin` and `academic_coordinator`.
- Updating login identity data such as name and email.
- Blocking or restoring user access through account status.
- Issuing one-time visible temporary passwords for support cases.
- Deleting only users without academic history.

## Academic Profile Responsibility

Students and professors are not created from `/users`.

- Student accounts are created through the Students module.
- Professor accounts are created through the Professors module.
- Academic profile data stays in the corresponding Core module.

This keeps document, phone, address, city, curriculum, program, semester, teaching profile and academic history consistent.

## Role Ownership

- Admin creates and manages admins and academic coordinators.
- Academic coordinators create and maintain students and professors from the academic modules.
- Student and professor roles are assigned by the academic creation workflows.
- Existing student/professor accounts can be blocked or restored from Identity & Access, but their academic role is not changed there.

## Temporary Password Reset

Admins can generate a temporary password for another account from `/users`.

- The password is generated server-side.
- Only the hashed password is stored.
- The plain temporary password is returned once in the response and is not stored in audit metadata.
- The admin must share it through an institutional channel.
- The user should change it after the next login.
- Admins cannot reset their own password from this action; they must use profile security settings.
- The action is recorded as `identity.password_reset` in `academic_audit_logs`.

## Future UI Scope

Roles and permissions remain code-governed for now. If the system later needs permission management in UI, it should be introduced as a dedicated admin submodule with audit logging and safeguards, not by reviving the legacy Roles/Permissions screens.

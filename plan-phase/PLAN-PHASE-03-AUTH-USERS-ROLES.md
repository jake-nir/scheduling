# PHASE 3 — USERS & ROLES

Status: PLANNED (do not build until requested)

---

## Scope

Complete authentication and authorization: user management CRUD, role
management with JSON permission map, permission-gated navigation/sidebar,
and hardened login/session security.

## Files

- `models\User.php` — CRUD + password hashing/verify (prepared statements)
- `models\Role.php` — CRUD + `hasPermission()` using `roles.permissions` JSON
- `controllers\UserController.php` — user/role list, create, edit, activate/deactivate, reset password
- `views\users\list.php`, `form.php`, `role-form.php`, `roles.php`
- `views\layouts\sidebar.php` — menu items rendered only if user's role has permission
- `core\auth.php` — final login gate replacing Phase 1 temporary auth

## Permission Keys (match master matrix)

```
dashboard.view, calendar.view, personnel.view, personnel.manage,
availability.view, availability.manage, dutyhistory.view,
dutyconfig.manage, rotationconfig.view, schedule.manage,
rotation.view, pod.manage, pod.revisions, reports.view,
users.manage, audit.view, settings.manage
```

| Key | Admin | Scheduler |
|---|:-:|:-:|
| dashboard.view, calendar.view | ✓ | ✓ |
| personnel.view | ✓ | ✓ |
| personnel.manage | ✓ | – |
| availability.view / availability.manage | ✓ | ✓ |
| dutyhistory.view | ✓ | ✓ |
| dutyconfig.manage | ✓ | – |
| rotationconfig.view | ✓ | ✓ |
| schedule.manage | ✓ | ✓ |
| rotation.view | ✓ | ✓ |
| pod.manage (create/finalize/print) | ✓ | ✓ |
| pod.revisions | ✓ | ✓ |
| reports.view | ✓ | ✓ |
| users.manage | ✓ | – |
| audit.view | ✓ | ✓ |
| settings.manage | ✓ | – |

## Behavior

- **Role create/edit**: checkboxes for each permission key; extra future roles
  supported (per spec "Future roles should be supported").
- **User CRUD**: username, full name, email, role, status; password set/reset with
  `password_hash()`, never stored plaintext, never echoed.
- **Hard blocks**: even if a page is opened by URL directly, `require_permission()`
  denies when the role lacks the key → 403 view. Server-side only.
- **Login**: username + password → `password_verify` → `session_regenerate_id(true)`
  → set `$_SESSION['user_id']`, record `last_login`.
- **Logout & audit**: both logged to `audit_logs` (login, logout, user changes).

## Acceptance Criteria

1. Admin can create a new role with a custom permission set.
2. Admin can create/edit/deactivate users and reset passwords.
3. A Scheduler user cannot reach any `*.manage` page (direct URL returns 403).
4. Sidebar hides items the user's role cannot access.
5. Changing a user's role changes their access immediately (no re-login required).
6. Deleting/suspending a user blocks their session on next request.
7. Logout kills the session and invalidates login token.

## Verification Steps

- `php -l` all files.
- Create `scheduler` user; login as scheduler → verify sidebar and direct-URL 403.
- Test resetting a password, immediate effect on next login.
- Confirm audit_logs rows for login/logout/user-create.
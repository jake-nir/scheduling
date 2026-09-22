# PHASE 1 — SYSTEM ARCHITECTURE

Status: PLANNED (do not build until requested)

---

## Scope

Establish the skeleton of the application: folder structure, environment
configuration, PDO connection, router/front controller, core helper library,
session + CSRF foundation, the admin theme (orange accent, CSS variables), and
the login page shell.

## Deliverables

```
duty-scheduling\
├── public\
│   ├── .htaccess                 ← rewrite all to index.php
│   ├── index.php                 ← front controller + login gate
│   ├── assets\
│   │   ├── css\app.css           ← theme (CSS variables, orange accents)
│   │   ├── css\print.css         ← stub for Phase 9
│   │   ├── js\app.js             ← shared UI helpers (placeholder)
│   │   └── img\                  ← placeholder logo
├── config\
│   ├── config.php                ← base URL, session name, timezone, env
│   └── database.php              ← PDO connection (MariaDB), prepared stmts
├── core\
│   ├── init.php                  ← constants, autoload, error display, session start
│   ├── db.php                    ← PDO singleton
│   ├── auth.php                  ← login/logout, is_logged_in(), current_user(), require_permission()
│   ├── csrf.php                  ← csrf_token(), csrf_verify()
│   ├── router.php                ← map URL → controller/action
│   ├── functions.php             ← e(), redirect(), flash(), setting()
│   ├── validation.php            ← required/date/numeric/enum validators
│   └── AuditLog.php              ← log() helper (table in Phase 2)
├── controllers\AuthController.php  ← login / logout actions
├── views\layouts\layout.php      ← base layout shell
└── views\auth\login.php          ← login form
```

## Theme Specification

CSS variables in `app.css`:

```css
:root {
  --primary: #F97316;
  --primary-dark: #C2410C;
  --primary-light: #FFF7ED;
  --dark: #1F2937;
  --bg: #F8FAFC;
  --border: #E5E7EB;
}
```

- Orange used for: primary buttons, navigation highlights, icons, badges,
  active states, important indicators. The overall look stays professional +
  clean + modern + administrative (neutral grays dominate; orange is accent).
- Layout: sticky top navbar, collapsible sidebar, `.no-print` classes from the
  start so Phase 9 printing is trivial.
- `print.css` **stub only** now: `@media print { .sidebar, .navbar, .no-print { display:none !important; } .print-area{ display:block; } }`

## Login Page

- System logo/icon, system name (from `settings` later; constant fallback now),
  username, password, login button, error message area.
- Orange-accent modern design, centered card, full-screen background `--bg`.
- POST only; server-side validation; brute-force throttling placeholder.

## Router

- URL pattern: `/index.php?route=controller/action` (query-string routing keeps
  XAMPP simple; `.htaccess` optional pretty URLs).
- Whitelist of routes; unknown route → 404 view.
- Every controller action runs through permission check (`require_permission()`)
  after auth gate.

## Security Foundation (Phase 1 level)

- `password_hash()` / `password_verify()`; session with `session_regenerate_id`
  on login; HTTP-only cookies.
- CSRF token generated in session, embedded in every POST form, verified on
  every POST.
- All output escaped via `e()`; all DB access via prepared statements.
- PHP 8.2 strict types; `display_errors` on only in local dev.

## Acceptance Criteria

1. Visiting `http://localhost/duty-scheduling/public/` redirects to login.
2. Login accepts seeded admin credentials (created in Phase 2; Phase 1 may use a
   temporary constant hash for testing, removed in Phase 2).
3. Logged-in users reach a placeholder dashboard; unknown routes show 404.
4. Every POST form includes and verifies a CSRF token.
5. Theme renders with orange accents; RESTRICT files inactive until Phase 2.

## Verification Steps

- `php -l` on all created PHP files (no syntax errors).
- Browser test: login page renders, invalid login shows error, valid login enters
  app, logout returns to login.
- Confirm sidebar/navbar have `.no-print` readiness for Phase 9.

**Do not proceed past Phase 1 scaffolds without confirmation.**
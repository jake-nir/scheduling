# PHASE 10 — SECURITY REVIEW, TESTING, UI POLISH

Status: PLANNED (do not build until requested)

---

## Scope

Final hardening and acceptance pass: full security review, the complete
TEST 1–12 scenario suite, bug fixing, performance checks, and UI polish so the
system is a functional, maintainable PHP + MySQL application (not a prototype).

## 1. Security Review Checklist

Authentication & sessions:
- password_hash/password_verify used everywhere; no plaintext.
- session_regenerate_id on login/logout; HttpOnly + Secure cookies; session id
  not in URL; idle logout if configured.
- Login brute-force throttle (attempt counter + lockout window).

Input/Output:
- 100% prepared statements / bound params; NO string-built SQL from user input.
- Every output escaped via `e()`; XSS vectors closed (attributes, JS).
- Server-side validation on ALL endpoints (JS validation is UX only).
- CSRF token verified on every state-changing POST.
- E-mail / service-number format checks; ENUM whitelist checks.

Authorization:
- `require_permission()` on every route + controller action (never hidden buttons only).
- Direct-URL access to any admin page from a Scheduler session → 403.
- Overrides require confirmation + reason; recorded with user + IP.

Data integrity:
- FKs + transactions around multi-step ops (assignment + rotation ledger +
  override + audit in one transaction).
- Sensitive settings (logo upload) validated: type, size, random filename.

Audit:
- Login, logout, personnel/rank/duty/eligibility changes, availability changes,
  schedule create/modify/cancel, overrides, POD create/finalize/revise/print.
- Fields: user_id, username, action, module, record_type, record_id, description, ip_address.

## 2. Test Suite (TEST 1–12)

| # | Scenario | Expected | Phase source |
|---|---|---|---|
| 1 | POW rotation — 5 PO3s | all 5 complete before cycle restart | 7 |
| 2 | PO3 unavailable (leave/schooling) | remains pending; rotation continues | 7 |
| 3 | PO2 as POW | warning (alternate) + recorded assignment | 6 |
| 4 | Sentinel: SN1 Art Balila Lobby/First | recommend Second/Third/other sub-duty | 7 |
| 5 | Repeated Sentinel Lobby/First | warning; Cancel or Proceed Anyway; override recorded | 7 |
| 6 | Armorer 2 reliefs | NO Third Relief offered | 5/7 |
| 7 | Lobby 3 reliefs | all three available | 5/7 |
| 8 | Hard conflict (same-date duty) | assignment BLOCKED | 6 |
| 9 | POD with CDO/OOD/JOOD/POW/Sentinel 1st+2nd+3rd | prints all correctly | 9 |
| 10 | POD change after finalization | revision + history kept | 9 |
| 11 | Personnel on leave | normal assignment prevented | 6 |
| 12 | Manual override of recommendation | confirmation required + reason recorded | 6/7 |

Execution: repeat each scenario against a fresh seed, log pass/fail per test,
fix regressions immediately.

## 3. Bug-Fix + Maintenance Pass

- `php -l` across every PHP file; remove notices/warnings.
- Confirm no SQL errors from ad-hoc queries; add missing index if a slow query
  surfaces on reports/calendar.
- Fix date/time handling (timezone consistency: Asia/Manila via config).
- Ensure `.no-print` and print layout on all printable screens.

## 4. UI Polish

- Consistent orange accents (primary buttons, active nav, badges, alerts) per theme.
- Empty states (no data) with helpful messages; loading indicators on AJAX.
- Form feedback (success/error flashes); confirm modals for destructive actions
  (delete/cancel) and overrides.
- Mobile: sidebar collapses; tables scroll horizontally; cards wrap.
- Final visual QA on login, dashboard, scheduling, rotation, POD print.

## 5. Final Acceptance Criteria

1. All 12 tests pass end-to-end.
2. Security checklist fully green (no plaintext SQL, no missing CSRF, no
   bypassed permission).
3. Zero PHP syntax errors; zero unhandled SQL errors on standard flows.
4. Scheduler and Admin roles each experience the correct scope (sidebar, 403s,
   actions).
5. System runs on XAMPP (PHP 8.2 + MariaDB) via `public\index.php`.

## 6. Sign-off

Report per-test results and remaining known issues. The application is declared
complete only when TEST 1–12 all pass and the security checklist is green.
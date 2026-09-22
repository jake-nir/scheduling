# PERSONNEL DUTY SCHEDULING & PLAN OF THE DAY SYSTEM — MASTER PLAN

> Master plan copy. Each development phase is separated into its own file:
> `PLAN-PHASE-01-...` through `PLAN-PHASE-10-...`.
> The system will NOT be built until a phase is explicitly requested.

---

## 1. System Purpose

A configurable duty scheduling system that manages personnel duty assignments,
duty rotations, sub-duties, relief sequences, availability, rank eligibility,
scheduling conflicts, exceptions, and printable Plans of the Day (POD).

The system must understand configured rotation rules — it must NOT behave like
a random scheduler, and duty rules must NOT be hard-coded in PHP.

---

## 2. Technology Stack

- PHP 8.2 (XAMPP)
- MariaDB 10.4 (XAMPP)
- HTML5, CSS3, Bootstrap 5, JavaScript
- Font Awesome or Bootstrap Icons
- PDO with prepared statements

---

## 3. Folder Structure

```
C:\xampp\htdocs\duty-scheduling\
├── public\                     ← document root
│   ├── index.php               ← front controller / router (+ login gate)
│   ├── assets\css\app.css      ← admin theme (CSS variables, orange accent)
│   ├── assets\css\print.css    ← POD print stylesheet
│   ├── assets\js\app.js, scheduler.js, calendar.js, pod.js
│   ├── assets\img\
│   └── uploads\logo\
├── config\config.php, database.php
├── database\schema.sql, seed.sql, migrations\
├── core\init.php, auth.php, csrf.php, router.php, functions.php,
│          validation.php, db.php, AuditLog.php
├── models\     (User, Role, Personnel, Rank, Duty, SubDuty, Relief,
│                Eligibility, Availability, RotationGroup, RotationMember,
│                RotationAssignment, Schedule, Override, PlanOfDay, PodItem,
│                PodRevision, Setting, AuditLog)
├── services\   (AvailabilityService, ConflictDetector, RotationEngine,
│                Scheduler, PodService, ReportService)
├── controllers\(Auth, Dashboard, Personnel, Rank, Duty, Eligibility,
│                Schedule, Rotation, Pod, Report, User, Audit, Setting)
├── views\layouts\, views\auth\, views\dashboard\, views\personnel\,
│          views\availability\, views\duty-history\, views\scheduling\,
│          views\duties\, views\pod\, views\reports\, views\users\,
│          views\audit\, views\settings\, views\partials\
├── api\        (schedule-recommendation.php, daily-schedule.php, calendar-data.php)
└── plan-phase\  ← THIS plan folder (master + per-phase files)
```

---

## 4. Database Schema (21 tables, normalized)

**Core reference**
- `roles` — id, role_name, description, permissions(JSON), is_active
- `users` — id, username(unique), password_hash, full_name, email, role_id(FK), status, last_login, created_at, updated_at
- `personnel` — id, service_number(unique), first_name, middle_name, last_name, suffix, rank_id(FK), designation, unit, contact_number, email, status, created_at, updated_at
- `ranks` — id, rank_name, rank_abbr, rank_order, is_active, created_at, updated_at

**Duty configuration**
- `duties` — id, duty_code, duty_name, description, start_time, end_time, personnel_required, rotation_type(none|sequential), has_subduties, sort_order, is_active
- `duty_subduties` — id, duty_id(FK), subduty_code, subduty_name, sort_order, is_active
- `duty_reliefs` — id, subduty_id(FK), relief_name, relief_order, sort_order, is_active  *(relief count = number of rows; configurable per sub-duty)*
- `rank_duty_eligibility` — id, rank_id(FK), duty_id(FK), subduty_id(FK null), priority, eligibility(Primary|Alternate), is_active
- `duty_rotation_groups` — id, duty_id(FK), group_name, current_cycle, current_position, is_active
- `duty_rotation_members` — id, rotation_group_id(FK), personnel_id(FK), sequence, is_active

**Operations**
- `personnel_availability` — id, personnel_id(FK), status(enum), start_date, end_date, reason, remarks, created_by
- `schedules` — id, schedule_date, duty_id(FK), subduty_id(FK null), relief_id(FK null), personnel_id(FK), rotation_group_id(null), rotation_cycle(null), source(auto|manual), status, created_by, created_at, updated_at
- `schedule_overrides` — id, schedule_id(FK), personnel_id(FK), assigned_duty_id, normal_duty_id(null), subduty_id(null), relief_id(null), override_type(rank_substitution|sentinel_repeat|rotation_bypass|availability_override), reason, confirmed_by, confirmed_at, created_at
- `rotation_assignments` — id, rotation_group_id(FK), personnel_id(FK), schedule_id(FK null), cycle_number, status(completed|pending|skipped|unavailable), completed_date, notes

**Plan of the Day**
- `plan_of_day` — id, pod_date, reference_number, status(draft|final|cancelled), created_by, approved_by, approved_at, version, created_at, updated_at
- `plan_of_day_items` — id, pod_id(FK), schedule_id(FK null), duty_id, subduty_id(null), relief_id(null), personnel_id, display_order
- `plan_of_day_revisions` — id, pod_id(FK), version, old_item_id, new_item_id, old_personnel_id, new_personnel_id, duty_id, reason, changed_by, changed_at

**Audit & settings**
- `audit_logs` — id, user_id, username, action, module, record_type, record_id, description, ip_address, created_at
- `settings` — id, setting_key(unique), setting_value, description

**Indexes:** all FKs; unique on `users.username`, `personnel.service_number`,
`rank_duty_eligibility(rank_id,duty_id,subduty_id,priority)`,
`schedules(schedule_date,duty_id,subduty_id,relief_id,personnel_id)`.

---

## 5. Table Relationships

```
personnel ──> rank ──> rank_duty_eligibility ──> duty
                │                                    │
                │                                    ├──> duty_subduties ──> duty_reliefs
                │                                    └──> duty_rotation_groups ──> duty_rotation_members
                │                                                                      │
                └───────────────────────────────────────────────────────────────> rotation_assignments
                                                                                          │
personnel_availability ──> personnel                       schedules <───────────────────┘
schedules ──> duty/subduty/relief/personnel                (one assignment row)
schedules ──> schedule_overrides
plan_of_day ──> plan_of_day_items ──> plan_of_day_revisions
```

---

## 6. User Roles & Permissions

Permission keys stored as JSON in `roles.permissions`, checked server-side per route.
Seed roles: **Admin**, **Scheduler**.

| Permission | Admin | Scheduler |
|---|:-:|:-:|
| Dashboard, Calendar, Daily Schedule | ✓ | ✓ |
| Personnel view / CRUD | ✓ | view |
| Availability view / manage | ✓ | ✓ |
| Duty History view | ✓ | ✓ |
| Duty / Sub-Duty / Relief / Eligibility config | ✓ | – |
| Rotation Groups & members | ✓ | view |
| Create/Modify schedules, manual overrides | ✓ | ✓ |
| Rotation Management page | ✓ | view |
| POD create/finalize/print | ✓ | ✓ |
| POD revisions | ✓ | view |
| Reports | ✓ | ✓ |
| User Management | ✓ | – |
| Audit Logs | ✓ | view |
| Settings | ✓ | – |

---

## 7. Duty Configuration Model

`duties.rotation_type` = `none` | `sequential`. Eligibility comes only from
`rank_duty_eligibility` rows (rank → duty → priority → eligibility). Nothing hard-coded.

```
Duty POW      → PO3 → priority 1 → Primary ; PO2 → priority 2 → Alternate
Duty JOOD     → PO2 → priority 1 → Primary ; PO1 → priority 2 → Primary
```

---

## 8. Sentinel Sub-Duty Model

`duties`: **Duty Sentinel**, `has_subduties=1`. Sub-duties: Duty Armorer,
Duty Lobby, Duty Post 1, Duty Post 2. Relief count per sub-duty via `duty_reliefs`:
Armorer → 2 reliefs, Lobby → 3 reliefs, Post 1 → 3 reliefs, Post 2 → 3 reliefs.

---

## 9. Relief Model

Reliefs are rows in `duty_reliefs` (per sub-duty). The UI/engine only ever offers
existing reliefs — Armorer offers First/Second only, Lobby offers all three.

---

## 10. POW Rotation Algorithm — complete all before restarting

```
1 group  = rotation group for Duty POW, members ordered by sequence
2 cycle  = max(cycle_number) in rotation_assignments (else 1)
3 pending = members whose latest rotation_assignments.cycle == cycle AND status != completed
4 candidates = pending filtered by: active, available, rank eligible, no hard conflict
5 recommend first candidate in sequence order
6 no pending candidates left → open cycle+1, restart from sequence 1
7 unavailable members stay pending/skipped in current cycle (never removed)
8 assignment → rotation_assignments row completed; group.current_cycle/position updated
```
Manual pick outside pending set = rotation bypass → warning + `schedule_overrides(rotation_bypass)`.

---

## 11. Sentinel Rotation Algorithm

```
1 prev = last Sentinel schedule (subduty X, relief R)
2 preference: a) same subduty X → next relief
              b) next subduty in sort_order → relief 1
              c) remaining subduties by sort_order
              d) fallback X/R only if nothing else
3 repeat of X/R in cycle → WARNING + confirm (sentinel_repeat override)
4 recommendation is advisory only
```

---

## 12. Conflict Detection Rules

Status precedence: **BLOCKED > WARNING > RECOMMENDED > ALTERNATE**.

- **BLOCKED (hard):** inactive personnel; same-date overlapping duty; invalid/inactive duty, sub-duty, relief; invalid date; unauthorized manual rotation assign.
- **WARNING (confirm + reason + audit):** Sentinel repeat of sub-duty+relief in cycle; outside normal rank-duty eligibility; rotation recommendation bypassed; back-to-back duties under rest-gap; high duty frequency.
- **RECOMMENDED / ALTERNATE:** advisory only.

---

## 13. Manual Override Logic

```
1 pick person + duty (+sub-duty/relief) + date
2 evaluate → Blocked / Warning / Recommended
3 Blocked  → reject, show reason
4 Warning  → modal "normally assigned to <normal duty>. Continue?" + reason
5 confirm  → schedule(source=manual,status=overridden) + schedule_overrides + audit
6 rank-duty eligibility is NEVER permanently changed
```

---

## 14. Plan of the Day

`plan_of_day` header + `plan_of_day_items` snapshot (duty, sub-duty/relief,
person, rank, service number, display_order). Status: draft → final → cancelled.
`plan_of_day_revisions` records deltas with reason/user/time. Print layout via
`print.css` (`@media print` hides sidebar/navbar; A4 portrait/landscape from settings;
header = unit+logo+POD+date+reference; duty section grouped by sub-duty/relief;
footer = Prepared / Reviewed / Approved + timestamp).

---

## 15. Development Phases

| Phase | Scope |
|---|---|
| **1 Architecture** | folder scaffold, config, router, core libs, theme, login, CSRF/session |
| **2 Database** | schema.sql + seed.sql (21 tables), FK/index checks |
| **3 Users & Roles** | users CRUD, role permissions, permission-gated sidebar |
| **4 Personnel & Ranks** | personnel CRUD, ranks CRUD/order, availability |
| **5 Duty Config** | duties, sub-duties, reliefs, rank eligibility, rotation groups |
| **6 Scheduling & Conflicts** | create/daily schedule, conflict detector, overrides, warnings modal |
| **7 Rotation Engine** | POW + Sentinel + relief rotation, rotation dashboard, duty history |
| **8 Calendar, Dashboard, Reports** | daily/weekly/monthly/rotation views, dashboard alerts, reports |
| **9 Plan of the Day** | create/finalize/print/history/revisions |
| **10 Security, Testing, Polish** | TEST 1–12 suite, audit pass, UI polish |

---

## 16. Seed Data

Admin user, roles {Admin, Scheduler}, ranks CDR→SN2, duties {CDO, OOD, JOOD, POW,
Sentinel}, Sentinel sub-duties + per-sub-duty reliefs, POW rotation group with 5
PO3 members, sample personnel, org settings (name, logo, print orientation).

---

## 17. Test Scenarios (master list)

1. POW rotation completes all before restart
2. PO3 unavailable → rotation continues, keeps them pending
3. PO2 as POW → warning + recorded assignment
4. Sentinel next-relief recommendation
5. Repeated Sentinel assignment → warning / proceed-anyway override
6. Two reliefs only (Armorer) → no Third Relief offered
7. Three reliefs (Lobby) → all available
8. Hard conflict → assignment blocked
9. POD contains CDO/OOD/JOOD/POW/Sentinel 1st/2nd/3rd Relief
10. POD revision records history
11. Unavailable personnel not normally assignable
12. Manual override requires confirmation + reason
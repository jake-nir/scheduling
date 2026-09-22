# PHASE 6 — SCHEDULING & CONFLICT DETECTION

Status: PLANNED (do not build until requested)

---

## Scope

Core scheduling workflow: create/daily schedule pages, the server-side
ConflictDetector (BLOCKED vs WARNING vs RECOMMENDED vs ALTERNATE), manual
override flow with confirmation modal, and the scheduling assistant's data
feed. Rotation-aware assignment comes in Phase 7.

## Files

- `services\ConflictDetector.php` — the rules engine
- `services\Scheduler.php` — evaluate(), manualAssign() (Phase 7 adds recommend())
- `controllers\ScheduleController.php` — index/create/store/delete/cancel
- `views\scheduling\create.php`, `daily.php`, `conflicts.php`
- `views\partials\override-modal.php`, `conflict-summary.php`
- `api\schedule-recommendation.php` — JSON feed used by the assistant (Phase 7 fills recommendations; Phase 6 returns eligibility + availability only)

## ConflictDetector Rules

Status precedence: **BLOCKED > WARNING > RECOMMENDED > ALTERNATE**.

### BLOCKED (hard — reject, never bypassable)
1. Personnel is inactive.
2. Personnel already has a schedule on the same date with overlapping time window
   (duty.start_time/end_time overlap or both NULL = full day).
3. Duty, sub-duty, or relief is inactive or not configured for the day.
4. Assignment to a duty the person's rank is not eligible for (no eligibility row)
   → requires an explicit override path in Phase 7; Phase 6 blocks with reason.
5. Invalid/unacceptable date (e.g., before system start or format error).

### WARNING (proceed after confirmation + reason)
1. Duty differs from person's normal primary duty (rank substitution).
2. Back-to-back duty: previous duty ends too close to this one (rest gap < `rest_gap_hours` setting, default 12).
3. Duty frequency: person performed the same duty recently within the frequency window (settings).
4. Rotation recommendation bypassed (Phase 7 hook).

### RECOMMENDED / ALTERNATE
- Advisory only. Phase 6 marks Primary-eligibility+available as likely recommended.

## Manual Assignment Flow

```
1 Pick      person + duty (+subduty/relief) + date (+ optional reason)
2 Evaluate  ConflictDetector → result object {status, issues[]}
3 BLOCKED   → show error, abort
4 WARNING   → modal: "This personnel is normally assigned to <normal duty>.
                     Continue with manual assignment?" + reason textarea
5 Confirm   → insert schedules (source='manual', status='overridden' if warning hit)
              insert schedule_overrides (override_type, reason, confirmed_by)
              audit_log append (user/IP)
6 Permanent rank-duty eligibility is NEVER changed.
```

## Daily Schedule View

- Table: Duty | Sub-Duty | Relief | Personnel | Rank | Status | Source | Actions
- Filter by date; duplicate person on same day flagged in orange.
- Delete allows "cancel with reason" that writes an override+audit.

## Overrides Table (schedule_overrides)

override_type enum: rank_substitution | sentinel_repeat (Phase 7) | rotation_bypass (Phase 7) | availability_override.
reason + confirmed_by + confirmed_at recorded for every override.

## Acceptance Criteria (cross-cutting)

- TEST 3 — PO2 selected for Duty POW → WARNING shown; proceeding records the assignment + override.
- TEST 8 — person already assigned same-date duty → assignment BLOCKED.
- TEST 11 — person on Leave → not assignable normally (BLOCKED).
- TEST 12 — manual override requires confirmation + reason; audit row exists.
- Warnings never silently bypassed; blocked never silently allowed.

## Verification Steps

- `php -l`; run the four TEST scenarios above in the browser.
- Verify audit_logs entries include action, module, record_id, user, IP.
- Confirm duplicate same-day assignment impossible without cancellation first.
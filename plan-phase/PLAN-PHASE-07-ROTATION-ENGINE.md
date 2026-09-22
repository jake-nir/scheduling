# PHASE 7 — ROTATION ENGINE & DUTY HISTORY

Status: PLANNED (do not build until requested)

---

## Scope

The reusable rotation engine: POW sequential rotation (complete-all-before-restart),
Sentinel sub-duty + relief rotation, relief-sequence recommendations, duty
history, the rotation management dashboard, and the scheduling assistant's
recommendation feed. Wires into Phase 6 Scheduler and ConflictDetector.

## Files

- `services\RotationEngine.php` — POW + Sentinel + relief rotation logic
- `controllers\RotationController.php` — rotation dashboard, assign-from-rotation
- `controllers\DutyHistoryController.php` — per-person history
- `views\scheduling\rotation.php` (dashboard), `views\duty-history\person.php`
- `api\schedule-recommendation.php` — extended: recommended / alternates / unavailable / warnings
- Hooks: `Scheduler::recommend()` delegates to RotationEngine; ConflictDetector gains WARNING cases for rotation bypass + sentinel repeat.

## POW Rotation (sequential — complete all before restart)

Rotation state derived from `duty_rotation_groups.current_cycle` +
`rotation_assignments` ledger:

```
1 members = active members of group ordered by sequence
2 cycle   = current_cycle (start 1 if empty)
3 pending = members whose latest rotation_assignments row for this group
            has cycle == current_cycle AND status != 'completed'
4 candidates = pending filtered: active + no availability overlap
              + rank eligible (Primary or Alternate) + no BLOCKED conflict
5 recommend first candidate in sequence order
6 if no pending candidates remain → increment cycle (current_cycle+1),
   ledger rows open for new cycle, restart from sequence 1
7 unavailable members keep status 'skipped'/'unavailable' in current cycle —
   they stay pending and do NOT advance the pointer (TEST 1, TEST 2)
8 assignment writes schedules + rotation_assignments(status=completed,
   cycle_number, completed_date); updates current_position
```

- Selecting a member who is NOT the recommended/pending candidate =
  `rotation_bypass` WARNING + `schedule_overrides.rotation_bypass` (TEST 12 variant).
- "Complete all before restarting" is guaranteed because the ledger keeps skipped
  members pending until they are available.

## Sentinel Rotation (relief + sub-duty)

```
1 for a given sub-duty within the day, order reliefs by relief_order
2 recommend the next relief after the person's last Sentinel relief
3 across sub-duties, rotate by sort_order after exhausting reliefs
4 repeat of same sub-duty+relief within the cycle → sentinel_repeat WARNING
   (modal shows previous assignment + proposed assignment; Proceed Anyway records
   schedule_overrides.sentinel_repeat)  (TEST 4, TEST 5)
5 relief count honors configured rows only — Armorer never offers Third (TEST 6),
   Lobby offers all three (TEST 7)
```

## Duty History

- Page per person: past schedules (date, parent duty, sub-duty, relief, status, source).
- Used by recommendations for frequency + rotation positioning.
- Sample expected output for SN1 Art Balila:
  Sept 1 → Sentinel/Lobby/First; Sept 5 → Sentinel/Post 1/First; Sept 9 → Sentinel/Lobby/Second

## Scheduling Assistant Feed (JSON)

Given date + duty (+subduty/relief):
- `recommended[]` — 🟢 primary rotation candidate + reason chips (eligible, available, pending in cycle, no conflict)
- `alternates[]` — 🟡 valid but not primary (other ranks, other sequence positions)
- `unavailable[]` — 🔴 with status (Leave/Schooling/…)
- `warnings[]` — ⚠️ (sentinel repeat, frequency, back-to-back, rotation bypass)
- `manual_enabled` — always true; assigning a non-recommended person re-runs ConflictDetector

## Rotation Dashboard

For Duty POW: "Current Cycle: N" + table:

| Sequence | Personnel | Status |
|--|--|--|
| 1 | PO3 Juan | Completed |
| 2 | PO3 Pedro | Pending |
| 3 | PO3 Bryan | Completed |
| 4 | PO3 Carlo | Unavailable |
| 5 | PO3 Daniel | Completed |

Plus: Current cycle, current position, completed count, pending count,
unavailable list, and "Next recommended personnel". Sentinel view shows per
sub-duty the relief rotation state and last assigned.

## Acceptance Criteria

- TEST 1 — 5-member POW group completes all five before restart.
- TEST 2 — unavailable members remain pending and get picked when available.
- TEST 4 — SN1 Art Balila after Lobby/First is recommended Lobby/Second (or Post 1).
- TEST 5 — reselecting Lobby/First triggers warning; Proceed Anyway records override.
- TEST 6/7 — relief counts honored by the engine.
- Rotation state machine is reusable by reports + dashboard (single service).

## Verification Steps

- `php -l`; run TEST 1–7 scenarios in browser with seeded data.
- Simulate leave for Pedro & Carlo, generate 6 days of POW → confirm completed sequence + restart after cycle fins with them skipped.
- Audit rows for rotation assignments + sentinel overrides.
- Confirm `api\schedule-recommendation.php` returns all four sections.
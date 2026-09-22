# PHASE 5 — DUTY CONFIGURATION

Status: PLANNED (do not build until requested)

---

## Scope

Fully configurable duty management: duties, sub-duties (Sentinel children),
per-sub-duty reliefs, rank→duty eligibility with priority, and rotation groups
+ members. All rules live in the database — nothing hard-coded.

## Files

- `controllers\DutyController.php` — duties CRUD
- `controllers\SubDutyController.php` — sub-duties CRUD under a parent duty
- `controllers\ReliefController.php` — reliefs CRUD per sub-duty
- `controllers\EligibilityController.php` — rank-duty eligibility matrix CRUD
- `controllers\RotationGroupController.php` — rotation groups + members CRUD
- `views\duties\list.php`, `form.php`, `subduties.php`, `reliefs.php`, `eligibility.php`, `rotation-groups.php`
- `models\Duty.php`, `SubDuty.php`, `Relief.php`, `Eligibility.php`, `RotationGroup.php`, `RotationMember.php`

## Duty Fields

duty_code (unique), duty_name, description, start_time, end_time, personnel_required,
rotation_type(none|sequential), has_subduties (0|1), sort_order, is_active.

- `has_subduties=1` locks the user into POSTing via sub-duty+relief (e.g., Sentinel).
- `personnel_required` = how many personnel the duty/section needs per day.

## Sub-Duty Fields (parent = Duty Sentinel)

subduty_code, subduty_name, sort_order, is_active. A parent duty's sub-duties are
managed on a dedicated tab (Duty Sentinel → Armorer/Lobby/Post 1/Post 2).

## Relief Fields (per sub-duty)

relief_name, relief_order, is_active.
**Relief count = number of relief rows.** Tests:
- Duty Armorer has only First/Second → UI/engine MUST NOT offer Third (TEST 6).
- Duty Lobby has First/Second/Third → all three available (TEST 7).

## Eligibility Matrix

rank_duty_eligibility: rank_id + duty_id + optional subduty_id + priority +
eligibility(Primary|Alternate).
- Combining a rank with both a parent duty AND its sub-duty checks is allowed.
- Example seeds: POW→PO3(P1 Primary)/PO2(P2 Alternate); JOOD→PO2(P1)/PO1(P2);
  Sentinel sub-duties→SN1(P1)/SN2(P2)/PO3(P3 Alternate).
- No duplicate (rank,duty,subduty,priority) rows.

## Rotation Groups

For duties with `rotation_type='sequential'`:
- Group: duty_id, group_name, current_cycle, current_position.
- Members: rotation_group_id, personnel_id, sequence, is_active.
- Sequences start at 1; a member cannot be in the same group twice.

## UI Rules (admin only — dutyconfig.manage)

- Duties list with active toggle + reorder.
- Sub-duty tab: add/edit/reorder sub-duties for the parent.
- Relief tab per sub-duty: add/edit/reorder reliefs.
- Eligibility matrix table (rank × duty rows) to set priority & Primary/Alternate.
- Rotation group page: pick duty, name group, then add members in sequence order.

## Acceptance Criteria

1. Adding a Third Relief to Duty Armorer is the ONLY way it ever becomes available.
2. Eligibility changes take effect for the scheduler immediately (no code change).
3. A duty with `has_subduties=0` never shows sub-duty/relief pickers downstream.
4. Duplicate rotation memberships rejected.
5. Scheduler role can view config but every mutation route returns 403.

## Verification Steps

- `php -l`; CRUD walkthrough per module.
- Confirm Armorer offers 2 reliefs, Lobby 3 (tests 6/7 at config level).
- Confirm deactivated duty/rank/relief disappears from scheduler pickers.
- Audit rows for duty/subduty/relief/eligibility/rotation changes.
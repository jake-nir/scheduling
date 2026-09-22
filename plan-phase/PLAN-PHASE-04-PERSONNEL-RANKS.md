# PHASE 4 — PERSONNEL & RANKS

Status: PLANNED (do not build until requested)

---

## Scope

Personnel directory management and configurable rank management, plus personnel
availability records that the scheduling engines will later consume.

## Files

- `controllers\PersonnelController.php` — list, search/filter, create, edit, activate/deactivate
- `controllers\RankController.php` — list, create, edit, reorder, activate/deactivate, set default eligibility hint (actual eligibility in Phase 5)
- `controllers\AvailabilityController.php` — list by person, add, edit, close/delete
- `views\personnel\list.php`, `form.php` (modal), `show.php` (profile + availability + duty history stub)
- `views\ranks\list.php`, `form.php`
- `views\availability\list.php`, `form.php`
- `models\Personnel.php`, `Rank.php`, `Availability.php`

## Personnel Fields (per spec)

service_number (unique), first_name, middle_name, last_name, suffix,
rank_id (FK), designation, unit, contact_number, email, status(active/inactive).

Display name format used everywhere: `RANK FNAME M. LNAME` e.g. `PO3 Bryan H Corpuz PCG`.

## Rank Fields

rank_name, rank_abbr, rank_order, is_active.
- Reorder changes `rank_order` values (higher = more senior display).
- Deactivating a rank keeps historical personnel records intact (FK preserved).

## Availability Fields

personnel_id, status (Available/Leave/Schooling/Sick/Official Assignment/Training/
Temporarily Unavailable/Other), start_date, end_date (nullable = open-ended), reason, remarks.
- Overlapping availability blocks rejected (same person, overlapping date range).
- An "Available" row can be closed early; cannot have two active overlapping rows.

## Validation

- Service numbers unique, format alphanumeric.
- Rank required; names min-length; email format; dates valid and end >= start.
- Server-side validation always; JS only improves UX.

## Acceptance Criteria

1. Admin adds/edits personnel; service_number duplicate rejected.
2. Rank list reorderable; inactive rank not offered for new personnel.
3. Personnel profile: info + current/future availability + recent assignments placeholder.
4. Scheduler role can VIEW personnel and MANAGE availability only (personnel create/edit hidden).
5. Availability overlap blocked with clear error.
6. All changes appended to audit_logs.

## Verification Steps

- `php -l` files; CRUD walkthrough with seeded sample personnel (Art Balila, etc.).
- Test overlapping availability rejection.
- Confirm audit rows: personnel.create/edit, rank.reorder, availability.add.
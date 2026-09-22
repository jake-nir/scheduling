# PHASE 9 — PLAN OF THE DAY (POD)

Status: PLANNED (do not build until requested)

---

## Scope

Dedicated POD module: create from an existing schedule date, generate snapshot
items, finalize, preview, print, history, and revision tracking with version
control and reason capture. Power the printable, official Plan of the Day.

## Files

- `services\PodService.php` — create/draft, snapshot, finalize, revise
- `controllers\PodController.php` — create, list (history), view, finalize, revise, cancel
- `views\pod\create.php`, `list.php`, `view.php`, `preview.php`, `print.php`, `revisions.php`
- `assets\css\print.css` — completed (lives from Phase 1 stub to full)
- `assets\js\pod.js` — preview/print helpers

## Data Structure

`plan_of_day`:
- pod_date, reference_number (auto or manual), status (draft|final|cancelled),
  version, created_by, approved_by, approved_at.

`plan_of_day_items` (snapshot, taken at generation — final POD unaffected by later schedule edits):
- pod_id, schedule_id (null if manually composed), duty_id, subduty_id, relief_id,
  personnel_id, display_order.
- Items carry rank + service number via personnel/rank JOINs at render time.

`plan_of_day_revisions`:
- pod_id, version, old/new item + old/new personnel, duty_id, reason, changed_by, changed_at.

## Workflow

1. **Create**: pick date → auto-build draft from that date's schedules
   (grouped: CDO, OOD, JOOD, POW, Sentinel sub-duty × relief) → allow reorder/edit.
2. **Draft** savable, not shown as official.
3. **Finalize** (`final`): frozen. Requires approved_by + approved_at.
4. **Revise** a FINAL POD → version+1; capture old→new + reason + user; keep
   previous version/history (TEST 10).
5. **Cancel**: mark cancelled with reason; never delete.

## Print Layout (official office use)

- **Header**: org name + logo (settings) centred, "PLAN OF THE DAY", date, reference number.
- **Duty section** (config-driven, not hard-coded), grouped by sub-duty then relief:
  ```
  DUTY CDO:                    CDR JOSE M BAYANI PCG
  DUTY OOD:                    ENS MYLA L CRUZ PCG
  DUTY JOOD:                   PO2 JUAN DELA CRUZ PCG
  DUTY POW:                    PO3 BRYAN H CORPUZ PCG
  DUTY SENTINEL — FIRST RELIEF:  SN1 CHRISTIAN L LOPEZ PCG
  DUTY SENTINEL — SECOND RELIEF: SN2 ART BALILA PCG
  DUTY SENTINEL — THIRD RELIEF:  SN1 RYAN A BANG PCG
  ```
  Each line: duty name (: sub-duty — relief) : rank first middle last unit. Time shown if configured.
- **Footer**: Prepared by / Reviewed by / Approved by lines + generated timestamp.
- **@page**: size from `print_orientation` setting (portrait/landscape), A4, clean margins.
- **CSS**:
  ```css
  @media print {
    .sidebar, .navbar, .no-print { display: none !important; }
    .print-area { display: block; }
    @page { size: A4 portrait; margin: 18mm; }
  }
  ```
- Buttons **Preview POD** and **Print POD** (print only ever shows `.print-area`).

## Acceptance Criteria

- TEST 9 — POD with CDO/OOD/JOOD/POW/Sentinel (1st/2nd/3rd Relief) renders all
  assignments correctly in preview + print.
- TEST 10 — final POD changed → revision recorded; previous version intact.
- Final POD cannot be silently edited; draft-only edits allowed without revision row.
- Printing shows no UI chrome; portrait/landscape from settings.

## Verification Steps

- `php -l`; create POD from a seeded day; preview + print; capture print CSS check
  (sidebar/navbar absent).
- Finalize → attempt edit without reason → blocked; with reason → version bump + revision row.
- Confirm audit rows: pod.create/finalize/revise/print.
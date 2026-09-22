# PHASE 8 — CALENDAR, DASHBOARD & REPORTS

Status: PLANNED (do not build until requested)

---

## Scope

Operational visibility: calendar views (daily/weekly/monthly/rotation), the
dashboard with today's duties + alerts, and the reports module (duty roster,
duty history, POW rotation, Sentinel rotation, availability, personnel status).

## Files

- `controllers\DashboardController.php` — dashboard aggregate queries
- `controllers\CalendarController.php` — daily/weekly/monthly/rotation views
- `controllers\ReportController.php` — report generation + print-friendly output
- `views\scheduling\calendar.php`, `views\dashboard\index.php`
- `views\reports\roster.php`, `duty-history.php`, `pow-rotation.php`,
  `sentinel-rotation.php`, `availability.php`, `personnel-status.php`
- `api\calendar-data.php` — JSON for the calendar widget

## Dashboard

- **Today's Duties** card list: CDO, OOD, JOOD, POW, Sentinel (from DB, not hard-coded).
- **Upcoming Duties** — next 7 days, grouped by date.
- **Personnel Status** counts by availability status (Available/Leave/Schooling/Sick/Other).
- **Scheduling Alerts** (computed live):
  - Unfilled duty (duty required on date with < personnel_required assigned)
  - Rotation issue (pending member now available; cycle stalled)
  - Personnel unavailable
  - Same-duty warning (recent same Sentine/ex duty)
  - Manual override / rank substitution (recent schedule_overrides)
  - Sentinel repeated assignment

## Calendar Views

- **Daily**: date picker → full duty list for that day → jump to create/daily schedule.
- **Weekly**: 7-day grid, duties per day.
- **Monthly**: month grid cells show count of assigned duties; click → daily view.
- **Rotation**: drills into POW/Sentinel cycle state (Phase 7 dashboard link).

## Reports (print-friendly, `.no-print` hiding)

1. **Duty Roster** — daily/weekly/monthly by duty.
2. **Personnel Duty History** — per person, full history (like Phase 7 page, batch).
3. **POW Rotation Report** — current cycle, completed/pending/unavailable, next recommendation.
4. **Sentinel Rotation Report** — sub-duty × relief grid, last assigned, next recommendation.
5. **Availability Report** — who is Leave/Schooling/etc in a date range.
6. **POD Report** — printable POD via Phase 9; stub link here until then.

## Acceptance Criteria

1. Dashboard accurately shows today's duties from schedules.
2. Unfilled-duty alert appears when a required duty has < expected personnel.
3. Calendar daily view equals daily-schedule view content.
4. Reports reflect current schedule state (fresh queries, no stale caches).
5. Scheduler role sees dashboard/reports/calendar; reports respect viewing perms.

## Verification Steps

- `php -l`; seed 3 days of schedules and confirm dashboard/calendar/reports.
- Force an unfilled duty (set personnel_required higher) → alert fires.
- Print-preview each report: no sidebar/navbar artifacts.
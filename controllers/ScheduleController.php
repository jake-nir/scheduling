<?php
declare(strict_types=1);

class ScheduleController
{
    public function index(): void
    {
        require_permission('schedule.manage');

        $pageTitle = 'Daily Scheduling';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $schedules = validate_date($date) ? Schedule::allForDate($date) : [];
        $viewFile = APP_ROOT . '/views/scheduling/daily.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('schedule.manage');

        $pageTitle = 'Create Assignment';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $duties = Duty::active();
        $personnel = Personnel::all();
        $viewFile = APP_ROOT . '/views/scheduling/create.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function store(): void
    {
        require_permission('schedule.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=schedule/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=schedule/create');
        }

        $assignment = [
            'schedule_date' => trim((string) ($_POST['schedule_date'] ?? '')),
            'duty_id' => (int) ($_POST['duty_id'] ?? 0),
            'subduty_id' => !empty($_POST['subduty_id']) ? (int) $_POST['subduty_id'] : null,
            'relief_id' => !empty($_POST['relief_id']) ? (int) $_POST['relief_id'] : null,
            'personnel_id' => (int) ($_POST['personnel_id'] ?? 0),
            'start_time' => trim((string) ($_POST['start_time'] ?? '')),
            'end_time' => trim((string) ($_POST['end_time'] ?? '')),
        ];

        $result = Scheduler::evaluate($assignment);
        $confirmed = !empty($_POST['confirm_warning']);
        $overrideType = trim((string) ($_POST['override_type'] ?? 'rank_substitution'));

        if ($result['status'] === 'BLOCKED') {
            $availabilityOverride = $overrideType === 'availability_override'
                && $confirmed
                && ConflictDetector::isAvailabilityOnlyBlock($result['blocking_issues'] ?? $result['issues']);
            if (!$availabilityOverride) {
                flash('error', implode('; ', $result['issues']));
                redirect(APP_PUBLIC_URL . '/?route=schedule/create&date=' . urlencode($assignment['schedule_date']));
            }
        }

        $reason = trim((string) ($_POST['reason'] ?? ''));

        try {
            $scheduleId = Scheduler::manualAssign($assignment, [
                'confirmed' => $confirmed || $result['status'] !== 'WARNING',
                'reason' => $reason,
                'override_type' => $overrideType,
                'created_by' => current_user()['id'] ?? 0,
            ]);
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
            redirect(APP_PUBLIC_URL . '/?route=schedule/create&date=' . urlencode($assignment['schedule_date']));
        }

        flash('success', 'Assignment saved.');
        redirect(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode($assignment['schedule_date']));
    }

    public function cancel(): void
    {
        require_permission('schedule.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=schedule/index');
        }

        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
        $reason = trim((string) ($_POST['reason'] ?? ''));
        if ($scheduleId <= 0) {
            flash('error', 'Schedule record not found.');
            redirect(APP_PUBLIC_URL . '/?route=schedule/index');
        }

        if (!Schedule::cancel($scheduleId, $reason, current_user()['id'] ?? 0)) {
            flash('error', 'Unable to cancel this assignment.');
            redirect(APP_PUBLIC_URL . '/?route=schedule/index');
        }

        flash('success', 'Assignment cancelled.');
        redirect(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode((string) ($_POST['schedule_date'] ?? date('Y-m-d'))));
    }

    public function conflicts(): void
    {
        require_permission('schedule.manage');

        $pageTitle = 'Scheduling Conflicts';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $viewFile = APP_ROOT . '/views/scheduling/conflicts.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }
}

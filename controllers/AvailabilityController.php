<?php
declare(strict_types=1);

class AvailabilityController
{
    public function index(): void
    {
        require_permission('availability.view');

        $pageTitle = 'Availability';
        $personnel = Personnel::all();
        $viewFile = APP_ROOT . '/views/availability/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('availability.manage');

        $pageTitle = 'Add Availability';
        $personnel = Personnel::all();
        $viewFile = APP_ROOT . '/views/availability/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('availability.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=availability/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=availability/index');
        }

        $personnelId = (int) ($_POST['personnel_id'] ?? 0);
        $status = trim((string) ($_POST['status'] ?? 'Available'));
        $startDate = trim((string) ($_POST['start_date'] ?? ''));
        $endDate = trim((string) ($_POST['end_date'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $remarks = trim((string) ($_POST['remarks'] ?? ''));

        if ($personnelId <= 0 || $startDate === '') {
            flash('error', 'Personnel and start date are required.');
            redirect(APP_PUBLIC_URL . '/?route=availability/create');
        }

        if (!validate_date($startDate) || ($endDate !== '' && !validate_date($endDate))) {
            flash('error', 'Please enter valid dates.');
            redirect(APP_PUBLIC_URL . '/?route=availability/create');
        }

        if ($endDate !== '' && strtotime($endDate) < strtotime($startDate)) {
            flash('error', 'End date cannot be earlier than start date.');
            redirect(APP_PUBLIC_URL . '/?route=availability/create');
        }

        if (Availability::overlapsForPerson($personnelId, $startDate, $endDate !== '' ? $endDate : $startDate)) {
            flash('error', 'Availability overlap detected for this person.');
            redirect(APP_PUBLIC_URL . '/?route=availability/create');
        }

        Availability::create([
            'personnel_id' => $personnelId,
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
            'remarks' => $remarks,
        ]);

        AuditLog::log('availability.add', 'availability', 'Availability added.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'personnel_availability', null, $_SERVER['REMOTE_ADDR'] ?? null);
        flash('success', 'Availability saved.');
        redirect(APP_PUBLIC_URL . '/?route=availability/index');
    }
}

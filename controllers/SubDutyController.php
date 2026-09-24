<?php
declare(strict_types=1);

class SubDutyController
{
    public function index(): void
    {
        require_permission('dutyconfig.manage');

        $dutyId = (int) ($_GET['duty_id'] ?? 0);
        $duty = $dutyId > 0 ? Duty::findById($dutyId) : null;
        if (!$duty) {
            flash('error', 'Duty not found.');
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        $pageTitle = 'Sub-Duties: ' . ($duty['duty_name'] ?? 'Duty');
        $subduties = SubDuty::allForDuty($dutyId);
        $viewFile = APP_ROOT . '/views/duties/subduties.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('dutyconfig.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        $dutyId = (int) ($_POST['duty_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        $subDutyCode = trim((string) ($_POST['subduty_code'] ?? ''));
        $subDutyName = trim((string) ($_POST['subduty_name'] ?? ''));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($dutyId <= 0 || $subDutyCode === '' || $subDutyName === '') {
            flash('error', 'Duty, sub-duty code and name are required.');
            redirect(APP_PUBLIC_URL . '/?route=subduties/index&duty_id=' . $dutyId);
        }

        $payload = [
            'duty_id' => $dutyId,
            'subduty_code' => $subDutyCode,
            'subduty_name' => $subDutyName,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ];

        if ($id > 0) {
            SubDuty::update($id, $payload);
            flash('success', 'Sub-duty updated successfully.');
        } else {
            SubDuty::create($payload);
            flash('success', 'Sub-duty created successfully.');
        }

        redirect(APP_PUBLIC_URL . '/?route=subduties/index&duty_id=' . $dutyId);
    }

    public function delete(): void
    {
        require_permission('dutyconfig.manage');

        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $dutyId = (int) ($_POST['duty_id'] ?? $_GET['duty_id'] ?? 0);
        if ($id > 0) {
            SubDuty::delete($id);
        }

        redirect(APP_PUBLIC_URL . '/?route=subduties/index&duty_id=' . $dutyId);
    }
}

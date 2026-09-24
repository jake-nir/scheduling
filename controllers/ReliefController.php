<?php
declare(strict_types=1);

class ReliefController
{
    public function index(): void
    {
        require_permission('dutyconfig.manage');

        $subDutyId = (int) ($_GET['subduty_id'] ?? 0);
        $subDuty = $subDutyId > 0 ? SubDuty::findById($subDutyId) : null;
        if (!$subDuty) {
            flash('error', 'Sub-duty not found.');
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        $pageTitle = 'Reliefs: ' . ($subDuty['subduty_name'] ?? 'Sub-duty');
        $reliefs = Relief::allForSubDuty($subDutyId);
        $viewFile = APP_ROOT . '/views/duties/reliefs.php';

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

        $subDutyId = (int) ($_POST['subduty_id'] ?? 0);
        $id = (int) ($_POST['id'] ?? 0);
        $reliefName = trim((string) ($_POST['relief_name'] ?? ''));
        $reliefOrder = (int) ($_POST['relief_order'] ?? 0);
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($subDutyId <= 0 || $reliefName === '') {
            flash('error', 'Sub-duty and relief name are required.');
            redirect(APP_PUBLIC_URL . '/?route=reliefs/index&subduty_id=' . $subDutyId);
        }

        $payload = [
            'subduty_id' => $subDutyId,
            'relief_name' => $reliefName,
            'relief_order' => $reliefOrder,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ];

        if ($id > 0) {
            Relief::update($id, $payload);
            flash('success', 'Relief updated successfully.');
        } else {
            Relief::create($payload);
            flash('success', 'Relief created successfully.');
        }

        redirect(APP_PUBLIC_URL . '/?route=reliefs/index&subduty_id=' . $subDutyId);
    }

    public function delete(): void
    {
        require_permission('dutyconfig.manage');

        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $subDutyId = (int) ($_POST['subduty_id'] ?? $_GET['subduty_id'] ?? 0);
        if ($id > 0) {
            Relief::delete($id);
        }

        redirect(APP_PUBLIC_URL . '/?route=reliefs/index&subduty_id=' . $subDutyId);
    }
}

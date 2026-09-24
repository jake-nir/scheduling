<?php
declare(strict_types=1);

class DutyController
{
    public function index(): void
    {
        require_permission('dutyconfig.manage');

        $pageTitle = 'Duty Configuration';
        $duties = Duty::all();
        $viewFile = APP_ROOT . '/views/duties/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('dutyconfig.manage');

        $pageTitle = 'Create Duty';
        $viewFile = APP_ROOT . '/views/duties/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function edit(): void
    {
        require_permission('dutyconfig.manage');

        $id = (int) ($_GET['id'] ?? 0);
        $duty = Duty::findById($id);
        if (!$duty) {
            flash('error', 'Duty not found.');
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        $pageTitle = 'Edit Duty';
        $viewFile = APP_ROOT . '/views/duties/form.php';
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

        $id = (int) ($_POST['id'] ?? 0);
        $dutyCode = trim((string) ($_POST['duty_code'] ?? ''));
        $dutyName = trim((string) ($_POST['duty_name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $startTime = trim((string) ($_POST['start_time'] ?? ''));
        $endTime = trim((string) ($_POST['end_time'] ?? ''));
        $personnelRequired = max(1, (int) ($_POST['personnel_required'] ?? 1));
        $rotationType = in_array($_POST['rotation_type'] ?? 'none', ['none', 'sequential'], true) ? (string) $_POST['rotation_type'] : 'none';
        $hasSubduties = isset($_POST['has_subduties']) ? 1 : 0;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($dutyCode === '' || $dutyName === '') {
            flash('error', 'Duty code and name are required.');
            redirect(APP_PUBLIC_URL . '/?route=duties/create');
        }

        $payload = [
            'duty_code' => $dutyCode,
            'duty_name' => $dutyName,
            'description' => $description,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'personnel_required' => $personnelRequired,
            'rotation_type' => $rotationType,
            'has_subduties' => $hasSubduties,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ];

        if ($id > 0) {
            Duty::update($id, $payload);
            flash('success', 'Duty updated successfully.');
            AuditLog::log('duty.update', 'duties', 'Duty updated.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'duties', $id, $_SERVER['REMOTE_ADDR'] ?? null);
        } else {
            $newId = Duty::create($payload);
            flash('success', 'Duty created successfully.');
            AuditLog::log('duty.create', 'duties', 'Duty created.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'duties', $newId, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        redirect(APP_PUBLIC_URL . '/?route=duties/index');
    }

    public function delete(): void
    {
        require_permission('dutyconfig.manage');

        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            Duty::delete($id);
            AuditLog::log('duty.delete', 'duties', 'Duty deleted.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'duties', $id, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        redirect(APP_PUBLIC_URL . '/?route=duties/index');
    }
}

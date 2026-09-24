<?php
declare(strict_types=1);

class RotationGroupController
{
    public function index(): void
    {
        require_permission('dutyconfig.manage');

        $dutyId = (int) ($_GET['duty_id'] ?? 0);
        $duty = $dutyId > 0 ? Duty::findById($dutyId) : null;
        if (!$duty) {
            $duty = Duty::active()[0] ?? null;
            $dutyId = $duty ? (int) $duty['id'] : 0;
        }

        $pageTitle = 'Rotation Groups';
        $duties = Duty::all();
        $groups = $dutyId > 0 ? RotationGroup::allForDuty($dutyId) : [];
        $personnel = Personnel::all();
        $viewFile = APP_ROOT . '/views/duties/rotation-groups.php';

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
        $groupName = trim((string) ($_POST['group_name'] ?? ''));
        $personnelIds = $_POST['personnel_ids'] ?? [];

        if ($dutyId <= 0 || $groupName === '') {
            flash('error', 'Duty and group name are required.');
            redirect(APP_PUBLIC_URL . '/?route=rotation-groups/index&duty_id=' . $dutyId);
        }

        $groupId = RotationGroup::create([
            'duty_id' => $dutyId,
            'group_name' => $groupName,
            'current_cycle' => 1,
            'current_position' => 0,
            'is_active' => 1,
        ]);

        $cleanMembers = [];
        foreach ($personnelIds as $personnelId) {
            $personnelId = (int) $personnelId;
            if ($personnelId > 0) {
                $cleanMembers[] = $personnelId;
            }
        }

        RotationMember::sync($groupId, $cleanMembers);
        flash('success', 'Rotation group saved.');
        redirect(APP_PUBLIC_URL . '/?route=rotation-groups/index&duty_id=' . $dutyId);
    }
}

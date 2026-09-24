<?php
declare(strict_types=1);

class RotationController
{
    public function index(): void
    {
        require_permission('rotation.view');

        $pageTitle = 'Rotation Dashboard';
        $dutyId = (int) ($_GET['duty_id'] ?? 0);
        $duty = $dutyId > 0 ? Duty::findById($dutyId) : null;
        if (!$duty) {
            $duty = Duty::active()[0] ?? null;
            $dutyId = $duty ? (int) $duty['id'] : 0;
        }

        $duties = Duty::active();
        $dashboard = $dutyId > 0 ? RotationEngine::dashboard($dutyId) : [
            'duty_id' => 0,
            'cycle' => 1,
            'members' => [],
            'next_recommendation' => null,
        ];
        $viewFile = APP_ROOT . '/views/scheduling/rotation.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }
}

<?php
declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        require_permission('dashboard.view');

        $pageTitle = 'Dashboard';
        $user = current_user();
        $viewFile = APP_ROOT . '/views/dashboard/index.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }
}

<?php
declare(strict_types=1);

class ReportController
{
    public function index(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Reports';
        $viewFile = APP_ROOT . '/views/reports/index.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function roster(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Duty Roster';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $records = validate_date($date) ? Schedule::allForDate($date) : [];
        $viewFile = APP_ROOT . '/views/reports/roster.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function dutyHistory(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Personnel Duty History';
        $personnel = Personnel::all();
        $selectedId = (int) ($_GET['personnel_id'] ?? ($personnel[0]['id'] ?? 0));
        $history = $selectedId > 0 ? DutyHistoryController::historyForPersonnel($selectedId) : [];
        $viewFile = APP_ROOT . '/views/reports/duty-history.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function powRotation(): void
    {
        require_permission('reports.view');
        $pageTitle = 'POW Rotation Report';
        $dutyId = (int) ($_GET['duty_id'] ?? 4);
        $dashboard = RotationEngine::dashboard($dutyId);
        $viewFile = APP_ROOT . '/views/reports/pow-rotation.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function sentinelRotation(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Sentinel Rotation Report';
        $records = [];
        $subDuties = SubDuty::allForDuty(5);
        foreach ($subDuties as $subDuty) {
            $reliefs = Relief::allForSubDuty((int) $subDuty['id']);
            $records[] = ['subduty' => $subDuty, 'reliefs' => $reliefs];
        }
        $viewFile = APP_ROOT . '/views/reports/sentinel-rotation.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function availability(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Availability Report';
        $start = trim((string) ($_GET['start_date'] ?? date('Y-m-d')));
        $end = trim((string) ($_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'))));
        $records = [];
        if (validate_date($start) && validate_date($end)) {
            $pdo = get_db();
            $statement = $pdo->prepare(
                'SELECT a.*, p.first_name, p.last_name, r.rank_abbr
                 FROM personnel_availability a
                 LEFT JOIN personnel p ON p.id = a.personnel_id
                 LEFT JOIN ranks r ON r.id = p.rank_id
                 WHERE a.start_date <= :end_date AND (a.end_date IS NULL OR a.end_date >= :start_date)
                 ORDER BY a.start_date ASC'
            );
            $statement->execute([':start_date' => $start, ':end_date' => $end]);
            $records = $statement->fetchAll() ?: [];
        }
        $viewFile = APP_ROOT . '/views/reports/availability.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function personnelStatus(): void
    {
        require_permission('reports.view');
        $pageTitle = 'Personnel Status';
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT p.id, p.first_name, p.last_name, p.status, r.rank_abbr
             FROM personnel p
             LEFT JOIN ranks r ON r.id = p.rank_id
             ORDER BY p.status DESC, p.last_name ASC'
        );
        $records = $statement->fetchAll() ?: [];
        $viewFile = APP_ROOT . '/views/reports/personnel-status.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function pod(): void
    {
        require_permission('reports.view');
        redirect(APP_PUBLIC_URL . '/?route=pod/index');
    }
}

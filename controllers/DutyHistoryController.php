<?php
declare(strict_types=1);

class DutyHistoryController
{
    public function index(): void
    {
        require_permission('dutyhistory.view');

        $personnel = Personnel::all();
        $selectedPersonnelId = (int) ($_GET['personnel_id'] ?? ($personnel[0]['id'] ?? 0));
        $pageTitle = 'Duty History';
        $viewFile = APP_ROOT . '/views/duty-history/person.php';

        $history = [];
        if ($selectedPersonnelId > 0) {
            $history = self::historyForPersonnel($selectedPersonnelId);
        }

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function person(): void
    {
        require_permission('dutyhistory.view');

        $personnelId = (int) ($_GET['personnel_id'] ?? 0);
        if ($personnelId <= 0) {
            redirect(APP_PUBLIC_URL . '/?route=dutyhistory/index');
        }

        $pageTitle = 'Duty History';
        $viewFile = APP_ROOT . '/views/duty-history/person.php';
        $person = Personnel::findById($personnelId);
        $history = self::historyForPersonnel($personnelId);
        $personnel = Personnel::all();

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public static function historyForPersonnel(int $personnelId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.schedule_date, s.status, s.source,
                    d.duty_name, sd.subduty_name, dr.relief_name
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             LEFT JOIN duty_subduties sd ON sd.id = s.subduty_id
             LEFT JOIN duty_reliefs dr ON dr.id = s.relief_id
             WHERE s.personnel_id = :personnel_id
             ORDER BY s.schedule_date DESC, s.id DESC'
        );
        $statement->execute([':personnel_id' => $personnelId]);

        return $statement->fetchAll() ?: [];
    }
}

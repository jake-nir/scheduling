<?php
declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        require_permission('dashboard.view');

        $today = date('Y-m-d');
        $pageTitle = 'Dashboard';
        $todayDuties = self::dutyListForDate($today);
        $upcoming = self::upcomingDuties(7);
        $personnelStatus = self::personnelStatusCounts();
        $alerts = self::scheduleAlerts();
        $viewFile = APP_ROOT . '/views/dashboard/index.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public static function dutyListForDate(string $date): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.*, d.duty_name, p.first_name, p.last_name, r.rank_abbr
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             LEFT JOIN personnel p ON p.id = s.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE s.schedule_date = :schedule_date
             ORDER BY d.sort_order IS NULL, d.sort_order ASC, s.created_at ASC'
        );
        $statement->execute([':schedule_date' => $date]);
        return $statement->fetchAll() ?: [];
    }

    public static function upcomingDuties(int $days): array
    {
        $items = [];
        $start = new DateTime('today');

        for ($offset = 0; $offset < $days; $offset++) {
            $day = clone $start;
            $day->modify('+' . $offset . ' day');
            $date = $day->format('Y-m-d');
            $items[$date] = self::dutyListForDate($date);
        }

        return $items;
    }

    public static function personnelStatusCounts(): array
    {
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM personnel_availability
             GROUP BY status
             ORDER BY status ASC'
        );
        $rows = $statement->fetchAll() ?: [];
        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) ($row['status'] ?? 'Available')] = (int) ($row['total'] ?? 0);
        }

        foreach (['Available', 'Leave', 'Schooling', 'Sick', 'Other'] as $status) {
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
        }

        return $counts;
    }

    public static function scheduleAlerts(): array
    {
        $alerts = [];
        $today = date('Y-m-d');
        $todayAssignments = self::dutyListForDate($today);

        foreach (Duty::active() as $duty) {
            $dutyId = (int) ($duty['id'] ?? 0);
            $required = (int) ($duty['personnel_required'] ?? 1);
            $assigned = 0;
            foreach ($todayAssignments as $assignment) {
                if ((int) ($assignment['duty_id'] ?? 0) === $dutyId) {
                    $assigned++;
                }
            }

            if ($assigned < $required) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => sprintf('%s is underfilled for today (%d/%d assigned).', $duty['duty_name'], $assigned, $required),
                ];
            }
        }

        $pdo = get_db();
        $overrideStatement = $pdo->prepare('SELECT COUNT(*) FROM schedule_overrides WHERE confirmed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $overrideStatement->execute();
        $overrideCount = (int) $overrideStatement->fetchColumn();
        if ($overrideCount > 0) {
            $alerts[] = ['type' => 'warning', 'message' => sprintf('There are %d recent manual overrides or rank substitutions.', $overrideCount)];
        }

        $sentinelRepeat = $pdo->prepare(
            'SELECT COUNT(*) FROM schedules s WHERE s.subduty_id IS NOT NULL AND s.relief_id IS NOT NULL AND s.schedule_date >= DATE_SUB(:today, INTERVAL 7 DAY)'
        );
        $sentinelRepeat->execute([':today' => $today]);
        if ((int) $sentinelRepeat->fetchColumn() > 0) {
            $alerts[] = ['type' => 'warning', 'message' => 'Sentinel assignments should be reviewed for repeated sub-duty/relief combinations.'];
        }

        return $alerts;
    }
}

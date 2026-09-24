<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
$dutyId = (int) ($_GET['duty_id'] ?? 0);
$subDutyId = !empty($_GET['subduty_id']) ? (int) $_GET['subduty_id'] : null;
$reliefId = !empty($_GET['relief_id']) ? (int) $_GET['relief_id'] : null;

if ($date === '' || !validate_date($date) || $dutyId <= 0) {
    echo json_encode(['recommendations' => [], 'alternates' => [], 'unavailable' => [], 'warnings' => ['Invalid date or duty selection.']]);
    exit;
}

$result = Scheduler::recommend([
    'personnel_id' => (int) ($_GET['personnel_id'] ?? 0),
    'duty_id' => $dutyId,
    'subduty_id' => $subDutyId,
    'relief_id' => $reliefId,
    'schedule_date' => $date,
    'start_time' => '08:00:00',
    'end_time' => '12:00:00',
]);

$recommended = $result['recommended'] ?? [];
$alternates = $result['alternates'] ?? [];
$unavailable = $result['unavailable'] ?? [];
$warnings = $result['warnings'] ?? [];

echo json_encode([
    'date' => $date,
    'duty_id' => $dutyId,
    'recommended' => $recommended,
    'alternates' => $alternates,
    'unavailable' => $unavailable,
    'warnings' => $warnings,
    'manual_enabled' => true,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

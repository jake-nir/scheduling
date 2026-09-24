<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
$dutyId = (int) ($_GET['duty_id'] ?? 0);

if ($date === '' || !validate_date($date) || $dutyId <= 0) {
    echo json_encode(['recommendations' => [], 'alternates' => [], 'unavailable' => [], 'warnings' => ['Invalid date or duty selection.']]);
    exit;
}

$recommendations = [];
$alternates = [];
$unavailable = [];
$warnings = [];

foreach (Personnel::all() as $person) {
    $personnelId = (int) $person['id'];
    $result = ConflictDetector::evaluate([
        'personnel_id' => $personnelId,
        'duty_id' => $dutyId,
        'schedule_date' => $date,
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);

    $entry = [
        'personnel_id' => $personnelId,
        'name' => Personnel::displayName($person),
        'rank' => $person['rank_abbr'] ?? '',
        'status' => $result['status'],
        'issues' => $result['issues'],
    ];

    if ($result['status'] === 'RECOMMENDED') {
        $recommendations[] = $entry;
    } elseif ($result['status'] === 'WARNING') {
        $warnings[] = $entry;
    } elseif ($result['status'] === 'BLOCKED') {
        $unavailable[] = $entry;
    } else {
        $alternates[] = $entry;
    }
}

echo json_encode([
    'date' => $date,
    'duty_id' => $dutyId,
    'recommendations' => $recommendations,
    'alternates' => $alternates,
    'unavailable' => $unavailable,
    'warnings' => $warnings,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/init.php';

header('Content-Type: application/json');

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
$range = trim((string) ($_GET['range'] ?? 'month'));

if (!validate_date($date)) {
    echo json_encode(['error' => 'Invalid date.']);
    exit;
}

$items = [];
if ($range === 'day') {
    $items = Schedule::allForDate($date);
} elseif ($range === 'week') {
    $start = new DateTime($date);
    $start->modify('monday this week');
    for ($i = 0; $i < 7; $i++) {
        $day = clone $start;
        $day->modify('+' . $i . ' day');
        $dayKey = $day->format('Y-m-d');
        $items[$dayKey] = Schedule::allForDate($dayKey);
    }
} else {
    $monthStart = new DateTime($date);
    $monthStart->modify('first day of this month');
    $monthEnd = clone $monthStart;
    $monthEnd->modify('last day of this month');

    $current = clone $monthStart;
    while ($current <= $monthEnd) {
        $key = $current->format('Y-m-d');
        $items[$key] = Schedule::allForDate($key);
        $current->modify('+1 day');
    }
}

echo json_encode([
    'date' => $date,
    'range' => $range,
    'items' => $items,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

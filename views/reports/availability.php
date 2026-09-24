<?php
declare(strict_types=1);
$records = $records ?? [];
?>
<div class="panel">
    <div class="panel-header">
        <span>Availability Report</span>
        <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=reports/availability'); ?>" class="inline-form no-print">
            <input type="hidden" name="route" value="reports/availability">
            <input type="date" name="start_date" value="<?= e((string) ($_GET['start_date'] ?? date('Y-m-d'))); ?>">
            <input type="date" name="end_date" value="<?= e((string) ($_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days')))); ?>">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>
    <div class="panel-body">
        <p class="muted">Personnel are available by default. Only unavailability periods within the range are listed.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Personnel</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))); ?></td>
                        <td><?= e($record['status'] ?? ''); ?></td>
                        <td><?= e((string) ($record['start_date'] ?? '')); ?></td>
                        <td><?= e((string) ($record['end_date'] ?? '')); ?></td>
                        <td><?= e($record['reason'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

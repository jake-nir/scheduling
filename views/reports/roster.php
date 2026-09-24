<?php
declare(strict_types=1);
$records = $records ?? [];
?>
<div class="panel">
    <div class="panel-header">
        <span>Duty Roster</span>
        <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=reports/roster'); ?>" class="inline-form no-print">
            <input type="hidden" name="route" value="reports/roster">
            <input type="date" name="date" value="<?= e((string) ($_GET['date'] ?? date('Y-m-d'))); ?>">
            <button type="submit" class="btn btn-secondary">Load</button>
        </form>
    </div>
    <div class="panel-body">
        <?php if (empty($records)): ?>
            <p>No assignments for the selected day.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duty</th>
                        <th>Personnel</th>
                        <th>Rank</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= e((string) ($record['schedule_date'] ?? '')); ?></td>
                            <td><?= e($record['duty_name'] ?? ''); ?></td>
                            <td><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))); ?></td>
                            <td><?= e($record['rank_abbr'] ?? ''); ?></td>
                            <td><?= e($record['status'] ?? 'planned'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

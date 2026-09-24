<?php
declare(strict_types=1);

$date = trim((string) ($date ?? ($_GET['date'] ?? date('Y-m-d'))));
?>
<div class="panel">
    <div class="panel-header">
        <span>Daily Schedule</span>
        <div class="button-row">
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode($date)); ?>">Refresh</a>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=schedule/create&date=' . urlencode($date)); ?>">Add Assignment</a>
        </div>
    </div>
    <div class="panel-body">
        <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=schedule/index'); ?>" class="inline-form">
            <input type="hidden" name="route" value="schedule/index">
            <input type="date" name="date" value="<?= e($date); ?>">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>

        <?php if (empty($schedules)): ?>
            <p>No assignments for this date.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Duty</th>
                        <th>Sub-duty</th>
                        <th>Relief</th>
                        <th>Personnel</th>
                        <th>Rank</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= e($schedule['duty_name'] ?? ''); ?></td>
                            <td><?= e($schedule['subduty_name'] ?? '-'); ?></td>
                            <td><?= e($schedule['relief_name'] ?? '-'); ?></td>
                            <td><?= e(($schedule['first_name'] ?? '') . ' ' . ($schedule['last_name'] ?? '')); ?></td>
                            <td><?= e($schedule['rank_abbr'] ?? ''); ?></td>
                            <td><?= e($schedule['status'] ?? 'planned'); ?></td>
                            <td><?= e($schedule['source'] ?? 'manual'); ?></td>
                            <td>
                                <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=schedule/cancel'); ?>" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                    <input type="hidden" name="schedule_id" value="<?= e((string) ($schedule['id'] ?? 0)); ?>">
                                    <input type="hidden" name="schedule_date" value="<?= e($date); ?>">
                                    <input type="text" name="reason" placeholder="Cancellation reason" value="">
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

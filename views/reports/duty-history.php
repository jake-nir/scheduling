<?php
declare(strict_types=1);
$history = $history ?? [];
$personnel = $personnel ?? Personnel::all();
$selectedId = (int) ($_GET['personnel_id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span>Personnel Duty History</span>
        <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=reports/duty-history'); ?>" class="inline-form no-print">
            <input type="hidden" name="route" value="reports/duty-history">
            <select name="personnel_id" onchange="this.form.submit()">
                <?php foreach ($personnel as $member): ?>
                    <option value="<?= e((string) $member['id']); ?>" <?= ((int) $member['id'] === $selectedId) ? 'selected' : ''; ?>><?= e(Personnel::displayName($member)); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="panel-body">
        <?php if (empty($history)): ?>
            <p>No duty history available.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duty</th>
                        <th>Sub-duty</th>
                        <th>Relief</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td><?= e((string) ($entry['schedule_date'] ?? '')); ?></td>
                            <td><?= e($entry['duty_name'] ?? ''); ?></td>
                            <td><?= e($entry['subduty_name'] ?? '-'); ?></td>
                            <td><?= e($entry['relief_name'] ?? '-'); ?></td>
                            <td><?= e($entry['status'] ?? 'planned'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
declare(strict_types=1);

$selectedId = (int) ($personnelId ?? ($_GET['personnel_id'] ?? 0));
$selectedPerson = $person ?? null;
?>
<div class="panel">
    <div class="panel-header">
        <span>Duty History</span>
        <div class="button-row">
            <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=dutyhistory/index'); ?>" class="inline-form">
                <input type="hidden" name="route" value="dutyhistory/index">
                <select name="personnel_id" onchange="this.form.submit()">
                    <?php foreach ($personnel as $member): ?>
                        <option value="<?= e((string) $member['id']); ?>" <?= ((int) $member['id'] === $selectedId) ? 'selected' : ''; ?>><?= e(Personnel::displayName($member)); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <?php if (!$selectedPerson && $selectedId > 0): ?>
            <?php $selectedPerson = Personnel::findById($selectedId); ?>
        <?php endif; ?>

        <?php if ($selectedPerson): ?>
            <h3><?= e(Personnel::displayName($selectedPerson)); ?></h3>
        <?php endif; ?>

        <?php if (empty($history)): ?>
            <p>No duty history recorded for this personnel.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Duty</th>
                        <th>Sub-duty</th>
                        <th>Relief</th>
                        <th>Status</th>
                        <th>Source</th>
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
                            <td><?= e($entry['source'] ?? 'manual'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

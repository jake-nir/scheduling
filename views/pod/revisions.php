<?php
declare(strict_types=1);
$pod = $pod ?? [];
$revisions = $revisions ?? [];
?>
<div class="panel">
    <div class="panel-header">
        <span>POD Revision History</span>
    </div>
    <div class="panel-body">
        <div class="inline-actions">
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/view&id=' . (int) ($pod['id'] ?? 0)); ?>">Back to POD</a>
        </div>
        <?php if (empty($revisions)): ?>
            <p>No revision history recorded for this POD.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Duty</th>
                        <th>Old Personnel</th>
                        <th>New Personnel</th>
                        <th>Reason</th>
                        <th>Changed By</th>
                        <th>Changed At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revisions as $revision): ?>
                        <tr>
                            <td><?= (int) ($revision['version'] ?? 0); ?></td>
                            <td><?= e($revision['duty_name'] ?? 'N/A'); ?></td>
                            <td><?= e(trim((string) (($revision['old_first_name'] ?? '') . ' ' . ($revision['old_last_name'] ?? '')))); ?></td>
                            <td><?= e(trim((string) (($revision['new_first_name'] ?? '') . ' ' . ($revision['new_last_name'] ?? '')))); ?></td>
                            <td><?= e($revision['reason'] ?? ''); ?></td>
                            <td><?= e($revision['changed_by_name'] ?? 'System'); ?></td>
                            <td><?= e($revision['changed_at'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

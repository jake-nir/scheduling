<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Duty Configuration</span>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=duties/create'); ?>">Add Duty</a>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Duty</th>
                    <th>Required</th>
                    <th>Sub-duty</th>
                    <th>Reliefs</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($duties as $duty): ?>
                    <tr>
                        <td><?= e($duty['duty_code'] ?? ''); ?></td>
                        <td><?= e($duty['duty_name'] ?? ''); ?></td>
                        <td><?= e((string) ($duty['personnel_required'] ?? 1)); ?></td>
                        <td><?= e((string) Duty::subDutyCount((int) ($duty['id'] ?? 0))); ?> / <?= e($duty['has_subduties'] ? 'Yes' : 'No'); ?></td>
                        <td><?= e((string) Duty::reliefCount((int) ($duty['id'] ?? 0))); ?></td>
                        <td><?= (int) ($duty['is_active'] ?? 1) === 1 ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=duties/edit&id=' . (int) ($duty['id'] ?? 0)); ?>">Edit</a>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=subduties/index&duty_id=' . (int) ($duty['id'] ?? 0)); ?>">Sub-duties</a>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=eligibility/index&duty_id=' . (int) ($duty['id'] ?? 0)); ?>">Eligibility</a>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=rotation-groups/index&duty_id=' . (int) ($duty['id'] ?? 0)); ?>">Rotations</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

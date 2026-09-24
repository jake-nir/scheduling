<?php
declare(strict_types=1);

$subDutyId = (int) ($subDuty['id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=subduties/index&duty_id=' . ($subDuty['duty_id'] ?? 0)); ?>">Back to Sub-duties</a>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=reliefs/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="subduty_id" value="<?= e((string) $subDutyId); ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Relief name</label>
                    <input type="text" name="relief_name" value="" required>
                </div>
                <div class="form-group">
                    <label>Relief order</label>
                    <input type="number" name="relief_order" value="1" min="1">
                </div>
                <div class="form-group">
                    <label>Sort order</label>
                    <input type="number" name="sort_order" value="0">
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="is_active" checked> Active</label>
                </div>
            </div>
            <div class="button-row">
                <button type="submit" class="btn btn-primary">Add Relief</button>
            </div>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relief order</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reliefs as $relief): ?>
                    <tr>
                        <td><?= e($relief['relief_name'] ?? ''); ?></td>
                        <td><?= e((string) ($relief['relief_order'] ?? 0)); ?></td>
                        <td><?= e((string) ($relief['sort_order'] ?? 0)); ?></td>
                        <td><?= (int) ($relief['is_active'] ?? 1) === 1 ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=reliefs/delete&id=' . (int) ($relief['id'] ?? 0) . '&subduty_id=' . $subDutyId); ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

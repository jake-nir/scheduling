<?php
declare(strict_types=1);

$dutyId = (int) ($duty['id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=duties/index'); ?>">Back to Duties</a>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=subduties/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="duty_id" value="<?= e((string) $dutyId); ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Code</label>
                    <input type="text" name="subduty_code" value="" required>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="subduty_name" value="" required>
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
                <button type="submit" class="btn btn-primary">Add Sub-duty</button>
            </div>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Reliefs</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subduties as $subduty): ?>
                    <tr>
                        <td><?= e($subduty['subduty_code'] ?? ''); ?></td>
                        <td><?= e($subduty['subduty_name'] ?? ''); ?></td>
                        <td><?= e((string) SubDuty::reliefCount((int) ($subduty['id'] ?? 0))); ?></td>
                        <td><?= e((string) ($subduty['sort_order'] ?? 0)); ?></td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=reliefs/index&subduty_id=' . (int) ($subduty['id'] ?? 0)); ?>">Reliefs</a>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=subduties/delete&id=' . (int) ($subduty['id'] ?? 0) . '&duty_id=' . $dutyId); ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

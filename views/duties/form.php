<?php
declare(strict_types=1);

$duty = $duty ?? null;
$id = (int) ($duty['id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=duties/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?= e((string) $id); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Duty code</label>
                    <input type="text" name="duty_code" value="<?= e($duty['duty_code'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Duty name</label>
                    <input type="text" name="duty_name" value="<?= e($duty['duty_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Start time</label>
                    <input type="time" name="start_time" value="<?= e($duty['start_time'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>End time</label>
                    <input type="time" name="end_time" value="<?= e($duty['end_time'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Personnel required</label>
                    <input type="number" min="1" name="personnel_required" value="<?= e((string) ($duty['personnel_required'] ?? 1)); ?>">
                </div>
                <div class="form-group">
                    <label>Sort order</label>
                    <input type="number" name="sort_order" value="<?= e((string) ($duty['sort_order'] ?? 0)); ?>">
                </div>
                <div class="form-group">
                    <label>Rotation type</label>
                    <select name="rotation_type">
                        <option value="none" <?= (($duty['rotation_type'] ?? 'none') === 'none') ? 'selected' : ''; ?>>None</option>
                        <option value="sequential" <?= (($duty['rotation_type'] ?? 'none') === 'sequential') ? 'selected' : ''; ?>>Sequential</option>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="has_subduties" <?= (!empty($duty['has_subduties']) || ($duty['has_subduties'] ?? 0) == 1) ? 'checked' : ''; ?>> Has sub-duties</label>
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="is_active" <?= (($duty['is_active'] ?? 1) == 1) ? 'checked' : ''; ?>> Active</label>
                </div>
                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?= e($duty['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save Duty</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=duties/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

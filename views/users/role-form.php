<?php
declare(strict_types=1);

$selectedPermissions = [];
if (isset($role['permissions'])) {
    $selectedPermissions = Role::decodePermissions($role['permissions'] ?? '[]');
}
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=roles/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?= e((string) ($role['id'] ?? 0)); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Role name</label>
                    <input type="text" name="role_name" value="<?= e($role['role_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" value="<?= e($role['description'] ?? ''); ?>">
                </div>
            </div>

            <div class="permission-grid">
                <?php foreach ($permissions as $permission): ?>
                    <label class="checkbox-row">
                        <input type="checkbox" name="perm_<?= e($permission); ?>" value="<?= e($permission); ?>" <?= in_array($permission, $selectedPermissions, true) ? 'checked' : ''; ?>>
                        <span><?= e($permission); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=roles/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

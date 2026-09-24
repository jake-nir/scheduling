<?php
declare(strict_types=1);

$userData = $user ?? ['id' => 0, 'username' => '', 'full_name' => '', 'email' => '', 'role_id' => 1, 'status' => 'active'];
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=users/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?= e((string) ($userData['id'] ?? 0)); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= e($userData['username'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Full name</label>
                    <input type="text" name="full_name" value="<?= e($userData['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($userData['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= e((string) $role['id']); ?>" <?= ((int) ($userData['role_id'] ?? 0) === (int) $role['id']) ? 'selected' : ''; ?>>
                                <?= e($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active" <?= (($userData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?= (($userData['status'] ?? 'active') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password <?= ($userData['id'] ?? 0) ? '(leave blank to keep current)' : ''; ?></label>
                    <input type="password" name="password" autocomplete="new-password">
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=users/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

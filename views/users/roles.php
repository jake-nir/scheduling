<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Roles</span>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=roles/create'); ?>">Create Role</a>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= e($role['role_name']); ?></td>
                        <td><?= e($role['description'] ?? ''); ?></td>
                        <td><?php
                            $perms = json_decode((string) ($role['permissions'] ?? '[]'), true);
                            echo e(implode(', ', is_array($perms) ? $perms : []));
                        ?></td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=roles/edit&id=' . (int) $role['id']); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

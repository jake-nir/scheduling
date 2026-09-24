<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Users</span>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=users/create'); ?>">Create User</a>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= e($row['username']); ?></td>
                        <td><?= e($row['full_name']); ?></td>
                        <td><?= e($row['email'] ?? ''); ?></td>
                        <td><?= e($row['role_name'] ?? 'Unassigned'); ?></td>
                        <td>
                            <span class="badge <?= ($row['status'] ?? 'inactive') === 'active' ? 'badge-primary' : 'badge-muted'; ?>">
                                <?= e($row['status'] ?? 'inactive'); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=users/edit&id=' . (int) $row['id']); ?>">Edit</a>
                            |
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=users/deactivate&id=' . (int) $row['id']); ?>" onclick="return confirm('Deactivate this user?');">Deactivate</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Personnel</span>
        <?php if (user_has_permission('personnel.manage')): ?>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=personnel/create'); ?>">Add Personnel</a>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service #</th>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($personnel as $person): ?>
                    <tr>
                        <td><?= e($person['service_number']); ?></td>
                        <td><a href="<?= e(APP_PUBLIC_URL . '/?route=personnel/show&id=' . (int) $person['id']); ?>"><?= e(Personnel::displayName($person)); ?></a></td>
                        <td><?= e($person['rank_abbr'] ?? ''); ?></td>
                        <td><?= e($person['designation'] ?? ''); ?></td>
                        <td>
                            <span class="badge <?= ($person['status'] ?? 'inactive') === 'active' ? 'badge-primary' : 'badge-muted'; ?>"><?= e($person['status'] ?? 'inactive'); ?></span>
                        </td>
                        <td>
                            <?php if (user_has_permission('personnel.manage')): ?>
                                <a href="<?= e(APP_PUBLIC_URL . '/?route=personnel/edit&id=' . (int) $person['id']); ?>">Edit</a>
                                |
                                <a href="<?= e(APP_PUBLIC_URL . '/?route=personnel/deactivate&id=' . (int) $person['id']); ?>" onclick="return confirm('Deactivate this personnel record?');">Deactivate</a>
                            <?php else: ?>
                                <a href="<?= e(APP_PUBLIC_URL . '/?route=personnel/show&id=' . (int) $person['id']); ?>">View</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Ranks</span>
        <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=ranks/create'); ?>">Add Rank</a>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Rank Name</th>
                    <th>Abbreviation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranks as $rank): ?>
                    <tr>
                        <td><?= e((string) ($rank['rank_order'] ?? 0)); ?></td>
                        <td><?= e($rank['rank_name']); ?></td>
                        <td><?= e($rank['rank_abbr']); ?></td>
                        <td><span class="badge <?= ((int) ($rank['is_active'] ?? 0) === 1) ? 'badge-primary' : 'badge-muted'; ?>"><?= ((int) ($rank['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive'; ?></span></td>
                        <td>
                            <a href="<?= e(APP_PUBLIC_URL . '/?route=ranks/edit&id=' . (int) $rank['id']); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

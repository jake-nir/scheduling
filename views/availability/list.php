<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Availability</span>
        <?php if (user_has_permission('availability.manage')): ?>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/create'); ?>">Add Availability</a>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <?php if (empty($personnel)): ?>
            <p>No personnel records exist.</p>
        <?php else: ?>
            <?php foreach ($personnel as $person): ?>
                <?php $blocks = Availability::allForPersonnel((int) $person['id']); ?>
                <div class="panel sub-panel">
                    <div class="panel-header">
                        <span><?= e(Personnel::displayName($person)); ?></span>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($blocks)): ?>
                            <p>No availability records.</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blocks as $block): ?>
                                        <tr>
                                            <td><?= e($block['status']); ?></td>
                                            <td><?= e($block['start_date']); ?></td>
                                            <td><?= e($block['end_date'] ?? 'Open ended'); ?></td>
                                            <td><?= e($block['reason'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

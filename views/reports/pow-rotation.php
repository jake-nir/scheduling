<?php
declare(strict_types=1);
$dashboard = $dashboard ?? RotationEngine::dashboard(4);
?>
<div class="panel">
    <div class="panel-header">
        <span>POW Rotation Report</span>
    </div>
    <div class="panel-body">
        <p><strong>Current Cycle:</strong> <?= e((string) ($dashboard['cycle'] ?? 1)); ?></p>
        <?php if (empty($dashboard['members'])): ?>
            <p>No rotation members configured.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Sequence</th>
                        <th>Personnel</th>
                        <th>Rank</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dashboard['members'] as $member): ?>
                        <tr>
                            <td><?= e((string) ($member['sequence'] ?? '')); ?></td>
                            <td><?= e($member['name'] ?? ''); ?></td>
                            <td><?= e($member['rank'] ?? ''); ?></td>
                            <td><?= e($member['status'] ?? 'Pending'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

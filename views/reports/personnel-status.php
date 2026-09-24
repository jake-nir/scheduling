<?php
declare(strict_types=1);
$records = $records ?? [];
?>
<div class="panel">
    <div class="panel-header">
        <span>Personnel Status</span>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Personnel</th>
                    <th>Rank</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= e(trim(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))); ?></td>
                        <td><?= e($record['rank_abbr'] ?? ''); ?></td>
                        <td><?= e($record['status'] ?? 'active'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

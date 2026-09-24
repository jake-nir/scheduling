<?php
declare(strict_types=1);
$records = $records ?? [];
?>
<div class="panel">
    <div class="panel-header">
        <span>Sentinel Rotation Report</span>
    </div>
    <div class="panel-body">
        <?php foreach ($records as $row): ?>
            <h3><?= e((string) ($row['subduty']['subduty_name'] ?? 'Sub-duty')); ?></h3>
            <ul>
                <?php foreach ($row['reliefs'] as $relief): ?>
                    <li><?= e($relief['relief_name'] ?? 'Relief'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</div>

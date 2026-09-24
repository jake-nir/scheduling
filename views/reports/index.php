<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span>Reports</span>
    </div>
    <div class="panel-body stack-list">
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/roster'); ?>">Duty Roster</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/duty-history'); ?>">Personnel Duty History</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/pow-rotation'); ?>">POW Rotation Report</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/sentinel-rotation'); ?>">Sentinel Rotation Report</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/availability'); ?>">Availability Report</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=reports/personnel-status'); ?>">Personnel Status</a>
        <a class="list-item" href="<?= e(APP_PUBLIC_URL . '/?route=pod/index'); ?>">POD Management</a>
    </div>
</div>

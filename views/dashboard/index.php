<?php
declare(strict_types=1);

$today = date('Y-m-d');
$todayDuties = $todayDuties ?? DashboardController::dutyListForDate($today);
$upcoming = $upcoming ?? DashboardController::upcomingDuties(7);
$personnelStatus = $personnelStatus ?? DashboardController::personnelStatusCounts();
$alerts = $alerts ?? DashboardController::scheduleAlerts();
?>
<div class="dashboard-grid">
    <div class="panel">
        <div class="panel-header">
            <span>Today's Duties</span>
            <span class="badge badge-primary">Live</span>
        </div>
        <div class="panel-body">
            <?php if (empty($todayDuties)): ?>
                <p>No duty assignments recorded for today.</p>
            <?php else: ?>
                <ul class="stack-list">
                    <?php foreach ($todayDuties as $duty): ?>
                        <li class="list-item">
                            <strong><?= e($duty['duty_name'] ?? 'Duty'); ?></strong>
                            <span><?= e(trim(($duty['first_name'] ?? '') . ' ' . ($duty['last_name'] ?? ''))); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span>Upcoming Duties</span>
        </div>
        <div class="panel-body stack-list">
            <?php foreach ($upcoming as $date => $entries): ?>
                <div class="list-item">
                    <strong><?= e($date); ?></strong>
                    <span><?= e((string) count($entries)); ?> assignment(s)</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span>Personnel Status</span>
        </div>
        <div class="panel-body stack-list">
            <?php foreach ($personnelStatus as $status => $count): ?>
                <div class="list-item">
                    <span><?= e($status); ?></span>
                    <strong><?= e((string) $count); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span>Scheduling Alerts</span>
        </div>
        <div class="panel-body stack-list">
            <?php if (empty($alerts)): ?>
                <div class="list-item">No alerts for this cycle.</div>
            <?php else: ?>
                <?php foreach ($alerts as $alert): ?>
                    <div class="list-item alert alert-<?= e($alert['type'] ?? 'info'); ?>">
                        <?= e($alert['message'] ?? 'Alert'); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

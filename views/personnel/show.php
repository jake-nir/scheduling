<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e(Personnel::displayName($person)); ?></span>
    </div>
    <div class="panel-body">
        <div class="form-grid">
            <div><strong>Service Number:</strong> <?= e($person['service_number'] ?? ''); ?></div>
            <div><strong>Rank:</strong> <?= e($person['rank_abbr'] ?? ''); ?></div>
            <div><strong>Designation:</strong> <?= e($person['designation'] ?? ''); ?></div>
            <div><strong>Unit:</strong> <?= e($person['unit'] ?? ''); ?></div>
            <div><strong>Contact:</strong> <?= e($person['contact_number'] ?? ''); ?></div>
            <div><strong>Email:</strong> <?= e($person['email'] ?? ''); ?></div>
            <div><strong>Status:</strong> <?= e($person['status'] ?? 'inactive'); ?></div>
        </div>

        <h3>Current / Future Unavailability</h3>
        <?php if (empty($availabilities)): ?>
            <p>No unavailability records. Personnel is available by default.</p>
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
                    <?php foreach ($availabilities as $row): ?>
                        <tr>
                            <td><?= e($row['status']); ?></td>
                            <td><?= e($row['start_date']); ?></td>
                            <td><?= e($row['end_date'] ?? 'Open ended'); ?></td>
                            <td><?= e($row['reason'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h3>Recent Assignments</h3>
        <p>Placeholder stub for duty history scheduled for later phases.</p>
    </div>
</div>

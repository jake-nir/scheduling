<?php
declare(strict_types=1);

$dutyId = (int) ($dutyId ?? ($dashboard['duty_id'] ?? 0));
$cycle = (int) ($dashboard['cycle'] ?? 1);
$members = $dashboard['members'] ?? [];
$next = $dashboard['next_recommendation'] ?? null;
?>
<div class="panel">
    <div class="panel-header">
        <span>Rotation Dashboard</span>
        <div class="button-row">
            <form method="get" action="<?= e(APP_PUBLIC_URL . '/?route=rotation/index'); ?>" class="inline-form">
                <input type="hidden" name="route" value="rotation/index">
                <select name="duty_id" onchange="this.form.submit()">
                    <?php foreach ($duties as $duty): ?>
                        <option value="<?= e((string) $duty['id']); ?>" <?= ((int) $duty['id'] === $dutyId) ? 'selected' : ''; ?>><?= e($duty['duty_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-label">Current Cycle</div>
                <div class="stat-value"><?= e((string) $cycle); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Next Recommended</div>
                <div class="stat-value"><?= $next ? e($next['name'] ?? '') : 'None'; ?></div>
            </div>
        </div>

        <?php if (empty($members)): ?>
            <p>No rotation members configured for this duty.</p>
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
                    <?php foreach ($members as $member): ?>
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

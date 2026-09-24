<?php
declare(strict_types=1);

$selectedDutyId = (int) ($duty['id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
        <select onchange="window.location='<?= e(APP_PUBLIC_URL); ?>/?route=eligibility/index&duty_id=' + this.value">
            <?php foreach (Duty::all() as $d): ?>
                <option value="<?= e((string) $d['id']); ?>" <?= ((int) $d['id'] === $selectedDutyId) ? 'selected' : ''; ?>><?= e($d['duty_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=eligibility/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="duty_id" value="<?= e((string) $selectedDutyId); ?>">

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Priority</th>
                        <th>Eligibility</th>
                        <th>Sub-duty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranks as $rank): ?>
                        <?php $existing = null; foreach ($eligibilityRows as $row) { if ((int) $row['rank_id'] === (int) $rank['id']) { $existing = $row; break; } } ?>
                        <tr>
                            <td><?= e($rank['rank_abbr'] ?? $rank['rank_name']); ?></td>
                            <td>
                                <input type="hidden" name="eligibility[<?= e((string) $rank['id']); ?>][id]" value="<?= e((string) ($existing['id'] ?? 0)); ?>">
                                <input type="number" min="1" name="eligibility[<?= e((string) $rank['id']); ?>][priority]" value="<?= e((string) ($existing['priority'] ?? 1)); ?>">
                            </td>
                            <td>
                                <select name="eligibility[<?= e((string) $rank['id']); ?>][eligibility]">
                                    <option value="Primary" <?= (($existing['eligibility'] ?? 'Primary') === 'Primary') ? 'selected' : ''; ?>>Primary</option>
                                    <option value="Alternate" <?= (($existing['eligibility'] ?? 'Primary') === 'Alternate') ? 'selected' : ''; ?>>Alternate</option>
                                </select>
                            </td>
                            <td>
                                <select name="eligibility[<?= e((string) $rank['id']); ?>][subduty_id]">
                                    <option value="">Parent duty</option>
                                    <?php foreach ($subDuties as $subDuty): ?>
                                        <option value="<?= e((string) $subDuty['id']); ?>" <?= ((int) ($existing['subduty_id'] ?? 0) === (int) $subDuty['id']) ? 'selected' : ''; ?>><?= e($subDuty['subduty_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save Matrix</button>
            </div>
        </form>
    </div>
</div>

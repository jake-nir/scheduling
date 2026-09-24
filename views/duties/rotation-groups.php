<?php
declare(strict_types=1);

$selectedDutyId = (int) ($_GET['duty_id'] ?? ($duty['id'] ?? 0));
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
        <select onchange="window.location='<?= e(APP_PUBLIC_URL); ?>/?route=rotation-groups/index&duty_id=' + this.value">
            <?php foreach ($duties as $d): ?>
                <option value="<?= e((string) $d['id']); ?>" <?= ((int) $d['id'] === $selectedDutyId) ? 'selected' : ''; ?>><?= e($d['duty_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=rotation-groups/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="duty_id" value="<?= e((string) $selectedDutyId); ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label>Group name</label>
                    <input type="text" name="group_name" value="" required>
                </div>
                <div class="form-group full-width">
                    <label>Members</label>
                    <select name="personnel_ids[]" multiple size="10" style="min-width: 260px;">
                        <?php foreach ($personnel as $member): ?>
                            <option value="<?= e((string) $member['id']); ?>"><?= e(Personnel::displayName($member)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save Group</button>
            </div>
        </form>

        <?php if (!empty($groups)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><?= e($group['group_name']); ?></td>
                            <td>
                                <?php $members = RotationMember::allForGroup((int) $group['id']); ?>
                                <?php foreach ($members as $member): ?>
                                    <?= e($member['first_name'] . ' ' . $member['last_name']); ?><br>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
declare(strict_types=1);

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
$selectedDutyId = (int) ($_GET['duty_id'] ?? 0);
?>
<div class="panel">
    <div class="panel-header">
        <span>Create Assignment</span>
        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode($date)); ?>">Back to Schedule</a>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=schedule/store'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="schedule_date" value="<?= e($date); ?>" required>
                </div>
                <div class="form-group">
                    <label>Personnel</label>
                    <select name="personnel_id" required>
                        <option value="">Select personnel</option>
                        <?php foreach ($personnel as $member): ?>
                            <option value="<?= e((string) $member['id']); ?>"><?= e(Personnel::displayName($member)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Duty</label>
                    <select name="duty_id" required>
                        <option value="">Select duty</option>
                        <?php foreach ($duties as $duty): ?>
                            <option value="<?= e((string) $duty['id']); ?>" <?= ((int) $duty['id'] === $selectedDutyId) ? 'selected' : ''; ?>><?= e($duty['duty_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sub-duty</label>
                    <select name="subduty_id">
                        <option value="">None</option>
                        <?php foreach (Duty::all() as $duty): ?>
                            <?php foreach (SubDuty::allForDuty((int) $duty['id']) as $subDuty): ?>
                                <option value="<?= e((string) $subDuty['id']); ?>"><?= e($duty['duty_name'] . ' / ' . $subDuty['subduty_name']); ?></option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Relief</label>
                    <select name="relief_id">
                        <option value="">None</option>
                        <?php foreach (Duty::all() as $duty): ?>
                            <?php foreach (SubDuty::allForDuty((int) $duty['id']) as $subDuty): ?>
                                <?php foreach (Relief::allForSubDuty((int) $subDuty['id']) as $relief): ?>
                                    <option value="<?= e((string) $relief['id']); ?>"><?= e($duty['duty_name'] . ' / ' . $subDuty['subduty_name'] . ' / ' . $relief['relief_name']); ?></option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Start time</label>
                    <input type="time" name="start_time">
                </div>
                <div class="form-group">
                    <label>End time</label>
                    <input type="time" name="end_time">
                </div>
                <div class="form-group">
                    <label>Override type</label>
                    <select name="override_type">
                        <option value="rank_substitution">Rank substitution</option>
                        <option value="availability_override">Availability override</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label>Reason</label>
                    <textarea name="reason" rows="3"></textarea>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save Assignment</button>
                <button type="submit" name="confirm_warning" value="1" class="btn btn-warning">Proceed with Warning</button>
            </div>
        </form>
    </div>
</div>

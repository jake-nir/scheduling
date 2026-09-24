<?php
declare(strict_types=1);
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=availability/save'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

            <div class="form-grid">
                <div class="form-group">
                    <label>Personnel</label>
                    <select name="personnel_id" required>
                        <?php foreach ($personnel as $person): ?>
                            <option value="<?= e((string) $person['id']); ?>"><?= e(Personnel::displayName($person)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Available">Available</option>
                        <option value="Leave">Leave</option>
                        <option value="Schooling">Schooling</option>
                        <option value="Sick">Sick</option>
                        <option value="Official Assignment">Official Assignment</option>
                        <option value="Training">Training</option>
                        <option value="Temporarily Unavailable">Temporarily Unavailable</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Start date</label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label>End date</label>
                    <input type="date" name="end_date">
                </div>

                <div class="form-group">
                    <label>Reason</label>
                    <input type="text" name="reason">
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="3"></textarea>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Save</button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

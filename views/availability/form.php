<?php
declare(strict_types=1);

$availability = $availability ?? null;
$isEdit = $availability !== null;
$actionRoute = $isEdit ? 'availability/update' : 'availability/save';
$selectedPersonnelId = $isEdit ? (int) ($availability['personnel_id'] ?? 0) : (int) ($_POST['personnel_id'] ?? $_GET['personnel_id'] ?? 0);
$selectedStatus = $isEdit ? (string) ($availability['status'] ?? '') : (string) ($_POST['status'] ?? '');
$selectedStart = $isEdit ? (string) ($availability['start_date'] ?? '') : (string) ($_POST['start_date'] ?? '');
$selectedEnd = $isEdit ? (string) ($availability['end_date'] ?? '') : (string) ($_POST['end_date'] ?? '');
$selectedReason = $isEdit ? (string) ($availability['reason'] ?? '') : (string) ($_POST['reason'] ?? '');
$selectedRemarks = $isEdit ? (string) ($availability['remarks'] ?? '') : (string) ($_POST['remarks'] ?? '');
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pageTitle); ?></span>
    </div>
    <div class="panel-body">
        <p class="muted">Personnel are considered available by default. Record only periods when a personnel member is unavailable for duty.</p>

        <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=' . $actionRoute); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e((string) $availability['id']); ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label>Personnel</label>
                    <select name="personnel_id" required>
                        <option value="">Select personnel</option>
                        <?php foreach ($personnel as $person): ?>
                            <option value="<?= e((string) $person['id']); ?>" <?= ((int) $person['id'] === $selectedPersonnelId) ? 'selected' : ''; ?>><?= e(Personnel::displayName($person)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Unavailability Type</label>
                    <select name="status" required>
                        <option value="">Select type</option>
                        <?php foreach (Availability::EXCEPTION_STATUSES as $option): ?>
                            <option value="<?= e($option); ?>" <?= ($selectedStatus === $option) ? 'selected' : ''; ?>><?= e($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Start date</label>
                    <input type="date" name="start_date" value="<?= e($selectedStart); ?>" required>
                </div>

                <div class="form-group">
                    <label>End date</label>
                    <input type="date" name="end_date" value="<?= e($selectedEnd); ?>">
                    <span class="field-hint">Leave blank if the unavailability is ongoing.</span>
                </div>

                <div class="form-group">
                    <label>Reason</label>
                    <input type="text" name="reason" value="<?= e($selectedReason); ?>" data-required-when="Other">
                    <span class="field-hint">Required when the type is Other.</span>
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="3"><?= e($selectedRemarks); ?></textarea>
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Unavailability' : 'Record Unavailability'; ?></button>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/index'); ?>">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var statusSelect = document.querySelector('select[name="status"]');
        var reasonInput = document.querySelector('input[name="reason"]');
        if (!statusSelect || !reasonInput) {
            return;
        }

        function syncReasonRequirement() {
            var isOther = statusSelect.value === 'Other';
            reasonInput.required = isOther;
        }

        statusSelect.addEventListener('change', syncReasonRequirement);
        syncReasonRequirement();
    })();
</script>
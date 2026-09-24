<?php
declare(strict_types=1);
$pod = $pod ?? [];
$items = $pod['items'] ?? [];
$personnel = Personnel::all();
?>
<div class="panel">
    <div class="panel-header">
        <span><?= e($pod['reference_number'] ?? 'POD'); ?></span>
    </div>
    <div class="panel-body">
        <div class="stack-form">
            <div class="meta-grid">
                <div><strong>Date</strong><br><?= e($pod['pod_date'] ?? ''); ?></div>
                <div><strong>Status</strong><br><?= e(strtoupper((string) ($pod['status'] ?? 'draft'))); ?></div>
                <div><strong>Version</strong><br><?= (int) ($pod['version'] ?? 1); ?></div>
                <div><strong>Approved by</strong><br><?= e($pod['approved_by_name'] ?? ($pod['approved_by'] ?? 'Pending')); ?></div>
            </div>
            <div class="inline-actions">
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/preview&id=' . (int) ($pod['id'] ?? 0)); ?>">Preview POD</a>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/print&id=' . (int) ($pod['id'] ?? 0)); ?>">Print</a>
                <?php if ((string) ($pod['status'] ?? 'draft') !== 'final'): ?>
                    <form method="POST" action="<?= e(APP_PUBLIC_URL . '/?route=pod/finalize'); ?>" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                        <input type="hidden" name="pod_id" value="<?= (int) ($pod['id'] ?? 0); ?>">
                        <button type="submit" class="btn btn-primary">Finalize POD</button>
                    </form>
                <?php endif; ?>
                <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/revisions&id=' . (int) ($pod['id'] ?? 0)); ?>">Revisions</a>
                <form method="POST" action="<?= e(APP_PUBLIC_URL . '/?route=pod/cancel'); ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="pod_id" value="<?= (int) ($pod['id'] ?? 0); ?>">
                    <input type="text" name="reason" placeholder="Reason for cancellation" value="" style="width: 220px;">
                    <button type="submit" class="btn btn-danger">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span>Assignments</span>
    </div>
    <div class="panel-body stacked-rows">
        <?php if (empty($items)): ?>
            <p>No assignments available for this POD.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <form method="POST" action="<?= e(APP_PUBLIC_URL . '/?route=pod/revise'); ?>" class="stack-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="pod_id" value="<?= (int) ($pod['id'] ?? 0); ?>">
                    <input type="hidden" name="item_id" value="<?= (int) ($item['id'] ?? 0); ?>">
                    <div class="meta-grid">
                        <div><strong><?= e(PodService::itemLabel($item)); ?></strong></div>
                        <div>
                            <label>
                                Personnel
                                <select name="personnel_id" required>
                                    <?php foreach ($personnel as $person): ?>
                                        <option value="<?= (int) $person['id']; ?>" <?= ((int) ($item['personnel_id'] ?? 0) === (int) $person['id']) ? 'selected' : ''; ?>>
                                            <?= e(PodService::personDisplayName($person)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <?php if ((string) ($pod['status'] ?? 'draft') === 'final'): ?>
                            <div>
                                <label>
                                    Revision reason
                                    <input type="text" name="reason" value="" placeholder="Required for final POD edits">
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

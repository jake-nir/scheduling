<?php
declare(strict_types=1);
$pods = $pods ?? [];
$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
?>
<div class="panel">
    <div class="panel-header">
        <span>Generate POD</span>
    </div>
    <div class="panel-body">
        <form method="POST" action="<?= e(APP_PUBLIC_URL . '/?route=pod/create'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <label>
                Schedule date
                <input type="date" name="pod_date" value="<?= e($date); ?>" required>
            </label>
            <button type="submit" class="btn btn-primary">Generate POD Draft</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <span>POD History</span>
    </div>
    <div class="panel-body stack-list">
        <?php if (empty($pods)): ?>
            <p>No POD records have been created yet.</p>
        <?php else: ?>
            <?php foreach ($pods as $pod): ?>
                <div class="list-item">
                    <div>
                        <strong><?= e($pod['reference_number'] ?? ('POD-' . (int) $pod['id'])); ?></strong><br>
                        <small><?= e($pod['pod_date'] ?? ''); ?> • <?= e(strtoupper((string) ($pod['status'] ?? 'draft'))); ?> • v<?= (int) ($pod['version'] ?? 1); ?></small>
                    </div>
                    <div class="inline-actions">
                        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/view&id=' . (int) $pod['id']); ?>">View</a>
                        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/preview&id=' . (int) $pod['id']); ?>">Preview</a>
                        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/print&id=' . (int) $pod['id']); ?>">Print</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

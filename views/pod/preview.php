<?php
declare(strict_types=1);
$pod = $pod ?? [];
$items = $pod['items'] ?? [];
$orgName = PodService::setting('org_name', 'Duty Scheduling');
$preparedBy = PodService::setting('pod_prepared_by', 'Operations');
$reviewedBy = PodService::setting('pod_reviewed_by', 'Duty Supervisor');
$approvedBy = PodService::setting('pod_approved_by', 'Commanding Officer');
$orientation = PodService::setting('print_orientation', 'portrait');
$orientationClass = strtolower((string) $orientation) === 'landscape' ? 'pod-print landscape' : 'pod-print portrait';
?>
<div class="panel no-print">
    <div class="panel-header">
        <span>POD Preview</span>
    </div>
    <div class="panel-body">
        <div class="inline-actions">
            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/view&id=' . (int) ($pod['id'] ?? 0)); ?>">Back to POD</a>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=pod/print&id=' . (int) ($pod['id'] ?? 0)); ?>">Print POD</a>
        </div>
    </div>
</div>

<div class="<?= e($orientationClass); ?> print-area">
    <div class="pod-header">
        <div class="pod-org-logo"><?= e($orgName); ?></div>
        <h2>PLAN OF THE DAY</h2>
        <div class="pod-meta">
            <span><strong>Date:</strong> <?= e($pod['pod_date'] ?? ''); ?></span>
            <span><strong>Reference:</strong> <?= e($pod['reference_number'] ?? 'N/A'); ?></span>
        </div>
    </div>

    <table class="pod-table">
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td class="pod-duty-label">DUTY <?= e(strtoupper((string) PodService::itemLabel($item))); ?></td>
                <td><?= e(PodService::personDisplayName($item)); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pod-footer">
        <div><strong>Prepared by:</strong> <?= e($preparedBy); ?></div>
        <div><strong>Reviewed by:</strong> <?= e($reviewedBy); ?></div>
        <div><strong>Approved by:</strong> <?= e($approvedBy); ?></div>
        <div><strong>Generated:</strong> <?= e(date('Y-m-d H:i:s')); ?></div>
    </div>
</div>

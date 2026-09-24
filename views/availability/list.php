<?php
declare(strict_types=1);

$today = date('Y-m-d');
$activePersonnel = array_values(array_filter(
    $personnel ?? [],
    static fn (array $person): bool => ($person['status'] ?? 'inactive') === 'active'
));
?>
<div class="panel">
    <div class="panel-header">
        <span>Unavailability</span>
        <?php if (user_has_permission('availability.manage')): ?>
            <a class="btn btn-primary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/create'); ?>">Record Unavailability</a>
        <?php endif; ?>
    </div>
    <div class="panel-body">
        <p class="muted">Personnel are available by default. Records below only show current or upcoming unavailability periods.</p>

        <?php if (empty($activePersonnel)): ?>
            <p>No active personnel records exist.</p>
        <?php else: ?>
            <?php foreach ($activePersonnel as $person): ?>
                <?php
                    $exceptions = Availability::activeForPerson((int) $person['id']);
                    $current = [];
                    $upcoming = [];
                    foreach ($exceptions as $exception) {
                        if (($exception['start_date'] ?? '') <= $today && ((string) ($exception['end_date'] ?? '') === '' || (string) $exception['end_date'] >= $today)) {
                            $current[] = $exception;
                        } elseif (($exception['start_date'] ?? '') > $today) {
                            $upcoming[] = $exception;
                        }
                    }
                    if (!empty($current)) {
                        $stateLabel = 'UNAVAILABLE';
                        $stateClass = 'badge-danger';
                    } elseif (!empty($upcoming)) {
                        $stateLabel = 'UPCOMING';
                        $stateClass = 'badge-warning';
                    } else {
                        $stateLabel = 'AVAILABLE';
                        $stateClass = 'badge-success';
                    }
                    $rows = array_merge($current, $upcoming);
                ?>
                <div class="panel sub-panel">
                    <div class="panel-header">
                        <span>
                            <?= e(Personnel::displayName($person)); ?>
                            <span class="badge <?= e($stateClass); ?>"><?= e($stateLabel); ?></span>
                        </span>
                        <?php if (user_has_permission('availability.manage')): ?>
                            <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/create&personnel_id=' . (int) $person['id']); ?>">Record Unavailability</a>
                        <?php endif; ?>
                    </div>
                    <div class="panel-body">
                        <?php if (empty($rows)): ?>
                            <p>Available by default — no current exceptions.</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Reason</th>
                                        <?php if (user_has_permission('availability.manage')): ?>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $block): ?>
                                        <tr>
                                            <td><?= e($block['status']); ?></td>
                                            <td><?= e($block['start_date']); ?></td>
                                            <td><?= e($block['end_date'] ?? 'Open ended'); ?></td>
                                            <td><?= e($block['reason'] ?? ''); ?></td>
                                            <?php if (user_has_permission('availability.manage')): ?>
                                                <td>
                                                    <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=availability/edit&id=' . (int) $block['id']); ?>">Edit</a>
                                                    <form method="post" action="<?= e(APP_PUBLIC_URL . '/?route=availability/delete'); ?>" class="inline-form">
                                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                                                        <input type="hidden" name="id" value="<?= e((string) $block['id']); ?>">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
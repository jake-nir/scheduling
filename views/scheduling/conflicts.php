<?php
declare(strict_types=1);

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
?>
<div class="panel">
    <div class="panel-header">
        <span>Conflict Summary</span>
        <a class="btn btn-secondary" href="<?= e(APP_PUBLIC_URL . '/?route=schedule/index&date=' . urlencode($date)); ?>">Back</a>
    </div>
    <div class="panel-body">
        <p>Blocked and warning scenarios are enforced server-side before assignment is allowed.</p>
        <ul>
            <li>Inactive personnel = BLOCKED</li>
            <li>Same-date overlapping schedule = BLOCKED</li>
            <li>Leave status = BLOCKED</li>
            <li>Rank not eligible = BLOCKED</li>
            <li>Rest gap / recent duty frequency = WARNING</li>
        </ul>
    </div>
</div>

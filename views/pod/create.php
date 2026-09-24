<?php
declare(strict_types=1);
$selectedDate = trim((string) ($_GET['date'] ?? date('Y-m-d')));
?>
<div class="panel">
    <div class="panel-header">
        <span>Create a POD</span>
    </div>
    <div class="panel-body">
        <form method="POST" action="<?= e(APP_PUBLIC_URL . '/?route=pod/create'); ?>" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <label>
                Schedule date
                <input type="date" name="pod_date" value="<?= e($selectedDate); ?>" required>
            </label>
            <button type="submit" class="btn btn-primary">Generate Draft POD</button>
        </form>
    </div>
</div>
